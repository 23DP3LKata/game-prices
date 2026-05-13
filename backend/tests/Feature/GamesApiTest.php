<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameStoreListing;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_games_index_returns_only_active_games(): void
    {
        Game::factory()->create([
            'name' => 'Active Game',
            'slug' => 'active-game',
            'is_active' => true,
        ]);

        Game::factory()->create([
            'name' => 'Hidden Game',
            'slug' => 'hidden-game',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/games');

        $response->assertOk();
        $response->assertJsonCount(1, 'games');
        $response->assertJsonPath('games.0.slug', 'active-game');
    }

    public function test_games_index_combines_store_and_price_data(): void
    {
        $game = Game::factory()->create([
            'name' => 'Joined Game',
            'slug' => 'joined-game',
            'is_active' => true,
        ]);

        $store = Store::create([
            'code' => 'steam',
            'name' => 'Steam',
            'itad_shop_id' => 1,
            'is_active' => true,
            'sync_enabled' => true,
        ]);

        GameStoreListing::create([
            'game_id' => $game->id,
            'store_id' => $store->id,
            'external_game_id' => 'steam-joined-game',
            'external_url' => 'https://example.com/game',
            'is_active' => true,
            'is_available' => true,
            'current_price' => 9.99,
            'original_price' => 19.99,
            'discount_percent' => 50,
            'is_on_sale' => true,
        ]);

        $response = $this->getJson('/api/games');

        $response->assertOk();
        $response->assertJsonPath('games.0.slug', 'joined-game');
        $response->assertJsonPath('games.0.stores.0.code', 'steam');
        $response->assertJsonPath('games.0.bestPrice', 9.99);
        $response->assertJsonPath('games.0.bestDiscount', 50);
    }

    public function test_games_show_returns_game_details_with_empty_price_placeholders(): void
    {
        $game = Game::factory()->create([
            'name' => 'Game Details',
            'slug' => 'game-details',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/games/'.$game->id);

        $response->assertOk();
        $response->assertJsonPath('game.id', $game->id);
        $response->assertJsonPath('game.slug', 'game-details');
        $response->assertJsonCount(0, 'prices');
        $response->assertJsonCount(0, 'priceHistory');
    }

    public function test_games_show_returns_not_found_for_inactive_game(): void
    {
        $game = Game::factory()->create([
            'slug' => 'inactive-game',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/games/'.$game->id);

        $response->assertNotFound();
    }
}
