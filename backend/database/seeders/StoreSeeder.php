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
                'itad_shop_id' => config('itad.shops.steam'),
            ],
            [
                'code' => 'epic',
                'name' => 'Epic Games Store',
                'itad_shop_id' => config('itad.shops.epic'),
            ],
            [
                'code' => 'microsoft-store',
                'name' => 'Microsoft Store',
                'itad_shop_id' => config('itad.shops.microsoft_store'),
            ],
            [
                'code' => '2game',
                'name' => '2Game',
                'itad_shop_id' => config('itad.shops.2game'),
            ],
            [
                'code' => 'greenmangaming',
                'name' => 'Green Man Gaming',
                'itad_shop_id' => config('itad.shops.greenmangaming'),
            ],
            [
                'code' => 'planetplay',
                'name' => 'PlanetPlay',
                'itad_shop_id' => config('itad.shops.planetplay'),
            ],
            [
                'code' => 'gamesplanet',
                'name' => 'GamesPlanet',
                'itad_shop_id' => config('itad.shops.gamesplanet'),
            ],
        ];

        foreach ($stores as $payload) {
            Store::query()->updateOrCreate(
                ['code' => $payload['code']],
                [
                    'name' => $payload['name'],
                    'itad_shop_id' => $payload['itad_shop_id'],
                    'is_active' => true,
                    'sync_enabled' => true,
                ],
            );
        }
    }
}
