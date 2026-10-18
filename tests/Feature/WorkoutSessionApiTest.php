<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutSessionApiTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Upper Body · AI plan',
            'source' => 'ai_plan',
            'started_at' => '2026-10-18T09:00:00+08:00',
            'ended_at' => '2026-10-18T09:42:00+08:00',
            'exercises' => [
                [
                    'name' => 'Push Up',
                    'type' => 'strength',
                    'sets' => [
                        ['set_number' => 1, 'reps' => 12, 'rest_time_seconds' => 60, 'form_score' => 90],
                        ['set_number' => 2, 'reps' => 10, 'rest_time_seconds' => 60, 'form_score' => 80],
                    ],
                ],
                [
                    'name' => 'Running',
                    'type' => 'cardio',
                    'sets' => [
                        ['set_number' => 1, 'reps' => 1, 'weight' => 0],
                    ],
                ],
            ],
        ], $overrides);
    }

    public function test_guests_cannot_save_a_session(): void
    {
        $this->postJson('/api/v1/workouts/session', $this->payload())
            ->assertUnauthorized();
    }

    public function test_it_persists_the_workout_exercises_and_every_set(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts/session', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('workout.name', 'Upper Body · AI plan')
            ->assertJsonPath('summary.total_sets', 3)
            ->assertJsonPath('summary.total_reps', 23)
            ->assertJsonPath('summary.exercise_count', 2)
            ->assertJsonPath('summary.duration_minutes', 42);

        $this->assertEquals(85.0, $response->json('summary.average_form_score'));

        $workout = Workout::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(42, $workout->total_duration_minutes);
        $this->assertSame(3, $workout->workoutSets()->count());

        $this->assertDatabaseCount('workouts', 1);
        $this->assertDatabaseCount('exercises', 2);
        $this->assertDatabaseCount('workout_sets', 3);

        $this->assertDatabaseHas('workout_sets', [
            'workout_id' => $workout->id,
            'set_number' => 1,
            'reps' => 12,
            'rest_time_seconds' => 60,
            'form_score' => 90,
        ]);

        $exercise = Exercise::where('exercise_name', 'Push Up')->firstOrFail();
        $this->assertSame('strength', $exercise->exercise_type);
        $this->assertSame(2, $exercise->sets);
        $this->assertSame(22, $exercise->reps);
        $this->assertSame($user->id, $exercise->user_id);
    }

    public function test_it_computes_volume_from_reps_times_weight(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts/session', $this->payload([
                'exercises' => [
                    [
                        'name' => 'Bench Press',
                        'type' => 'strength',
                        'sets' => [
                            ['set_number' => 1, 'reps' => 10, 'weight' => 60],
                            ['set_number' => 2, 'reps' => 8, 'weight' => 70],
                        ],
                    ],
                ],
            ]));

        $response->assertCreated()->assertJsonPath('summary.total_reps', 18);
        $this->assertEquals(1160.0, $response->json('summary.total_volume'));
    }

    public function test_form_score_is_null_when_no_camera_ran(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts/session', $this->payload([
                'exercises' => [
                    [
                        'name' => 'Squat',
                        'type' => 'strength',
                        'sets' => [['set_number' => 1, 'reps' => 8]],
                    ],
                ],
            ]));

        $response->assertCreated()
            ->assertJsonPath('summary.average_form_score', null);

        $this->assertDatabaseHas('workout_sets', [
            'set_number' => 1,
            'form_score' => null,
        ]);
    }

    public function test_it_requires_at_least_one_exercise_and_set(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts/session', $this->payload(['exercises' => []]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('exercises');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts/session', $this->payload([
                'exercises' => [
                    ['name' => 'Push Up', 'type' => 'strength', 'sets' => []],
                ],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('exercises.0.sets');
    }

    public function test_it_rejects_an_impossible_form_score(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts/session', $this->payload([
                'exercises' => [
                    [
                        'name' => 'Push Up',
                        'type' => 'strength',
                        'sets' => [['set_number' => 1, 'reps' => 5, 'form_score' => 140]],
                    ],
                ],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('exercises.0.sets.0.form_score');
    }

    public function test_an_ended_at_before_started_at_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts/session', $this->payload([
                'started_at' => '2026-10-18T09:00:00+08:00',
                'ended_at' => '2026-10-18T08:00:00+08:00',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ended_at');
    }

    public function test_the_saved_session_shows_up_in_workout_history(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts/session', $this->payload())
            ->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/workouts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Upper Body · AI plan')
            ->assertJsonCount(3, 'data.0.workout_sets');
    }

    public function test_sessions_are_never_written_to_another_user(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts/session', $this->payload())
            ->assertCreated();

        $this->assertSame(0, Workout::where('user_id', $stranger->id)->count());
        $this->assertSame(0, Exercise::where('user_id', $stranger->id)->count());
        $this->assertSame(0, WorkoutSet::whereHas('workout', function ($query) use ($stranger) {
            $query->where('user_id', $stranger->id);
        })->count());
    }
}
