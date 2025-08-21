<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $matches = $this->faker->numberBetween(0, 100);
        $wins = $this->faker->numberBetween(0, $matches);

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'image' => $this->faker->optional()->imageUrl(200, 200, 'people'),
            'nickname' => $this->faker->optional()->userName(),
            'goals' => $this->faker->numberBetween(0, 50),
            'assists' => $this->faker->numberBetween(0, 30),
            'tackles' => $this->faker->numberBetween(0, 40),
            'mvps' => $this->faker->numberBetween(0, 10),
            'wins' => $wins,
            'matches' => $matches,
            'average_rating' => $this->faker->randomFloat(2, 0, 10),
        ];
    }

    /**
     * State para jogador novato (sem estatísticas)
     */
    public function newbie(): static
    {
        return $this->state(fn (array $attributes) => [
            'goals' => 0,
            'assists' => 0,
            'tackles' => 0,
            'mvps' => 0,
            'wins' => 0,
            'matches' => 0,
            'average_rating' => 0.00,
        ]);
    }
}
