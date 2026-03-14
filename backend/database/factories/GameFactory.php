<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(2);

        return [
            'name' => $title,
            'slug' => Str::slug($title.'-'.fake()->unique()->numberBetween(100, 999)),
            'description' => fake()->paragraph(3),
            'genre' => fake()->randomElement([
                'Action',
                'Action, Adventure',
                'RPG',
                'Strategy',
                'Simulation',
                'Indie',
            ]),
            'image_url' => null,
            'developer' => fake()->company(),
            'publisher' => fake()->company(),
            'release_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'is_active' => true,
        ];
    }
}
