<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workout>
 */
class WorkoutFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');
        $endedAt = (clone $startedAt)->modify('+'.fake()->numberBetween(30, 120).' minutes');

        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement([
                'Morning Workout', 'Leg Day', 'Upper Body', 'Full Body',
                'Push Day', 'Pull Day', 'Cardio Session',
            ]),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'notes' => fake()->optional()->sentence(),
            'total_volume' => fake()->randomFloat(2, 1000, 50000),
            'total_duration_minutes' => fake()->numberBetween(30, 120),
        ];
    }
}
