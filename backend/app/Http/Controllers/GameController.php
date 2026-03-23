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
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'genre', 'image_url'])
            ->map(fn (Game $game) => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'genre' => $game->genre,
                'logo' => $game->image_url,
            ]);

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
            $priceHistory = GamePrice::query()
                ->whereIn('game_store_listing_id', $storeListings->pluck('id'))
                ->with('gameStoreListing.store')
                ->orderByDesc('recorded_at')
                ->limit(60)
                ->get()
                ->map(fn (GamePrice $price) => [
                    'store' => [
                        'code' => $price->gameStoreListing?->store?->code,
                        'name' => $price->gameStoreListing?->store?->name,
                    ],
                    'externalGameId' => $price->gameStoreListing?->external_game_id,
                    'recordedAt' => optional($price->recorded_at)->toDateTimeString(),
                    'price' => $price->price,
                    'originalPrice' => $price->original_price,
                    'discountPercent' => $price->discount_percent,
                    'isOnSale' => $price->is_on_sale,
                    'isAvailable' => $price->is_available,
                    'currency' => 'EUR',
                ])->values();
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
