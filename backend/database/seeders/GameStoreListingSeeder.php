<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameStoreListing;
use App\Models\Store;
use Illuminate\Database\Seeder;

class GameStoreListingSeeder extends Seeder
{
    public function run(): void
    {
        $steamMap = [
            'resident-evil-requiem' => '3764200',
            'sid-meiers-civilization-vii' => '1295660',
            'cities-skylines-ii' => '949230',
            'slime-rancher-2' => '1657630',
            'sons-of-the-forest' => '1326470',
        ];

        $store = Store::query()->where('code', 'steam')->first();
        if (! $store) {
            return;
        }

        $games = Game::query()->get();

        foreach ($games as $game) {
            $steamAppId = $steamMap[$game->slug] ?? null;
            if (! $steamAppId) {
                continue;
            }

            GameStoreListing::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'game_id' => $game->id,
                ],
                [
                    'external_game_id' => 'app/'.$steamAppId,
                    'title_in_store' => $game->name,
                    'external_url' => $this->steamUrl($steamAppId),
                    'is_active' => true,
                    'is_available' => true,
                ],
            );
        }
    }

    private function steamUrl(string $appId): string
    {
        return 'https://store.steampowered.com/app/'.urlencode($appId).'/';
    }
}
