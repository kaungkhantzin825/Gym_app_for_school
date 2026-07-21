<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['cardio', 'strength', 'flexibility', 'sports']);

        return [
            'user_id' => User::factory(),
            'exercise_name' => fake()->randomElement([
                'Bench Press', 'Squat', 'Deadlift', 'Running',
                'Cycling', 'Yoga', 'Push-ups', 'Pull-ups',
            ]),
            'exercise_type' => $type,
            'duration_minutes' => $type === 'cardio' ? fake()->numberBetween(10, 90) : null,
            'calories_burned' => fake()->randomFloat(2, 50, 800),
            'sets' => $type === 'strength' ? fake()->numberBetween(2, 6) : null,
            'reps' => $type === 'strength' ? fake()->numberBetween(5, 20) : null,
            'weight' => $type === 'strength' ? fake()->randomFloat(2, 5, 200) : null,
            'exercise_time' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
