<?php

namespace App\Services\Stores;

use App\Models\GamePrice;
use App\Models\GameStoreListing;
use App\Models\Store;
use App\Services\Stores\DTO\SteamPriceData;
use Carbon\CarbonImmutable;

class SteamPriceSyncService
{
    public function __construct(private readonly SteamPriceClient $client)
    {
    }

    /**
     * @return array{synced:int, updated:int, skipped:int, failed:int}
     */
    public function syncStoreListings(Store $store): array
    {
        $listings = $store->listings()
            ->where('is_active', true)
            ->whereHas('game', fn ($query) => $query->where('is_active', true))
            ->get();

        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        $now = CarbonImmutable::now()->startOfSecond();

        foreach ($listings as $listing) {
            $stats['synced']++;

            $priceData = $this->client->fetchPrice($listing->external_game_id);
            if ($priceData === null) {
                $stats['failed']++;
                continue;
            }

            $this->updateListing($listing, $priceData, $now);
            $stats['updated']++;

            if ($priceData->hasPrice()) {
                if (! $this->recordHistory($listing, $priceData, $now)) {
                    $stats['skipped']++;
                }
            } else {
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    private function updateListing(GameStoreListing $listing, SteamPriceData $priceData, CarbonImmutable $now): void
    {
        $listing->fill([
            'title_in_store' => $priceData->title ?? $listing->title_in_store,
            'external_url' => $listing->external_url ?? $this->steamUrl($listing->external_game_id),
            'current_price' => $priceData->price,
            'original_price' => $priceData->originalPrice,
            'discount_percent' => $priceData->discountPercent,
            'is_on_sale' => $priceData->isOnSale,
            'is_available' => $priceData->isAvailable,
            'last_synced_at' => $now,
        ]);

        $listing->save();
    }

    private function recordHistory(GameStoreListing $listing, SteamPriceData $priceData, CarbonImmutable $now): bool
    {
        $latest = GamePrice::query()
            ->where('game_store_listing_id', $listing->id)
            ->orderByDesc('recorded_at')
            ->first();

        if ($latest &&
            $latest->price === $priceData->price &&
            $latest->original_price === $priceData->originalPrice &&
            $latest->discount_percent === $priceData->discountPercent &&
            $latest->is_available === $priceData->isAvailable &&
            $latest->is_on_sale === $priceData->isOnSale
        ) {
            return false;
        }

        GamePrice::query()->updateOrCreate(
            [
                'game_store_listing_id' => $listing->id,
                'recorded_at' => $now,
            ],
            [
                'price' => $priceData->price,
                'original_price' => $priceData->originalPrice,
                'discount_percent' => $priceData->discountPercent,
                'is_available' => $priceData->isAvailable,
                'is_on_sale' => $priceData->isOnSale,
            ],
        );

        return true;
    }

    private function steamUrl(string $appId): string
    {
        return 'https://store.steampowered.com/app/'.urlencode($appId).'/';
    }
}
