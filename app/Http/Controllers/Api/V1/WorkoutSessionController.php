<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWorkoutSessionRequest;
use App\Models\Workout;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Persists a finished live training session in one call.
 *
 * The app streams reps/sets as the user trains, so by the time the session
 * ends it holds everything: which movements were done, every set, and (from
 * the pose tracker) a camera-derived form score per set. This endpoint writes
 * that as a Workout + one Exercise row per movement + a WorkoutSet per set,
 * so history, reports and the admin panel see real relational data instead of
 * a summary string in `notes`.
 */
class WorkoutSessionController extends Controller
{
    public function store(StoreWorkoutSessionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $startedAt = Carbon::parse($data['started_at']);
        $endedAt = isset($data['ended_at']) ? Carbon::parse($data['ended_at']) : now();
        $durationMinutes = max(0, (int) $startedAt->diffInMinutes($endedAt));

        $workout = DB::transaction(function () use ($user, $data, $startedAt, $endedAt, $durationMinutes) {
            /** @var Workout $workout */
            $workout = $user->workouts()->create([
                'name' => $data['name'],
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'notes' => $data['notes'] ?? null,
                'total_duration_minutes' => $durationMinutes,
            ]);

            $totalVolume = 0.0;

            foreach ($data['exercises'] as $exerciseData) {
                $sets = $exerciseData['sets'];

                $exercise = $user->exercises()->create([
                    'exercise_name' => $exerciseData['name'],
                    'exercise_type' => $exerciseData['type'],
                    'exercise_time' => $startedAt,
                    'sets' => count($sets),
                    'reps' => $this->sumReps($sets),
                ]);

                foreach ($sets as $set) {
                    $workout->workoutSets()->create([
                        'exercise_id' => $exercise->id,
                        'set_number' => $set['set_number'],
                        'reps' => $set['reps'] ?? null,
                        'weight' => $set['weight'] ?? null,
                        'rpe' => $set['rpe'] ?? null,
                        'rest_time_seconds' => $set['rest_time_seconds'] ?? null,
                        'form_score' => $set['form_score'] ?? null,
                    ]);

                    $totalVolume += ($set['reps'] ?? 0) * ($set['weight'] ?? 0);
                }
            }

            $workout->update(['total_volume' => round($totalVolume, 2)]);

            return $workout;
        });

        $workout->load('workoutSets.exercise');

        return response()->json([
            'workout' => $workout,
            'summary' => $this->summary($workout),
        ], 201);
    }

    /**
     * @param  array<int, array<string, mixed>>  $sets
     */
    private function sumReps(array $sets): ?int
    {
        $total = array_sum(array_map(fn ($set) => $set['reps'] ?? 0, $sets));

        return $total > 0 ? (int) $total : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(Workout $workout): array
    {
        $sets = $workout->workoutSets;

        $formScores = $sets->pluck('form_score')->filter(fn ($score) => $score !== null);

        return [
            'duration_minutes' => $workout->total_duration_minutes,
            'total_sets' => $sets->count(),
            'total_reps' => (int) $sets->sum(fn ($set) => $set->reps ?? 0),
            'total_volume' => (float) $workout->total_volume,
            'exercise_count' => $sets->pluck('exercise_id')->unique()->count(),
            'average_form_score' => $formScores->isEmpty()
                ? null
                : round($formScores->avg(), 1),
        ];
    }
}
