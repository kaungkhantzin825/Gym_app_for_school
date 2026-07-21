<?php

namespace Database\Factories;

use App\Models\PhotoGallery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhotoGallery>
 */
class PhotoGalleryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'photo_url' => 'photos/'.fake()->uuid().'.jpg',
            'caption' => fake()->optional()->sentence(),
            'taken_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
