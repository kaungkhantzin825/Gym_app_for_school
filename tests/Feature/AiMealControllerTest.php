<?php

namespace Tests\Feature;

use App\Ai\Agents\MealAnalyzer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Ai;
use Laravel\Ai\Exceptions\RateLimitedException;
use Tests\TestCase;

class AiMealControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_analyzes_an_uploaded_meal_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        Ai::fakeAgent(MealAnalyzer::class, [[
            'foods' => [[
                'food_name' => 'Grilled Chicken Breast',
                'serving_size' => 150,
                'serving_unit' => 'g',
                'calories' => 248,
                'protein' => 46,
                'carbs' => 0,
                'fat' => 5,
                'fiber' => 0,
                'sugar' => 0,
                'sodium' => 110,
            ]],
            'meal_name' => 'Grilled Chicken Plate',
            'confidence' => 0.91,
        ]]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/ai/meal/analyze', [
            'image' => UploadedFile::fake()->image('meal.jpg'),
            'meal_type' => 'lunch',
        ]);

        $response->assertOk()->assertJson([
            'analysis' => [
                'meal_name' => 'Grilled Chicken Plate',
                'confidence' => 0.91,
            ],
        ]);

        $this->assertNotNull($response->json('image_path'));
        Storage::disk('public')->assertExists($response->json('image_path'));
    }

    public function test_it_returns_a_429_when_the_ai_provider_is_rate_limited(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        Ai::fakeAgent(MealAnalyzer::class, [function (): void {
            throw RateLimitedException::forProvider('mistral', 429);
        }]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/ai/meal/analyze', [
            'image' => UploadedFile::fake()->image('meal.jpg'),
            'meal_type' => 'lunch',
        ]);

        $response->assertStatus(429)->assertJson([
            'message' => 'Meal scan is temporarily unavailable because the AI provider is rate limited. Please retry shortly.',
            'provider' => 'mistral',
        ]);

        Storage::disk('public')->assertDirectoryEmpty('meal-scans');
    }
}
