<?php

namespace Database\Seeders;

use App\Models\BookmarkedExercise;
use App\Models\TutorialVideo;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookmarkSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@gym.app')->first();
        $male = User::where('email', 'male@gym.app')->first();
        $female = User::where('email', 'female@gym.app')->first();

        $benchPress = TutorialVideo::where('title', 'Bench Press')->first();
        $inclinePress = TutorialVideo::where('title', 'Incline Bench Press')->first();
        $seatedFly = TutorialVideo::where('title', 'Lever Seated Fly')->first();

        $seedItems = [
            // Sample guest bookmarks for default app_id
            [
                'user_id' => null,
                'app_id' => 'app_demo_guest',
                'tutorial_video_id' => $benchPress?->id,
                'exercise_title' => 'Bench Press',
                'muscle_group' => 'chest',
            ],
            [
                'user_id' => null,
                'app_id' => 'app_demo_guest',
                'tutorial_video_id' => $inclinePress?->id,
                'exercise_title' => 'Incline Bench Press',
                'muscle_group' => 'chest',
            ],
            [
                'user_id' => null,
                'app_id' => 'app_demo_guest',
                'tutorial_video_id' => $seatedFly?->id,
                'exercise_title' => 'Lever Seated Fly',
                'muscle_group' => 'chest',
            ],
        ];

        if ($admin) {
            $seedItems[] = [
                'user_id' => $admin->id,
                'app_id' => null,
                'tutorial_video_id' => $benchPress?->id,
                'exercise_title' => 'Bench Press',
                'muscle_group' => 'chest',
            ];
            $seedItems[] = [
                'user_id' => $admin->id,
                'app_id' => null,
                'tutorial_video_id' => $seatedFly?->id,
                'exercise_title' => 'Lever Seated Fly',
                'muscle_group' => 'chest',
            ];
        }

        if ($male) {
            $seedItems[] = [
                'user_id' => $male->id,
                'app_id' => null,
                'tutorial_video_id' => $benchPress?->id,
                'exercise_title' => 'Bench Press',
                'muscle_group' => 'chest',
            ];
        }

        if ($female) {
            $seedItems[] = [
                'user_id' => $female->id,
                'app_id' => null,
                'tutorial_video_id' => $seatedFly?->id,
                'exercise_title' => 'Lever Seated Fly',
                'muscle_group' => 'chest',
            ];
        }

        foreach ($seedItems as $item) {
            BookmarkedExercise::updateOrCreate(
                [
                    'user_id' => $item['user_id'],
                    'app_id' => $item['app_id'],
                    'exercise_title' => $item['exercise_title'],
                ],
                $item
            );
        }
    }
}
