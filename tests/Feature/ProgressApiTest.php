<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProgressApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<int, array{0:int, 1:float|null}>  $sets  [reps, weight]
     */
    private function logSession(
        User $user,
        string $exerciseName,
        Carbon $startedAt,
        array $sets,
        string $type = 'strength',
        ?int $minutes = 40,
    ): Workout {
        $workout = Workout::factory()->for($user)->create([
            'started_at' => $startedAt,
            'total_duration_minutes' => $minutes,
        ]);

        $exercise = Exercise::factory()->for($user)->create([
            'exercise_name' => $exerciseName,
            'exercise_type' => $type,
            'exercise_time' => $startedAt,
        ]);

        foreach ($sets as $index => [$reps, $weight]) {
            WorkoutSet::factory()->for($workout)->for($exercise)->create([
                'set_number' => $index + 1,
                'reps' => $reps,
                'weight' => $weight,
            ]);
        }

        return $workout;
    }

    public function test_guests_cannot_read_progress(): void
    {
        $this->getJson('/api/v1/progress')->assertUnauthorized();
    }

    public function test_a_new_user_gets_an_empty_but_well_formed_payload(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/progress');

        $response->assertOk()
            ->assertJsonPath('totals.workouts', 0)
            ->assertJsonPath('totals.sets', 0)
            ->assertJsonPath('totals.reps', 0)
            ->assertJsonPath('exercises', []);

        // The chart always gets eight buckets so it can render without gaps.
        $this->assertCount(8, $response->json('weekly'));
        $this->assertSame(0, $response->json('weekly.7.sets'));
    }

    public function test_totals_cover_workouts_sets_reps_volume_and_minutes(): void
    {
        $user = User::factory()->create();

        $this->logSession($user, 'Bench Press', now()->subDays(2), [[10, 60.0], [8, 70.0]], minutes: 45);
        $this->logSession($user, 'Squat', now()->subDay(), [[5, 100.0]], minutes: 30);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/progress');

        $response->assertOk()
            ->assertJsonPath('totals.workouts', 2)
            ->assertJsonPath('totals.sets', 3)
            ->assertJsonPath('totals.reps', 23)
            ->assertJsonPath('totals.minutes', 75);

        // 10*60 + 8*70 + 5*100
        $this->assertEquals(1660.0, $response->json('totals.volume'));
    }

    public function test_it_aggregates_each_exercise(): void
    {
        $user = User::factory()->create();

        $this->logSession($user, 'Bench Press', now()->subDays(7), [[8, 60.0]]);
        $this->logSession($user, 'Bench Press', now()->subDay(), [[10, 70.0], [9, 70.0]]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/progress');

        $response->assertOk()
            ->assertJsonPath('exercises.0.name', 'Bench Press')
            ->assertJsonPath('exercises.0.type', 'strength')
            ->assertJsonPath('exercises.0.sessions', 2)
            ->assertJsonPath('exercises.0.sets', 3)
            ->assertJsonPath('exercises.0.best_reps', 10);

        $this->assertEquals(70.0, $response->json('exercises.0.best_weight'));
        // Epley on the best set: 70 * (1 + 10/30)
        $this->assertEquals(93.3, $response->json('exercises.0.estimated_1rm'));
    }

    public function test_trend_compares_the_last_two_sessions(): void
    {
        $user = User::factory()->create();

        $this->logSession($user, 'Bench Press', now()->subDays(14), [[8, 60.0]]);
        $this->logSession($user, 'Bench Press', now()->subDay(), [[8, 70.0]]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/progress')
            ->assertOk()
            ->assertJsonPath('exercises.0.trend', 'up');
    }

    public function test_trend_is_flat_when_the_load_barely_moves(): void
    {
        $user = User::factory()->create();

        $this->logSession($user, 'Bench Press', now()->subDays(14), [[8, 60.0]]);
        $this->logSession($user, 'Bench Press', now()->subDay(), [[8, 60.0]]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/progress')
            ->assertOk()
            ->assertJsonPath('exercises.0.trend', 'flat');
    }

    public function test_trend_is_new_with_a_single_session(): void
    {
        $user = User::factory()->create();

        $this->logSession($user, 'Bench Press', now()->subDay(), [[8, 60.0]]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/progress')
            ->assertOk()
            ->assertJsonPath('exercises.0.trend', 'new');
    }

    public function test_it_adds_weight_once_every_set_hits_ten_reps(): void
    {
        $user = User::factory()->create();

        $this->logSession($user, 'Bench Press', now()->subDay(), [[10, 60.0], [11, 60.0]]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/progress');

        $response->assertOk()
            ->assertJsonPath('exercises.0.next_target.reason', 'increase_weight')
            ->assertJsonPath('exercises.0.next_target.reps', 8);

        $this->assertEquals(62.5, $response->json('exercises.0.next_target.weight'));
    }

    public function test_it_chases_a_rep_before_adding_weight(): void
    {
        $user = User::factory()->create();

        $this->logSession($user, 'Bench Press', now()->subDay(), [[8, 60.0], [9, 60.0]]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/progress');

        $response->assertOk()
            ->assertJsonPath('exercises.0.next_target.reason', 'add_rep')
            ->assertJsonPath('exercises.0.next_target.reps', 10);

        $this->assertEquals(60.0, $response->json('exercises.0.next_target.weight'));
    }

    public function test_bodyweight_movements_only_progress_on_reps(): void
    {
        $user = User::factory()->create();

        $this->logSession($user, 'Push-Up', now()->subDay(), [[12, null]], type: 'strength');

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/progress');

        $response->assertOk()
            ->assertJsonPath('exercises.0.best_weight', null)
            ->assertJsonPath('exercises.0.estimated_1rm', null)
            ->assertJsonPath('exercises.0.next_target.weight', null)
            ->assertJsonPath('exercises.0.next_target.reps', 13);
    }

    public function test_weekly_volume_puts_todays_sets_in_the_last_bucket(): void
    {
        $user = User::factory()->create();

        $this->logSession($user, 'Squat', now(), [[5, 100.0]]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/progress');

        $weekly = $response->json('weekly');
        $this->assertCount(8, $weekly);
        $this->assertSame(1, $weekly[7]['sets']);
        $this->assertEquals(500.0, $weekly[7]['volume']);
        $this->assertSame(0, $weekly[0]['sets']);
    }

    public function test_progress_never_leaks_another_users_training(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $this->logSession($stranger, 'Bench Press', now(), [[10, 100.0]]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/progress');

        $response->assertOk()
            ->assertJsonPath('totals.workouts', 0)
            ->assertJsonPath('exercises', []);
    }
}
