<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\WorkoutGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GenerateWorkoutRequest;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

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

        $response = $agent->prompt($prompt);

        return response()->json(
            $this->attachExerciseTutorialUrls(
                $this->resolveStructuredResponsePayload($response),
                $adminExerciseCatalog,
            )
        );
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
