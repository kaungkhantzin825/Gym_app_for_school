<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'age' => fake()->numberBetween(18, 60),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'height' => fake()->randomFloat(2, 150, 200),
            'current_weight' => fake()->randomFloat(2, 50, 120),
            'target_weight' => fake()->randomFloat(2, 50, 120),
            'goal' => fake()->randomElement(['lose_weight', 'gain_weight', 'maintain', 'build_muscle']),
            'activity_level' => fake()->randomElement(['sedentary', 'light', 'moderate', 'active', 'very_active']),
            'daily_calories_target' => fake()->randomFloat(2, 1500, 3500),
            'daily_protein_target' => fake()->randomFloat(2, 50, 200),
            'daily_carbs_target' => fake()->randomFloat(2, 100, 400),
            'daily_fat_target' => fake()->randomFloat(2, 40, 150),
        ];
    }
}
