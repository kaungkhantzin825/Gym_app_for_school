<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_reach_the_workout_endpoints(): void
    {
        $this->getJson('/api/v1/workouts')->assertUnauthorized();
        $this->postJson('/api/v1/workouts', [])->assertUnauthorized();
    }

    public function test_index_returns_only_the_authenticated_users_workouts(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $mine = Workout::factory()->for($user)->create();
        Workout::factory()->for($stranger)->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/workouts');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id)
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]);
    }

    public function test_index_orders_by_started_at_descending(): void
    {
        $user = User::factory()->create();
        $older = Workout::factory()->for($user)->create([
            'started_at' => now()->subDays(3),
        ]);
        $newer = Workout::factory()->for($user)->create([
            'started_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/workouts');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id);
    }

    public function test_store_creates_a_workout_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/workouts', [
            'name' => 'Push Day',
            'started_at' => now()->subHour()->toIso8601String(),
            'ended_at' => now()->toIso8601String(),
            'total_duration_minutes' => 60,
            'notes' => 'Reps: 40',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'Push Day')
            ->assertJsonPath('user_id', $user->id);

        $this->assertDatabaseHas('workouts', [
            'user_id' => $user->id,
            'name' => 'Push Day',
            'total_duration_minutes' => 60,
        ]);
    }

    public function test_store_persists_the_rep_count(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/workouts', [
            'name' => 'Bench press',
            'started_at' => now()->subMinutes(20)->toIso8601String(),
            'ended_at' => now()->toIso8601String(),
            'total_duration_minutes' => 20,
            'total_reps' => 60,
        ]);

        $response->assertCreated()
            ->assertJsonPath('total_reps', 60);

        $this->assertDatabaseHas('workouts', [
            'user_id' => $user->id,
            'name' => 'Bench press',
            'total_reps' => 60,
        ]);
    }

    public function test_update_can_change_the_rep_count(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->for($user)->create(['total_reps' => 30]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/workouts/{$workout->id}", ['total_reps' => 75]);

        $response->assertOk()->assertJsonPath('total_reps', 75);
        $this->assertSame(75, $workout->fresh()->total_reps);
    }

    public function test_a_negative_rep_count_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts', [
                'name' => 'Bench press',
                'total_reps' => -5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('total_reps');
    }

    public function test_store_validates_the_payload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts', [
                'name' => 'Backwards',
                'started_at' => now()->toIso8601String(),
                'ended_at' => now()->subHour()->toIso8601String(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ended_at');
    }

    public function test_show_returns_the_workout_with_its_sets(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->for($user)->create();
        WorkoutSet::factory()->for($workout)->create(['set_number' => 1, 'reps' => 10]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/workouts/{$workout->id}");

        $response->assertOk()
            ->assertJsonPath('id', $workout->id)
            ->assertJsonCount(1, 'workout_sets');
    }

    public function test_a_user_cannot_read_another_users_workout(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $workout = Workout::factory()->for($owner)->create();

        $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/v1/workouts/{$workout->id}")
            ->assertForbidden();
    }

    public function test_update_edits_an_owned_workout(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->for($user)->create(['name' => 'Old name']);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/workouts/{$workout->id}", ['name' => 'Leg Day']);

        $response->assertOk()->assertJsonPath('name', 'Leg Day');
        $this->assertSame('Leg Day', $workout->fresh()->name);
    }

    public function test_a_user_cannot_update_another_users_workout(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $workout = Workout::factory()->for($owner)->create(['name' => 'Untouched']);

        $this->actingAs($intruder, 'sanctum')
            ->putJson("/api/v1/workouts/{$workout->id}", ['name' => 'Hacked'])
            ->assertForbidden();

        $this->assertSame('Untouched', $workout->fresh()->name);
    }

    public function test_destroy_soft_deletes_an_owned_workout(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/workouts/{$workout->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Workout deleted successfully');

        $this->assertSoftDeleted('workouts', ['id' => $workout->id]);
    }

    public function test_a_user_cannot_delete_another_users_workout(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $workout = Workout::factory()->for($owner)->create();

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/workouts/{$workout->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted('workouts', ['id' => $workout->id]);
    }
}
