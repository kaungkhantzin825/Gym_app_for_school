<?php

namespace Database\Factories;

use App\Models\CameraSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CameraSession>
 */
class CameraSessionFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');
        $duration = fake()->numberBetween(60, 900);
        $sets = fake()->numberBetween(1, 5);
        $reps = fake()->numberBetween(5, 40);

        return [
            'user_id' => User::factory(),
            'exercise_type' => fake()->randomElement(['strength', 'cardio', 'flexibility']),
            'exercise_name' => fake()->randomElement([
                'Push-Up', 'Shoulder Press', 'Bicep Curl', 'Squat',
            ]),
            'started_at' => $startedAt,
            'ended_at' => (clone $startedAt)->modify("+{$duration} seconds"),
            'duration_seconds' => $duration,
            'sets_count' => $sets,
            'total_reps' => $reps,
            'avg_form_score' => fake()->numberBetween(55, 98),
            'sets' => [],
            'source' => 'pose_tracker',
        ];
    }
}
