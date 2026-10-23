<?php

namespace Tests\Feature;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Meal photos are the only way a member can tell two similar meals apart, so
 * they have to survive the round trip: stored path in, loadable URL out.
 */
class MealPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_a_scan_path_is_accepted_and_turned_into_a_served_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/meals', [
            'name' => 'Southeast Asian rice meal',
            'meal_time' => now()->toIso8601String(),
            'photo_path' => 'meal-scans/abc123.jpg',
        ]);

        $response->assertCreated()
            ->assertJsonPath('photo_path', 'meal-scans/abc123.jpg');

        // /storage/... answers 403 on the live host, so the URL has to point at
        // the PHP media route instead.
        $this->assertStringContainsString(
            '/api/v1/media/meal-scans/abc123.jpg',
            $response->json('photo_url'),
        );
    }

    public function test_a_meal_without_a_photo_reports_a_null_url(): void
    {
        $user = User::factory()->create();
        $meal = Meal::factory()->for($user)->create(['photo_path' => null]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/meals/{$meal->id}")
            ->assertOk()
            ->assertJsonPath('photo_url', null);
    }

    public function test_a_path_that_tries_to_escape_the_upload_directory_is_rejected(): void
    {
        $user = User::factory()->create();

        foreach (['../../.env', '/etc/passwd', 'meal-scans/../../.env'] as $path) {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/v1/meals', [
                    'name' => 'lunch',
                    'meal_time' => now()->toIso8601String(),
                    'photo_path' => $path,
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('photo_path');
        }
    }

    public function test_uploading_a_photo_stores_it_and_points_the_meal_at_it(): void
    {
        $user = User::factory()->create();
        $meal = Meal::factory()->for($user)->create(['photo_path' => null]);

        $response = $this->actingAs($user, 'sanctum')->post(
            "/api/v1/meals/{$meal->id}/photo",
            ['image' => UploadedFile::fake()->image('meal.jpg')],
            ['Accept' => 'application/json'],
        );

        $response->assertOk();

        $path = $meal->fresh()->photo_path;
        $this->assertNotNull($path);
        $this->assertStringStartsWith('meals/', $path);
        Storage::disk('public')->assertExists($path);

        $this->assertStringContainsString('/api/v1/media/meals/', $response->json('photo_url'));
    }

    public function test_replacing_a_photo_deletes_the_previous_file(): void
    {
        $user = User::factory()->create();
        $meal = Meal::factory()->for($user)->create(['photo_path' => null]);

        $this->actingAs($user, 'sanctum')->post(
            "/api/v1/meals/{$meal->id}/photo",
            ['image' => UploadedFile::fake()->image('first.jpg')],
            ['Accept' => 'application/json'],
        )->assertOk();

        $first = $meal->fresh()->photo_path;

        $this->actingAs($user, 'sanctum')->post(
            "/api/v1/meals/{$meal->id}/photo",
            ['image' => UploadedFile::fake()->image('second.jpg')],
            ['Accept' => 'application/json'],
        )->assertOk();

        $second = $meal->fresh()->photo_path;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_a_photo_can_be_removed(): void
    {
        $user = User::factory()->create();
        $meal = Meal::factory()->for($user)->create(['photo_path' => null]);

        $this->actingAs($user, 'sanctum')->post(
            "/api/v1/meals/{$meal->id}/photo",
            ['image' => UploadedFile::fake()->image('meal.jpg')],
            ['Accept' => 'application/json'],
        )->assertOk();

        $path = $meal->fresh()->photo_path;

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/meals/{$meal->id}/photo")
            ->assertOk()
            ->assertJsonPath('photo_path', null)
            ->assertJsonPath('photo_url', null);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_only_the_owner_can_change_a_meal_photo(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $meal = Meal::factory()->for($owner)->create(['photo_path' => null]);

        $this->actingAs($intruder, 'sanctum')->post(
            "/api/v1/meals/{$meal->id}/photo",
            ['image' => UploadedFile::fake()->image('meal.jpg')],
            ['Accept' => 'application/json'],
        )->assertForbidden();

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/meals/{$meal->id}/photo")
            ->assertForbidden();

        $this->assertNull($meal->fresh()->photo_path);
    }

    public function test_the_media_route_serves_a_meal_photo(): void
    {
        Storage::disk('public')->put('meals/photo.jpg', 'binary');

        $this->get('/api/v1/media/meals/photo.jpg')->assertOk();
        $this->get('/api/v1/media/meal-scans/photo.jpg')->assertNotFound();
    }
}
