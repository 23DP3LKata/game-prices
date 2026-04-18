<?php

namespace App\Services\Stores;

use App\Models\Game;
use App\Models\GamePrice;
use App\Models\GameStoreListing;
use App\Models\WishlistItem;
use App\Models\Store;
use App\Notifications\GamePriceChangedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ItadPriceSyncService
{
    public function __construct(private readonly ItadClient $client)
    {
    }

    /**
     * @return array{resolved:int, missing:int, listings_created:int, listings_updated:int, failed:int}
     */
    public function syncListings(): array
    {
        $stats = [
            'resolved' => 0,
            'missing' => 0,
            'listings_created' => 0,
            'listings_updated' => 0,
            'failed' => 0,
        ];

        $games = Game::query()
            ->where('is_active', true)
            ->get();

        $missing = $games->whereNull('itad_id');
        if ($missing->isNotEmpty()) {
            foreach ($missing->chunk(100) as $chunk) {
                $titles = $chunk->pluck('name')->all();
                $resolved = $this->client->lookupGameIdsByTitle($titles);
                if ($resolved === []) {
                    $stats['failed']++;
                    continue;
                }

                foreach ($chunk as $game) {
                    $itadId = $resolved[$game->name] ?? null;
                    if (! is_string($itadId) || $itadId === '') {
                        $stats['missing']++;
                        continue;
                    }

                    $game->forceFill(['itad_id' => $itadId])->save();
                    $stats['resolved']++;
                }
            }
        }

        $gamesWithId = Game::query()
            ->where('is_active', true)
            ->whereNotNull('itad_id')
            ->get()
            ->keyBy('itad_id');

        if ($gamesWithId->isEmpty()) {
            return $stats;
        }

        $stores = Store::query()
            ->where('is_active', true)
            ->where('sync_enabled', true)
            ->whereNotNull('itad_shop_id')
            ->get();

        foreach ($stores as $store) {
            $shopId = (int) $store->itad_shop_id;
            if ($shopId <= 0) {
                continue;
            }

            foreach ($gamesWithId->keys()->chunk(200) as $chunk) {
                $map = $this->client->lookupShopIdsByGameIds($shopId, $chunk->all());
                if ($map === []) {
                    $stats['failed']++;
                    continue;
                }

                foreach ($map as $itadId => $shopIds) {
                    $shopGameId = $this->firstShopGameId($shopIds);
                    if ($shopGameId === null) {
                        continue;
                    }

                    $game = $gamesWithId->get($itadId);
                    if (! $game) {
                        continue;
                    }

                    $listing = GameStoreListing::query()->updateOrCreate(
                        [
                            'store_id' => $store->id,
                            'game_id' => $game->id,
                        ],
                        [
                            'external_game_id' => $shopGameId,
                            'title_in_store' => $game->name,
                            'is_active' => true,
                            'is_available' => true,
                        ],
                    );

                    if ($listing->wasRecentlyCreated) {
                        $stats['listings_created']++;
                    } else {
                        $stats['listings_updated']++;
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * @return array{synced:int, updated:int, skipped:int, failed:int}
     */
    public function syncPrices(): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        $stores = Store::query()
            ->where('is_active', true)
            ->where('sync_enabled', true)
            ->whereNotNull('itad_shop_id')
            ->get();

        if ($stores->isEmpty()) {
            return $stats;
        }

        $storeByShopId = $stores->keyBy('itad_shop_id');
        $shopIds = $stores->pluck('itad_shop_id')->unique()->values()->all();

        $listings = GameStoreListing::query()
            ->where('is_active', true)
            ->whereIn('store_id', $stores->pluck('id'))
            ->whereHas('game', fn ($query) => $query->where('is_active', true)->whereNotNull('itad_id'))
            ->with(['game', 'store'])
            ->get();

        if ($listings->isEmpty()) {
            return $stats;
        }

        $listingsByItadId = $listings
            ->groupBy(fn (GameStoreListing $listing) => $listing->game?->itad_id)
            ->filter(fn (Collection $group, $itadId) => is_string($itadId) && $itadId !== '');

        $now = CarbonImmutable::now()->startOfSecond();
        $country = config('itad.country');

        foreach ($listingsByItadId->keys()->chunk(200) as $chunk) {
            $prices = $this->client->fetchPrices($chunk->all(), $shopIds, is_string($country) ? $country : null);
            if ($prices === []) {
                $stats['failed']++;
                continue;
            }

            foreach ($prices as $entry) {
                $itadId = $entry['id'] ?? null;
                if (! is_string($itadId) || $itadId === '') {
                    continue;
                }

                $deals = $this->extractDeals($entry);
                if ($deals === []) {
                    foreach ($listingsByItadId->get($itadId, collect()) as $listing) {
                        $stats['synced']++;
                        $this->markUnavailable($listing, $now);
                        $stats['skipped']++;
                    }
                    continue;
                }

                foreach ($listingsByItadId->get($itadId, collect()) as $listing) {
                    $stats['synced']++;

                    $store = $listing->store;
                    if (! $store) {
                        $stats['skipped']++;
                        continue;
                    }

                    $shopId = $store?->itad_shop_id;
                    if (! is_numeric($shopId)) {
                        $stats['skipped']++;
                        continue;
                    }

                    $deal = $this->findDealForShop($deals, (int) $shopId, $store);
                    if (! $deal) {
                        if (config('app.debug')) {
                            Log::info('ITAD deal not found for store.', [
                                'itad_id' => $itadId,
                                'game_id' => $listing->game_id,
                                'store_id' => $store->id,
                                'store_code' => $store->code,
                                'store_name' => $store->name,
                                'shop_id' => (int) $shopId,
                                'deal_shop_ids' => $this->collectDealShopIds($deals),
                                'deal_shop_names' => $this->collectDealShopNames($deals),
                            ]);
                        }

                        $this->markUnavailable($listing, $now);
                        $stats['skipped']++;
                        continue;
                    }

                    $price = $this->extractMoney($deal['price'] ?? null);
                    $regular = $this->extractMoney($deal['regular'] ?? $deal['regularPrice'] ?? null);
                    $discount = $this->extractDiscount($deal);
                    $isOnSale = $discount !== null && $discount > 0;
                    $previousPrice = $listing->current_price !== null ? (string) $listing->current_price : null;

                    if ($price === null) {
                        $this->markUnavailable($listing, $now);
                        $stats['skipped']++;
                        continue;
                    }

                    $listing->fill([
                        'current_price' => $price,
                        'original_price' => $regular,
                        'discount_percent' => $discount,
                        'is_on_sale' => $isOnSale,
                        'is_available' => true,
                        'last_synced_at' => $now,
                        'external_url' => $deal['url'] ?? $listing->external_url,
                    ]);

                    $listing->save();

                    if ($this->recordHistory($listing, $price, $regular, $discount, $isOnSale, $now)) {
                        $stats['updated']++;
                    } else {
                        $stats['skipped']++;
                    }

                    $this->notifyWishlistUsersForPriceChange($listing, $previousPrice, $price, $regular, $discount, $isOnSale, $now);
                }
            }
        }

        return $stats;
    }

    private function extractDeals(array $entry): array
    {
        $deals = $entry['deals'] ?? $entry['prices'] ?? [];
        return is_array($deals) ? $deals : [];
    }

    private function findDealForShop(array $deals, int $shopId, Store $store): ?array
    {
        foreach ($deals as $deal) {
            if (! is_array($deal)) {
                continue;
            }

            $dealShopId = $this->extractShopId($deal);
            if ($dealShopId !== null && $dealShopId === $shopId) {
                return $deal;
            }

            $dealShopName = $this->extractShopName($deal);
            if ($dealShopName === null) {
                continue;
            }

            $normalizedDeal = $this->normalizeShopName($dealShopName);
            if ($normalizedDeal === '') {
                continue;
            }

            $normalizedStoreName = $this->normalizeShopName($store->name);
            $normalizedStoreCode = $this->normalizeShopName($store->code);

            if ($normalizedDeal === $normalizedStoreName || $normalizedDeal === $normalizedStoreCode) {
                return $deal;
            }
        }

        return null;
    }

    private function extractShopId(array $deal): ?int
    {
        $shop = $deal['shop'] ?? null;
        if (is_array($shop) && isset($shop['id']) && is_numeric($shop['id'])) {
            return (int) $shop['id'];
        }

        if (is_numeric($shop)) {
            return (int) $shop;
        }

        if (isset($deal['shopId']) && is_numeric($deal['shopId'])) {
            return (int) $deal['shopId'];
        }

        return null;
    }

    private function extractShopName(array $deal): ?string
    {
        $shop = $deal['shop'] ?? null;
        if (is_array($shop)) {
            $name = $shop['name'] ?? $shop['title'] ?? null;
            if (is_string($name) && $name !== '') {
                return $name;
            }
        }

        if (is_string($shop) && $shop !== '' && ! is_numeric($shop)) {
            return $shop;
        }

        $name = $deal['shopName'] ?? $deal['shop_name'] ?? null;
        if (is_string($name) && $name !== '') {
            return $name;
        }

        return null;
    }

    private function normalizeShopName(string $value): string
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9]+/', '', $value) ?? '');
        return $normalized;
    }

    /**
     * @return list<int>
     */
    private function collectDealShopIds(array $deals): array
    {
        $ids = [];
        foreach ($deals as $deal) {
            if (! is_array($deal)) {
                continue;
            }

            $id = $this->extractShopId($deal);
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        $ids = array_values(array_unique($ids));
        sort($ids);
        return $ids;
    }

    /**
     * @return list<string>
     */
    private function collectDealShopNames(array $deals): array
    {
        $names = [];
        foreach ($deals as $deal) {
            if (! is_array($deal)) {
                continue;
            }

            $name = $this->extractShopName($deal);
            if ($name !== null) {
                $names[] = $name;
            }
        }

        $names = array_values(array_unique($names));
        sort($names, SORT_NATURAL | SORT_FLAG_CASE);
        return $names;
    }

    private function extractDiscount(array $deal): ?int
    {
        $discount = $deal['cut'] ?? $deal['discount'] ?? $deal['priceCut'] ?? null;
        if (is_numeric($discount)) {
            return (int) round((float) $discount);
        }

        return null;
    }

    private function extractMoney(mixed $value): ?string
    {
        if (is_array($value)) {
            $amount = $value['amount'] ?? $value['value'] ?? $value['price'] ?? null;
            if (is_numeric($amount)) {
                return number_format((float) $amount, 2, '.', '');
            }

            $amountInt = $value['amountInt'] ?? $value['amount_int'] ?? null;
            if (is_numeric($amountInt)) {
                return number_format(((float) $amountInt) / 100, 2, '.', '');
            }
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        return null;
    }

    private function markUnavailable(GameStoreListing $listing, CarbonImmutable $now): void
    {
        $listing->fill([
            'current_price' => null,
            'original_price' => null,
            'discount_percent' => null,
            'is_on_sale' => false,
            'is_available' => false,
            'last_synced_at' => $now,
        ]);

        $listing->save();
    }

    private function recordHistory(
        GameStoreListing $listing,
        ?string $price,
        ?string $regular,
        ?int $discount,
        bool $isOnSale,
        CarbonImmutable $now,
    ): bool {
        if ($price === null) {
            return false;
        }

        $latest = GamePrice::query()
            ->where('game_store_listing_id', $listing->id)
            ->orderByDesc('recorded_at')
            ->first();

        if ($latest &&
            $latest->price === $price &&
            $latest->original_price === $regular &&
            $latest->discount_percent === $discount &&
            $latest->is_available === true &&
            $latest->is_on_sale === $isOnSale
        ) {
            return false;
        }

        GamePrice::query()->updateOrCreate(
            [
                'game_store_listing_id' => $listing->id,
                'recorded_at' => $now,
            ],
            [
                'price' => $price,
                'original_price' => $regular,
                'discount_percent' => $discount,
                'is_available' => true,
                'is_on_sale' => $isOnSale,
            ],
        );

        return true;
    }

    private function notifyWishlistUsersForPriceChange(
        GameStoreListing $listing,
        ?string $previousPrice,
        string $newPrice,
        ?string $originalPrice,
        ?int $discountPercent,
        bool $isOnSale,
        CarbonImmutable $now,
    ): void {
        if ($previousPrice === null || $previousPrice === $newPrice) {
            return;
        }

        $wishlistItems = WishlistItem::query()
            ->where('game_id', $listing->game_id)
            ->where('notifications_enabled', true)
            ->with('user')
            ->get()
            ->filter(function (WishlistItem $item) use ($newPrice): bool {
                if ($item->last_notified_price === null) {
                    return true;
                }

                return (string) $item->last_notified_price !== $newPrice;
            });

        if ($wishlistItems->isEmpty()) {
            return;
        }

        $game = $listing->game;
        $store = $listing->store;

        foreach ($wishlistItems as $wishlistItem) {
            $user = $wishlistItem->user;
            if (! $user) {
                continue;
            }

            $user->notify(new GamePriceChangedNotification(
                $game,
                $listing,
                $previousPrice,
                $newPrice,
                $originalPrice,
                $discountPercent,
                $isOnSale,
                $now,
            ));

            $wishlistItem->forceFill([
                'last_notified_price' => $newPrice,
                'last_notified_at' => $now,
            ])->save();
        }
    }

    private function firstShopGameId(mixed $shopIds): ?string
    {
        if (is_string($shopIds) && $shopIds !== '') {
            return $shopIds;
        }

        if (is_array($shopIds)) {
            $first = Arr::first($shopIds, fn ($value) => is_string($value) && $value !== '');
            return is_string($first) ? $first : null;
        }

        return null;
    }

}
