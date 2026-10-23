<?php

namespace Tests\Feature;

use App\Models\CameraSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CameraSessionApiTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'client_id' => 'device-session-1',
            'exercise_type' => 'strength',
            'exercise_name' => 'Push-Up',
            'started_at' => now()->subMinutes(10)->toDateTimeString(),
            'ended_at' => now()->toDateTimeString(),
            'duration_seconds' => 600,
            'sets_count' => 3,
            'total_reps' => 42,
            'avg_form_score' => 86,
            'sets' => [
                ['set_number' => 1, 'reps' => 14, 'form_score' => 88],
                ['set_number' => 2, 'reps' => 14, 'form_score' => 84],
                ['set_number' => 3, 'reps' => 14, 'form_score' => 86],
            ],
        ], $overrides);
    }

    public function test_it_stores_a_camera_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/camera-sessions', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('session.exercise_name', 'Push-Up')
            ->assertJsonPath('session.total_reps', 42);

        $this->assertDatabaseHas('camera_sessions', [
            'user_id' => $user->id,
            'client_id' => 'device-session-1',
            'total_reps' => 42,
        ]);
    }

    public function test_replaying_the_same_client_id_does_not_duplicate(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/camera-sessions', $this->payload())
            ->assertCreated();

        // The phone retried after a dropped response.
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/camera-sessions', $this->payload())
            ->assertOk();

        $this->assertSame(1, CameraSession::where('user_id', $user->id)->count());
    }

    public function test_it_lists_only_the_users_own_sessions_newest_first(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        CameraSession::factory()->for($user)->create([
            'exercise_name' => 'Older',
            'started_at' => now()->subDay(),
        ]);
        CameraSession::factory()->for($user)->create([
            'exercise_name' => 'Newer',
            'started_at' => now(),
        ]);
        CameraSession::factory()->for($other)->create(['exercise_name' => 'Not yours']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/camera-sessions')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.exercise_name', 'Newer')
            ->assertJsonPath('data.1.exercise_name', 'Older');
    }

    public function test_stats_summarise_reps_and_personal_bests(): void
    {
        $user = User::factory()->create();

        CameraSession::factory()->for($user)->create([
            'exercise_name' => 'Push-Up',
            'total_reps' => 30,
            'avg_form_score' => 80,
            'duration_seconds' => 300,
            'started_at' => now()->subDays(2),
        ]);
        CameraSession::factory()->for($user)->create([
            'exercise_name' => 'Push-Up',
            'total_reps' => 45,
            'avg_form_score' => 90,
            'duration_seconds' => 420,
            'started_at' => now()->subDay(),
        ]);
        CameraSession::factory()->for($user)->create([
            'exercise_name' => 'Squat',
            'total_reps' => 20,
            'avg_form_score' => 70,
            'duration_seconds' => 240,
            'started_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/camera-sessions/stats');

        $response->assertOk()
            ->assertJsonPath('totals.sessions', 3)
            ->assertJsonPath('totals.reps', 95)
            ->assertJsonPath('totals.seconds', 960)
            ->assertJsonPath('this_week', 3);

        $bests = collect($response->json('personal_bests'))
            ->keyBy('exercise_name');

        $this->assertSame(45, $bests['Push-Up']['best_reps']);
        $this->assertSame(90, $bests['Push-Up']['best_form_score']);
        $this->assertSame(20, $bests['Squat']['best_reps']);
    }

    public function test_it_validates_the_payload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/camera-sessions', [
                'exercise_type' => 'strength',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['exercise_name', 'started_at', 'ended_at']);
    }

    public function test_the_endpoints_require_a_token(): void
    {
        $this->getJson('/api/v1/camera-sessions')->assertUnauthorized();
        $this->getJson('/api/v1/camera-sessions/stats')->assertUnauthorized();
    }
}
