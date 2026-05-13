<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMinPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class GameController extends Controller
{
    public function index(): JsonResponse
    {
        $games = Game::query()
            ->where('games.is_active', true)
            ->leftJoin('game_store_listings as gsl', function ($join) {
                $join->on('gsl.game_id', '=', 'games.id')
                    ->where('gsl.is_active', true);
            })
            ->leftJoin('stores as s', function ($join) {
                $join->on('s.id', '=', 'gsl.store_id')
                    ->where('s.is_active', true);
            })
            ->select([
                'games.id',
                'games.name',
                'games.slug',
                'games.genre',
                'games.image_url',
                'games.release_date',
                'games.developer',
                'games.publisher',
                'gsl.current_price as listing_price',
                'gsl.discount_percent as listing_discount_percent',
                's.code as store_code',
                's.name as store_name',
            ])
            ->orderBy('games.name')
            ->get()
            ->groupBy('id')
            ->map(function (Collection $rows) {
                /** @var Game $game */
                $game = $rows->first();

                $stores = $rows
                    ->filter(fn ($row) => filled($row->store_code))
                    ->map(fn ($row) => [
                        'code' => $row->store_code,
                        'name' => $row->store_name,
                    ])
                    ->unique('code')
                    ->values();

                $bestPrice = $rows
                    ->pluck('listing_price')
                    ->filter(fn ($price) => $price !== null)
                    ->map(fn ($price) => (float) $price)
                    ->min();

                $bestDiscount = $rows
                    ->pluck('listing_discount_percent')
                    ->filter(fn ($discount) => $discount !== null)
                    ->map(fn ($discount) => (int) $discount)
                    ->max();

                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'genre' => $game->genre,
                    'developer' => $game->developer,
                    'publisher' => $game->publisher,
                    'logo' => $game->image_url,
                    'releaseDate' => optional($game->release_date)->toDateString(),
                    'stores' => $stores,
                    'bestPrice' => $bestPrice !== null ? round((float) $bestPrice, 2) : null,
                    'bestDiscount' => $bestDiscount !== null ? (int) $bestDiscount : null,
                ];
            })
            ->values();

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
