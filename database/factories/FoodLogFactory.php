<?php

namespace Database\Factories;

use App\Models\FoodLog;
use App\Models\Meal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FoodLog>
 */
class FoodLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'meal_id' => Meal::factory(),
            'food_name' => fake()->word(),
            'fatsecret_food_id' => fake()->optional()->numerify('######'),
            'brand_name' => fake()->optional()->company(),
            'serving_size' => fake()->randomFloat(2, 1, 500),
            'serving_unit' => fake()->randomElement(['g', 'ml', 'oz', 'cup', 'tbsp']),
            'calories' => fake()->randomFloat(2, 10, 800),
            'protein' => fake()->randomFloat(2, 0, 50),
            'carbs' => fake()->randomFloat(2, 0, 100),
            'fat' => fake()->randomFloat(2, 0, 40),
            'fiber' => fake()->optional()->randomFloat(2, 0, 15),
            'sugar' => fake()->optional()->randomFloat(2, 0, 30),
            'sodium' => fake()->optional()->randomFloat(2, 0, 1000),
        ];
    }
}
