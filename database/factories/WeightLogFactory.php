<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WeightLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeightLog>
 */
class WeightLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'weight' => fake()->randomFloat(2, 50, 150),
            'notes' => fake()->optional()->sentence(),
            'logged_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
