<?php

namespace Tests\Feature;

use App\Ai\Agents\WorkoutGenerator;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Ai;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StructuredTextResponse;
use Tests\TestCase;

class AiWorkoutControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminExerciseCatalog(): void
    {
        $admin = User::factory()->admin()->create();

        Exercise::factory()->for($admin)->create([
            'exercise_name' => 'Bench Press',
            'exercise_type' => 'strength',
            'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
            'exercise_time' => now(),
        ]);

        Exercise::factory()->for($admin)->create([
            'exercise_name' => 'Walking',
            'exercise_type' => 'cardio',
            'exercise_tutorial_url' => Storage::url('exercise_tutorials/1.gif'),
            'exercise_time' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function workoutRequestPayload(): array
    {
        return [
            'fitness_level' => 'intermediate',
            'primary_goal' => 'build muscle',
            'duration_minutes' => 45,
            'available_equipment' => 'Dumbbells and resistance bands',
            'focus_area' => 'upper body',
        ];
    }

    public function test_it_returns_structured_workout_data(): void
    {
        $this->createAdminExerciseCatalog();

        $user = User::factory()->create();
        $instructions = (string) (new WorkoutGenerator($user))->instructions();

        $this->assertStringContainsString('Admin-managed exercise catalog:', $instructions);
        $this->assertStringContainsString('Bench Press', $instructions);
        $this->assertStringContainsString('exercise_tutorials', $instructions);

        Ai::fakeAgent(WorkoutGenerator::class, [function (string $prompt): array {
            $this->assertStringContainsString('Fitness level: intermediate', $prompt);
            $this->assertStringContainsString('Primary goal: build muscle', $prompt);
            $this->assertStringContainsString('Duration (minutes): 45', $prompt);
            $this->assertStringContainsString('Available equipment: Dumbbells and resistance bands', $prompt);
            $this->assertStringContainsString('Focus area: upper body', $prompt);

            return [
                'workout_name' => 'Upper Body Strength',
                'estimated_duration_minutes' => 45,
                'exercises' => [[
                    'exercise_name' => 'Bench Press',
                    'exercise_type' => 'strength',
                    'sets' => 4,
                    'reps' => 8,
                    'weight_kg' => 60,
                    'duration_minutes' => null,
                    'rest_time_seconds' => 90,
                    'rpe' => 8,
                    'calories_burned' => 70,
                    'notes' => 'Keep shoulders packed.',
                ]],
                'total_estimated_calories' => 240,
                'notes' => 'Warm up before the first working set.',
            ];
        }]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/ai/workout/generate', $this->workoutRequestPayload());

        $response->assertOk()->assertJson([
            'workout_name' => 'Upper Body Strength',
            'estimated_duration_minutes' => 45,
            'total_estimated_calories' => 240,
            'exercises' => [[
                'exercise_name' => 'Bench Press',
                'exercise_type' => 'strength',
                'exercise_tutorial_url' => url('/storage/exercise_tutorials/1.gif'),
            ]],
        ]);
    }

    public function test_it_falls_back_to_decoding_raw_json_when_structured_payload_is_empty(): void
    {
        $this->createAdminExerciseCatalog();

        $user = User::factory()->create();

        Ai::fakeAgent(WorkoutGenerator::class, [new StructuredTextResponse(
            [],
            json_encode([
                'workout_name' => 'Recovery Session',
                'estimated_duration_minutes' => 30,
                'exercises' => [[
                    'exercise_name' => 'Walking',
                    'exercise_type' => 'cardio',
                    'duration_minutes' => 30,
                    'calories_burned' => 120,
                    'notes' => 'Maintain a brisk pace.',
                ]],
                'total_estimated_calories' => 120,
                'notes' => 'Stay conversational throughout.',
            ], JSON_THROW_ON_ERROR),
            new Usage(0, 0),
            new Meta('mistral', 'mistral-medium-latest'),
        )]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/ai/workout/generate', $this->workoutRequestPayload());

        $response->assertOk()->assertJson([
            'workout_name' => 'Recovery Session',
            'estimated_duration_minutes' => 30,
            'total_estimated_calories' => 120,
            'exercises' => [[
                'exercise_name' => 'Walking',
                'exercise_type' => 'cardio',
                'exercise_tutorial_url' => url('/storage/exercise_tutorials/1.gif'),
            ]],
        ]);

        $response->assertJsonStructure([
            'workout_name',
            'estimated_duration_minutes',
            'exercises' => [[
                'exercise_name',
                'exercise_type',
                'duration_minutes',
                'calories_burned',
                'notes',
            ]],
            'total_estimated_calories',
            'notes',
        ]);
    }

    public function test_it_requires_admin_managed_exercises_with_tutorial_urls(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/ai/workout/generate', $this->workoutRequestPayload());

        $response->assertStatus(422)->assertJson([
            'message' => 'Workout generation requires admin-managed exercises with tutorial URLs.',
        ]);
    }

    public function test_it_requires_all_workout_generation_inputs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/ai/workout/generate', []);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'fitness_level',
            'primary_goal',
            'duration_minutes',
            'available_equipment',
            'focus_area',
        ]);
    }
}
