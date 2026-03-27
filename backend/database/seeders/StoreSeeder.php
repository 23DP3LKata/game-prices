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
