<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMinPrice;
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

        $priceHistory = GameMinPrice::query()
            ->where('game_id', $game->id)
            ->with('gameStoreListing.store')
            ->orderByDesc('recorded_at')
            ->limit(60)
            ->get()
            ->map(fn (GameMinPrice $point) => [
                'store' => [
                    'code' => $point->gameStoreListing?->store?->code,
                    'name' => $point->gameStoreListing?->store?->name,
                ],
                'externalGameId' => $point->gameStoreListing?->external_game_id,
                'recordedAt' => optional($point->recorded_at)->toDateTimeString(),
                'price' => $point->price,
                'originalPrice' => $point->original_price,
                'discountPercent' => $point->discount_percent,
                'isOnSale' => $point->is_on_sale,
                'isAvailable' => true,
                'currency' => 'EUR',
            ])
            ->values();

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
