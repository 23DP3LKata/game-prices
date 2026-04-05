<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GamePrice;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function index(): JsonResponse
    {
        $games = Game::query()
            ->where('is_active', true)
            ->with([
                'storeListings' => function ($query) {
                    $query->where('is_active', true)->with('store');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'genre', 'image_url', 'release_date'])
            ->map(function (Game $game) {
                $storeListings = $game->storeListings;

                $stores = $storeListings
                    ->map(fn ($listing) => [
                        'code' => $listing->store?->code,
                        'name' => $listing->store?->name,
                    ])
                    ->filter(fn (array $store) => filled($store['code']))
                    ->unique('code')
                    ->values();

                $bestPrice = $storeListings
                    ->map(fn ($listing) => $listing->current_price !== null ? (float) $listing->current_price : null)
                    ->filter(fn ($price) => $price !== null)
                    ->min();

                $bestDiscount = $storeListings
                    ->map(fn ($listing) => $listing->discount_percent !== null ? (int) $listing->discount_percent : null)
                    ->filter(fn ($discount) => $discount !== null)
                    ->max();

                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'genre' => $game->genre,
                    'logo' => $game->image_url,
                    'releaseDate' => optional($game->release_date)->toDateString(),
                    'stores' => $stores,
                    'bestPrice' => $bestPrice !== null ? round((float) $bestPrice, 2) : null,
                    'bestDiscount' => $bestDiscount !== null ? (int) $bestDiscount : null,
                ];
            });

        return response()->json([
            'games' => $games,
        ]);
    }

    public function show(Game $game): JsonResponse
    {
        if (! $game->is_active) {
            abort(404);
        }

        $storeListings = $game->storeListings()
            ->where('is_active', true)
            ->with('store')
            ->get();

        $prices = $storeListings->map(fn ($listing) => [
            'store' => [
                'code' => $listing->store?->code,
                'name' => $listing->store?->name,
            ],
            'externalGameId' => $listing->external_game_id,
            'storeUrl' => $listing->external_url,
            'currentPrice' => $listing->current_price,
            'originalPrice' => $listing->original_price,
            'discountPercent' => $listing->discount_percent,
            'isOnSale' => $listing->is_on_sale,
            'isAvailable' => $listing->is_available,
            'lastSyncedAt' => optional($listing->last_synced_at)->toDateTimeString(),
            'currency' => 'EUR',
        ])->values();

        $priceHistory = collect();
        if ($storeListings->isNotEmpty()) {
            $history = GamePrice::query()
                ->whereIn('game_store_listing_id', $storeListings->pluck('id'))
                ->with('gameStoreListing.store')
                ->orderByDesc('recorded_at')
                ->limit(200)
                ->get();

            $priceHistory = $history
                ->groupBy(fn (GamePrice $price) => optional($price->recorded_at)->toDateTimeString())
                ->map(function ($group) {
                    /** @var \Illuminate\Support\Collection<int, GamePrice> $group */
                    $lowest = $group->sortBy(fn (GamePrice $price) => $price->price)->first();
                    if (! $lowest) {
                        return null;
                    }

                    return [
                        'store' => [
                            'code' => $lowest->gameStoreListing?->store?->code,
                            'name' => $lowest->gameStoreListing?->store?->name,
                        ],
                        'externalGameId' => $lowest->gameStoreListing?->external_game_id,
                        'recordedAt' => optional($lowest->recorded_at)->toDateTimeString(),
                        'price' => $lowest->price,
                        'originalPrice' => $lowest->original_price,
                        'discountPercent' => $lowest->discount_percent,
                        'isOnSale' => $lowest->is_on_sale,
                        'isAvailable' => $lowest->is_available,
                        'currency' => 'EUR',
                    ];
                })
                ->filter()
                ->sortByDesc('recordedAt')
                ->take(60)
                ->values();
        }

        return response()->json([
            'game' => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'description' => $game->description,
                'image' => $game->image_url,
                'genre' => $game->genre,
                'releaseDate' => optional($game->release_date)->toDateString(),
                'developer' => $game->developer,
                'publisher' => $game->publisher,
            ],
            'prices' => $prices,
            'priceHistory' => $priceHistory,
        ]);
    }
}
