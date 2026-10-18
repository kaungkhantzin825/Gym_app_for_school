<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_reach_the_exercise_endpoints(): void
    {
        $this->getJson('/api/v1/exercises')->assertUnauthorized();
    }

    public function test_index_returns_only_own_exercises(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        Exercise::factory()->for($user)->count(2)->create();
        Exercise::factory()->for($stranger)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/exercises')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_filters_by_type_and_date(): void
    {
        $user = User::factory()->create();

        $cardio = Exercise::factory()->for($user)->create([
            'exercise_type' => 'cardio',
            'exercise_time' => now(),
        ]);
        Exercise::factory()->for($user)->create([
            'exercise_type' => 'strength',
            'exercise_time' => now(),
        ]);
        Exercise::factory()->for($user)->create([
            'exercise_type' => 'cardio',
            'exercise_time' => now()->subDays(5),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/exercises?type=cardio')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/exercises?type=cardio&date='.now()->toDateString())
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $cardio->id);
    }

    public function test_store_creates_an_exercise_for_the_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/exercises', [
            'exercise_name' => 'Bench Press',
            'exercise_type' => 'strength',
            'sets' => 4,
            'reps' => 8,
            'weight' => 60,
            'exercise_time' => now()->toIso8601String(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('exercise_name', 'Bench Press')
            ->assertJsonPath('user_id', $user->id);

        $this->assertDatabaseHas('exercises', [
            'user_id' => $user->id,
            'exercise_name' => 'Bench Press',
        ]);
    }

    public function test_store_rejects_an_unknown_exercise_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/exercises', [
                'exercise_name' => 'Bench Press',
                'exercise_type' => 'crossfit',
                'exercise_time' => now()->toIso8601String(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('exercise_type');
    }

    public function test_a_user_cannot_touch_another_users_exercise(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $exercise = Exercise::factory()->for($owner)->create(['exercise_name' => 'Squat']);

        $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/v1/exercises/{$exercise->id}")
            ->assertForbidden();

        $this->actingAs($intruder, 'sanctum')
            ->putJson("/api/v1/exercises/{$exercise->id}", ['exercise_name' => 'Hack'])
            ->assertForbidden();

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/exercises/{$exercise->id}")
            ->assertForbidden();

        $this->assertSame('Squat', $exercise->fresh()->exercise_name);
        $this->assertNotSoftDeleted('exercises', ['id' => $exercise->id]);
    }

    public function test_owner_can_update_and_soft_delete(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->for($user)->create(['exercise_name' => 'Squat']);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/exercises/{$exercise->id}", ['exercise_name' => 'Front Squat'])
            ->assertOk()
            ->assertJsonPath('exercise_name', 'Front Squat');

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/exercises/{$exercise->id}")
            ->assertOk();

        $this->assertSoftDeleted('exercises', ['id' => $exercise->id]);
    }
}
