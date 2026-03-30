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
                'description' => 'A survival horror story focused on investigation, tension, and scarce resources inside a decaying facility.',
                'genre' => 'Survival Horror',
                'image_url' => 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/3764200/ce5437442768e38eb575f205ab9397d0264017b0/header.jpg?t=1772587704',
                'developer' => 'Capcom',
                'publisher' => 'Capcom',
                'release_date' => '2025-03-21',
                'is_active' => true,
            ],
            [
                'name' => "Sid Meier's Civilization VII",
                'slug' => 'sid-meiers-civilization-vii',
                'description' => 'The next era of turn-based strategy where you expand, negotiate, and build a legacy through the ages.',
                'genre' => 'Strategy',
                'image_url' => 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1295660/header.jpg?t=1770414576',
                'developer' => 'Firaxis Games',
                'publisher' => '2K',
                'release_date' => '2025-06-01',
                'is_active' => true,
            ],
            [
                'name' => 'Cities: Skylines II',
                'slug' => 'cities-skylines-ii',
                'description' => 'A modern city builder with deep simulation, layered infrastructure, and large-scale urban growth.',
                'genre' => 'Simulation',
                'image_url' => 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/949230/header.jpg?t=1773949108',
                'developer' => 'Colossal Order',
                'publisher' => 'Paradox Interactive',
                'release_date' => '2023-10-24',
                'is_active' => true,
            ],
            [
                'name' => 'Slime Rancher 2',
                'slug' => 'slime-rancher-2',
                'description' => 'Explore a vibrant alien world, collect new slimes, and build a colorful ranch in this cozy adventure.',
                'genre' => 'Adventure',
                'image_url' => 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1657630/e0ebd5982e69895132d4ffdf80a1a6048ab53d25/header.jpg?t=1765374630',
                'developer' => 'Monomi Park',
                'publisher' => 'Monomi Park',
                'release_date' => '2022-09-22',
                'is_active' => true,
            ],
            [
                'name' => 'Sons Of The Forest',
                'slug' => 'sons-of-the-forest',
                'description' => 'Survive on a remote island, build defenses, and uncover a dark mystery in this open-world survival horror.',
                'genre' => 'Survival',
                'image_url' => 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1326470/header.jpg?t=1708624856',
                'developer' => 'Endnight Games',
                'publisher' => 'Newnight',
                'release_date' => '2024-02-22',
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
