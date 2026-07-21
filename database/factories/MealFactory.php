<?php

namespace Database\Factories;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meal>
 */
class MealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['breakfast', 'lunch', 'dinner', 'snack']),
            'photo_path' => fake()->optional()->filePath(),
            'notes' => fake()->optional()->sentence(),
            'total_calories' => fake()->randomFloat(2, 100, 1200),
            'total_protein' => fake()->randomFloat(2, 5, 80),
            'total_carbs' => fake()->randomFloat(2, 10, 150),
            'total_fat' => fake()->randomFloat(2, 5, 60),
            'meal_time' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
