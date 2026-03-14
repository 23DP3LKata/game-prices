<?php

namespace App\Http\Controllers;

use App\Models\Game;
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
            // Price API integration will be added in a future iteration.
            'prices' => [],
            'priceHistory' => [],
        ]);
    }
}
