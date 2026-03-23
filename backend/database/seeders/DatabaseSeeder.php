<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StoreSeeder::class);
        $this->call(GameSeeder::class);
        $this->call(GameStoreListingSeeder::class);

        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'nickname' => 'Test User',
                'password' => 'password',
                'role' => 'user',
            ],
        );
    }
}
