<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        Store::query()->updateOrCreate(
            ['code' => 'steam'],
            [
                'name' => 'Steam',
                'website_url' => 'https://store.steampowered.com',
                'is_active' => true,
                'sync_enabled' => true,
                'priority' => 10,
            ],
        );
    }
}
