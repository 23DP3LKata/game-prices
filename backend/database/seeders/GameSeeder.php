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
                'name' => 'Cyber Frontier 2078',
                'slug' => 'cyber-frontier-2078',
                'description' => 'Explore a neon megacity, complete side missions, and shape your own story across multiple districts.',
                'genre' => 'Action, RPG',
                'image_url' => null,
                'developer' => 'Neon Forge Studio',
                'publisher' => 'Apex Pixel',
                'release_date' => '2025-02-14',
                'is_active' => true,
            ],
            [
                'name' => 'Kingdoms of Emberfall',
                'slug' => 'kingdoms-of-emberfall',
                'description' => 'Build an army, conquer provinces, and defend your lands in a tactical fantasy world.',
                'genre' => 'Strategy',
                'image_url' => null,
                'developer' => 'Iron Oak Interactive',
                'publisher' => 'Iron Oak Interactive',
                'release_date' => '2024-11-08',
                'is_active' => true,
            ],
            [
                'name' => 'Drift Horizon X',
                'slug' => 'drift-horizon-x',
                'description' => 'Compete in high-speed races with a deep customization system and global leaderboards.',
                'genre' => 'Racing',
                'image_url' => null,
                'developer' => 'Vertex Wheels',
                'publisher' => 'Skyline Digital',
                'release_date' => '2025-06-30',
                'is_active' => true,
            ],
            [
                'name' => 'Aether Isles',
                'slug' => 'aether-isles',
                'description' => 'Sail floating islands, gather ancient artifacts, and uncover secrets of a lost civilization.',
                'genre' => 'Adventure',
                'image_url' => null,
                'developer' => 'Blue Lantern Games',
                'publisher' => 'Blue Lantern Games',
                'release_date' => '2023-09-21',
                'is_active' => true,
            ],
            [
                'name' => 'Factory Protocol',
                'slug' => 'factory-protocol',
                'description' => 'Automate production lines, optimize logistics, and scale your industrial empire.',
                'genre' => 'Simulation',
                'image_url' => null,
                'developer' => 'Gridline Labs',
                'publisher' => 'Gridline Labs',
                'release_date' => '2024-04-12',
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
