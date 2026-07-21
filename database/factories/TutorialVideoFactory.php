<?php

namespace Database\Factories;

use App\Models\TutorialVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TutorialVideo>
 */
class TutorialVideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'video_url' => 'https://example.com/videos/'.fake()->uuid().'.mp4',
            'thumbnail_url' => 'https://example.com/thumbnails/'.fake()->uuid().'.jpg',
            'gender_target' => fake()->randomElement(['boy', 'girl', 'both']),
            'muscle_group' => fake()->randomElement([
                'chest', 'back', 'shoulders', 'arms', 'legs', 'core', 'full_body',
            ]),
        ];
    }
}
