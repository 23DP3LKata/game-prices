<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'code' => 'steam',
                'name' => 'Steam',
                'website_url' => 'https://store.steampowered.com',
                'itad_shop_id' => config('itad.shops.steam'),
                'priority' => 10,
            ],
            [
                'code' => 'epic',
                'name' => 'Epic Games Store',
                'website_url' => 'https://store.epicgames.com',
                'itad_shop_id' => config('itad.shops.epic'),
                'priority' => 20,
            ],
            [
                'code' => 'microsoft-store',
                'name' => 'Microsoft Store',
                'website_url' => 'https://apps.microsoft.com',
                'itad_shop_id' => config('itad.shops.microsoft_store'),
                'priority' => 30,
            ],
            [
                'code' => '2game',
                'name' => '2Game',
                'website_url' => 'https://2game.com',
                'itad_shop_id' => config('itad.shops.2game'),
                'priority' => 40,
            ],
            [
                'code' => 'greenmangaming',
                'name' => 'Green Man Gaming',
                'website_url' => 'https://www.greenmangaming.com',
                'itad_shop_id' => config('itad.shops.greenmangaming'),
                'priority' => 50,
            ],
            [
                'code' => 'planetplay',
                'name' => 'PlanetPlay',
                'website_url' => 'https://www.planetplay.com',
                'itad_shop_id' => config('itad.shops.planetplay'),
                'priority' => 60,
            ],
            [
                'code' => 'gamesplanet',
                'name' => 'GamesPlanet',
                'website_url' => 'https://gamesplanet.com',
                'itad_shop_id' => config('itad.shops.gamesplanet'),
                'priority' => 70,
            ],
        ];

        foreach ($stores as $payload) {
            Store::query()->updateOrCreate(
                ['code' => $payload['code']],
                [
                    'name' => $payload['name'],
                    'website_url' => $payload['website_url'],
                    'itad_shop_id' => $payload['itad_shop_id'],
                    'is_active' => true,
                    'sync_enabled' => true,
                    'priority' => $payload['priority'],
                ],
            );
        }
    }
}
