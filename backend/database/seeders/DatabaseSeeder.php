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
        $this->call(GameSeeder::class);

        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'nickname' => 'Test User',
                'password' => 'password',
                'role' => 'user',
            ],
        );

        if (Game::query()->count() < 8) {
            Game::factory()->count(3)->create();
        }
    }
}
