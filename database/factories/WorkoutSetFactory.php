<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutSet>
 */
class WorkoutSetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workout_id' => Workout::factory(),
            'exercise_id' => Exercise::factory(),
            'set_number' => fake()->numberBetween(1, 5),
            'reps' => fake()->numberBetween(5, 20),
            'weight' => fake()->randomFloat(2, 10, 200),
            'rpe' => fake()->optional()->numberBetween(1, 10),
            'rest_time_seconds' => fake()->optional()->numberBetween(30, 180),
            'is_superset' => fake()->boolean(20),
        ];
    }
}
