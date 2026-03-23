<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            [
                'name' => 'Resident Evil Requiem',
                'slug' => 'resident-evil-requiem',
                'description' => null,
                'genre' => null,
                'image_url' => null,
                'developer' => null,
                'publisher' => null,
                'release_date' => null,
                'is_active' => true,
            ],
            [
                'name' => "Sid Meier's Civilization VII",
                'slug' => 'sid-meiers-civilization-vii',
                'description' => null,
                'genre' => null,
                'image_url' => null,
                'developer' => null,
                'publisher' => null,
                'release_date' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Cities: Skylines II',
                'slug' => 'cities-skylines-ii',
                'description' => null,
                'genre' => null,
                'image_url' => null,
                'developer' => null,
                'publisher' => null,
                'release_date' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Slime Rancher 2',
                'slug' => 'slime-rancher-2',
                'description' => null,
                'genre' => null,
                'image_url' => null,
                'developer' => null,
                'publisher' => null,
                'release_date' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Sons Of The Forest',
                'slug' => 'sons-of-the-forest',
                'description' => null,
                'genre' => null,
                'image_url' => null,
                'developer' => null,
                'publisher' => null,
                'release_date' => null,
                'is_active' => true,
            ],
        ];

        foreach ($games as $payload) {
            Game::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                $payload,
            );
        }
    }
}
