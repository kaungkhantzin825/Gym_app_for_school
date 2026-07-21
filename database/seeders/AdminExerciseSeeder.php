<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AdminExerciseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@gym.app'],
            User::factory()->admin()->make([
                'name' => 'Admin',
                'email' => 'admin@gym.app',
            ])->toArray(),
        );

        foreach ($this->exercises() as $exercise) {
            Exercise::query()->updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'exercise_name' => $exercise['exercise_name'],
                ],
                [
                    'exercise_type' => $exercise['exercise_type'],
                    'exercise_tutorial_url' => $exercise['exercise_tutorial_url'],
                    'duration_minutes' => $exercise['duration_minutes'],
                    'calories_burned' => null,
                    'sets' => null,
                    'reps' => null,
                    'weight' => null,
                    'exercise_time' => now(),
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, int|string|null>>
     */
    private function exercises(): array
    {
        return [
            [
                'exercise_name' => 'Bench Press',
                'exercise_type' => 'strength',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => null,
            ],
            [
                'exercise_name' => 'Squat',
                'exercise_type' => 'strength',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => null,
            ],
            [
                'exercise_name' => 'Deadlift',
                'exercise_type' => 'strength',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => null,
            ],
            [
                'exercise_name' => 'Push-ups',
                'exercise_type' => 'strength',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => null,
            ],
            [
                'exercise_name' => 'Pull-ups',
                'exercise_type' => 'strength',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => null,
            ],
            [
                'exercise_name' => 'Running',
                'exercise_type' => 'cardio',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => 20,
            ],
            [
                'exercise_name' => 'Cycling',
                'exercise_type' => 'cardio',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => 20,
            ],
            [
                'exercise_name' => 'Jump Rope',
                'exercise_type' => 'cardio',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => 10,
            ],
            [
                'exercise_name' => 'Yoga',
                'exercise_type' => 'flexibility',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => 15,
            ],
            [
                'exercise_name' => 'Dynamic Stretching',
                'exercise_type' => 'flexibility',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => 10,
            ],
            [
                'exercise_name' => 'Burpees',
                'exercise_type' => 'sports',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => 10,
            ],
            [
                'exercise_name' => 'Mountain Climbers',
                'exercise_type' => 'sports',
                'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
                'duration_minutes' => 10,
            ],
        ];
    }
}
