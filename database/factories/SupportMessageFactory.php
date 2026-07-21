<?php

namespace Database\Factories;

use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportMessage>
 */
class SupportMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'status' => 'pending',
            'admin_reply' => null,
            'replied_at' => null,
        ];
    }

    public function replied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'replied',
            'admin_reply' => fake()->paragraph(),
            'replied_at' => now(),
        ]);
    }
}
