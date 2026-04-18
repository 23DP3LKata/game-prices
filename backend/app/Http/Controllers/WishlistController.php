<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wishlistItems = WishlistItem::query()
            ->where('user_id', $request->user()->id)
            ->with(['game.storeListings.store'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (WishlistItem $item): array {
                $game = $item->game;
                $bestPrice = $game?->storeListings
                    ->filter(fn ($listing) => $listing->current_price !== null)
                    ->min(fn ($listing) => (float) $listing->current_price);

                return [
                    'id' => $item->id,
                    'notificationsEnabled' => $item->notifications_enabled,
                    'lastNotifiedPrice' => $item->last_notified_price !== null ? (float) $item->last_notified_price : null,
                    'lastNotifiedAt' => optional($item->last_notified_at)->toDateTimeString(),
                    'game' => [
                        'id' => $game?->id,
                        'name' => $game?->name,
                        'slug' => $game?->slug,
                        'genre' => $game?->genre,
                        'image' => $game?->image_url,
                        'releaseDate' => optional($game?->release_date)->toDateString(),
                    ],
                    'bestPrice' => $bestPrice !== null ? round((float) $bestPrice, 2) : null,
                ];
            })
            ->values();

        return response()->json([
            'wishlist' => $wishlistItems,
        ]);
    }

    public function store(Request $request, Game $game): JsonResponse
    {
        if (! $game->is_active) {
            abort(404);
        }

        $validated = $request->validate([
            'notifications_enabled' => ['sometimes', 'boolean'],
        ]);

        $wishlistItem = WishlistItem::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'game_id' => $game->id,
            ],
            [
                'notifications_enabled' => $validated['notifications_enabled'] ?? true,
            ],
        );

        return response()->json([
            'message' => 'Game added to wishlist.',
            'wishlistItem' => [
                'id' => $wishlistItem->id,
                'gameId' => $wishlistItem->game_id,
                'notificationsEnabled' => $wishlistItem->notifications_enabled,
                'lastNotifiedPrice' => $wishlistItem->last_notified_price !== null ? (float) $wishlistItem->last_notified_price : null,
                'lastNotifiedAt' => optional($wishlistItem->last_notified_at)->toDateTimeString(),
            ],
        ], 201);
    }

    public function status(Request $request, Game $game): JsonResponse
    {
        if (! $game->is_active) {
            abort(404);
        }

        $isWishlisted = WishlistItem::query()
            ->where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->exists();

        return response()->json([
            'isWishlisted' => $isWishlisted,
        ]);
    }

    public function destroy(Request $request, Game $game): JsonResponse
    {
        WishlistItem::query()
            ->where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->delete();

        return response()->json([
            'message' => 'Game removed from wishlist.',
        ]);
    }
}
