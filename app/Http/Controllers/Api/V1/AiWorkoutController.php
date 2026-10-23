<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\WorkoutGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GenerateWorkoutRequest;
use App\Models\Exercise;
use App\Services\AiKeyRotatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AiWorkoutController extends Controller
{
    public function generate(GenerateWorkoutRequest $request): JsonResponse
    {
        $input = $request->validated();
        $adminExerciseCatalog = Exercise::adminCatalog();

        if ($adminExerciseCatalog->isEmpty()) {
            return response()->json([
                'message' => 'Workout generation requires admin-managed exercises with tutorial URLs.',
            ], 422);
        }

        $agent = new WorkoutGenerator($request->user());

        $prompt = implode("\n", [
            'Generate a personalized workout using the following user input:',
            "Fitness level: {$input['fitness_level']}",
            "Primary goal: {$input['primary_goal']}",
            "Duration (minutes): {$input['duration_minutes']}",
            "Available equipment: {$input['available_equipment']}",
            "Focus area: {$input['focus_area']}",
        ]);

        $payload = [];

        try {
            $response = AiKeyRotatorService::runWithRotation(function (string $provider, string $key) use ($agent, $prompt) {
                $agent->setProvider($provider);

                return $agent->prompt($prompt);
            });

            $payload = $this->resolveStructuredResponsePayload($response);

            if (empty($payload) || empty($payload['exercises'])) {
                Log::warning('AI provider returned empty workout payload; using intelligent catalog fallback.');
                $payload = $this->buildFallbackWorkoutPayload($input, $adminExerciseCatalog);
            }
        } catch (\Throwable $e) {
            Log::warning("AI workout generation failed across keys/providers: {$e->getMessage()}. Using catalog fallback.");
            $payload = $this->buildFallbackWorkoutPayload($input, $adminExerciseCatalog);
        }

        return response()->json(
            $this->attachExerciseTutorialUrls(
                $payload,
                $adminExerciseCatalog,
            )
        );
    }

    /**
     * Generate an intelligent fallback workout directly from the admin catalog.
     *
     * @param  array<string, mixed>  $input
     * @param  Collection<int, Exercise>  $adminExerciseCatalog
     * @return array<string, mixed>
     */
    private function buildFallbackWorkoutPayload(array $input, Collection $adminExerciseCatalog): array
    {
        $focusArea = strtolower(trim($input['focus_area'] ?? 'chest'));
        $duration = (int) ($input['duration_minutes'] ?? 30);
        $level = strtolower(trim($input['fitness_level'] ?? 'intermediate'));
        $goal = strtolower(trim($input['primary_goal'] ?? 'build_muscle'));

        // Match exercises by focus area / muscle group or name
        $matches = $adminExerciseCatalog->filter(function (Exercise $ex) use ($focusArea) {
            $name = strtolower($ex->exercise_name);
            $type = strtolower($ex->exercise_type);
            $desc = strtolower($ex->description ?? '');

            if (str_contains($focusArea, 'chest')) {
                return str_contains($name, 'chest') || str_contains($name, 'press') || str_contains($name, 'push') || str_contains($name, 'fly') || str_contains($name, 'dip');
            }
            if (str_contains($focusArea, 'back')) {
                return str_contains($name, 'back') || str_contains($name, 'row') || str_contains($name, 'pull') || str_contains($name, 'lat');
            }
            if (str_contains($focusArea, 'arm') || str_contains($focusArea, 'bicep') || str_contains($focusArea, 'tricep')) {
                return str_contains($name, 'curl') || str_contains($name, 'tricep') || str_contains($name, 'arm') || str_contains($name, 'dip');
            }
            if (str_contains($focusArea, 'leg')) {
                return str_contains($name, 'squat') || str_contains($name, 'lunge') || str_contains($name, 'leg') || str_contains($name, 'calf');
            }
            if (str_contains($focusArea, 'core') || str_contains($focusArea, 'abs')) {
                return str_contains($name, 'plank') || str_contains($name, 'crunch') || str_contains($name, 'core') || str_contains($name, 'ab');
            }

            return true;
        });

        if ($matches->count() < 3) {
            $matches = $adminExerciseCatalog;
        }

        $targetCount = max(3, min(8, (int) round($duration / 8)));
        $selected = $matches->shuffle()->take($targetCount);

        $sets = $level === 'beginner' ? 3 : ($level === 'advanced' ? 4 : 3);
        $reps = str_contains($goal, 'strength') ? 8 : (str_contains($goal, 'muscle') ? 10 : 12);
        $rest = $level === 'beginner' ? 90 : 60;
        $rpe = $level === 'beginner' ? 6 : ($level === 'advanced' ? 8 : 7);

        $exercises = [];
        foreach ($selected as $ex) {
            $exercises[] = [
                'exercise_name' => $ex->exercise_name,
                'exercise_type' => $ex->exercise_type ?? 'strength',
                'sets' => $sets,
                'reps' => $reps,
                'weight_kg' => $level === 'beginner' ? 12 : ($level === 'advanced' ? 24 : 16),
                'duration_minutes' => max(3, (int) round($duration / max(1, $selected->count()))),
                'rest_time_seconds' => $rest,
                'rpe' => $rpe,
                'calories_burned' => (int) round(($duration * 7) / max(1, $selected->count())),
                'notes' => 'Maintain steady form and control tempo.',
                'exercise_tutorial_url' => $ex->exercise_tutorial_url,
            ];
        }

        return [
            'workout_name' => ucwords(str_replace('_', ' ', $focusArea)).' '.ucfirst($level).' Session',
            'estimated_duration_minutes' => $duration,
            'exercises' => $exercises,
            'total_estimated_calories' => (int) round($duration * 7.5),
            'notes' => 'Personalized routine tailored to your '.$level.' level and '.str_replace('_', ' ', $goal).' goal.',
        ];
    }

    /**
     * Resolve the response payload, falling back to raw JSON text when the
     * provider returns an empty structured payload.
     */
    private function resolveStructuredResponsePayload(object $response): array
    {
        $payload = method_exists($response, 'toArray') ? $response->toArray() : [];

        if ($payload !== []) {
            return $payload;
        }

        if (! property_exists($response, 'text') || ! is_string($response->text) || $response->text === '') {
            return [];
        }

        $decoded = json_decode($response->text, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  Collection<int, Exercise>  $adminExerciseCatalog
     * @return array<string, mixed>
     */
    private function attachExerciseTutorialUrls(array $payload, Collection $adminExerciseCatalog): array
    {
        if (! isset($payload['exercises']) || ! is_array($payload['exercises'])) {
            return $payload;
        }

        $catalogByName = $adminExerciseCatalog->keyBy(
            fn (Exercise $exercise) => mb_strtolower(trim($exercise->exercise_name))
        );

        $payload['exercises'] = array_map(function (mixed $exercise) use ($catalogByName): mixed {
            if (! is_array($exercise) || ! isset($exercise['exercise_name']) || ! is_string($exercise['exercise_name'])) {
                return $exercise;
            }

            $catalogExercise = $catalogByName->get(mb_strtolower(trim($exercise['exercise_name'])));

            if (! $catalogExercise instanceof Exercise) {
                $exercise['exercise_tutorial_url'] = null;

                return $exercise;
            }

            $exercise['exercise_name'] = $catalogExercise->exercise_name;
            $exercise['exercise_type'] = $catalogExercise->exercise_type;
            $exercise['exercise_tutorial_url'] = $this->normalizeExerciseTutorialUrl(
                $catalogExercise->exercise_tutorial_url,
            );

            return $exercise;
        }, $payload['exercises']);

        return $payload;
    }

    private function normalizeExerciseTutorialUrl(?string $exerciseTutorialUrl): ?string
    {
        if ($exerciseTutorialUrl === null || $exerciseTutorialUrl === '') {
            return null;
        }

        if (str_starts_with($exerciseTutorialUrl, 'http://') || str_starts_with($exerciseTutorialUrl, 'https://')) {
            return $exerciseTutorialUrl;
        }

        return url($exerciseTutorialUrl);
    }
}
