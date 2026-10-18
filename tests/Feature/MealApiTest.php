<?php

namespace Tests\Feature;

use App\Models\FoodLog;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_reach_the_meal_endpoints(): void
    {
        $this->getJson('/api/v1/meals')->assertUnauthorized();
    }

    public function test_index_returns_only_own_meals_newest_first(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $older = Meal::factory()->for($user)->create(['meal_time' => now()->subDay()]);
        $newer = Meal::factory()->for($user)->create(['meal_time' => now()]);
        Meal::factory()->for($stranger)->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/meals');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id);
    }

    public function test_index_can_be_filtered_to_a_single_day(): void
    {
        $user = User::factory()->create();

        $today = Meal::factory()->for($user)->create(['meal_time' => now()]);
        Meal::factory()->for($user)->create([
            'meal_time' => now()->subDays(2),
            // created_at is part of the filter (meals can be back-dated), so
            // the older meal must look older on both columns.
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/meals?date='.now()->toDateString());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $today->id);
    }

    public function test_store_creates_a_meal_owned_by_the_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/meals', [
            'name' => 'lunch',
            'meal_time' => now()->toIso8601String(),
            'notes' => 'Chicken and rice',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'lunch')
            ->assertJsonPath('user_id', $user->id);

        // Nutrient totals default to zero until food logs are attached.
        $this->assertDatabaseHas('meals', [
            'user_id' => $user->id,
            'name' => 'lunch',
            'total_calories' => 0,
        ]);
    }

    public function test_store_accepts_a_free_form_meal_name(): void
    {
        $user = User::factory()->create();

        // The app stores the AI's own meal name (e.g. "Grilled chicken
        // salad"), so creation must not be limited to the four meal slots.
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/meals', [
                'name' => 'Grilled chicken salad',
                'meal_time' => now()->toIso8601String(),
            ])
            ->assertCreated()
            ->assertJsonPath('name', 'Grilled chicken salad');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/meals', [
                'name' => str_repeat('a', 256),
                'meal_time' => now()->toIso8601String(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_show_includes_the_food_logs(): void
    {
        $user = User::factory()->create();
        $meal = Meal::factory()->for($user)->create();
        FoodLog::factory()->for($meal)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/meals/{$meal->id}");

        $response->assertOk()
            ->assertJsonPath('id', $meal->id)
            ->assertJsonCount(1, 'food_logs');
    }

    public function test_update_and_destroy_respect_ownership(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $meal = Meal::factory()->for($owner)->create(['name' => 'lunch']);

        $this->actingAs($intruder, 'sanctum')
            ->putJson("/api/v1/meals/{$meal->id}", ['name' => 'dinner'])
            ->assertForbidden();

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/meals/{$meal->id}")
            ->assertForbidden();

        $this->assertSame('lunch', $meal->fresh()->name);

        $this->actingAs($owner, 'sanctum')
            ->putJson("/api/v1/meals/{$meal->id}", ['name' => 'dinner'])
            ->assertOk()
            ->assertJsonPath('name', 'dinner');

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/v1/meals/{$meal->id}")
            ->assertOk();

        $this->assertSoftDeleted('meals', ['id' => $meal->id]);
    }
}
