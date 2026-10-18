<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Turns the saved set data into the answer every lifter actually wants:
 * "am I getting stronger, and what should I lift next time?"
 *
 * Totals are all-time; the per-exercise breakdown and weekly volume use the
 * last six months so the payload stays small for long-time users.
 */
class ProgressController extends Controller
{
    private const EXERCISE_WINDOW_MONTHS = 6;

    private const WEEKS = 8;

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $sets = $this->recentSets($userId);

        return response()->json([
            'totals' => $this->totals($userId),
            'weekly' => $this->weeklyVolume($sets),
            'exercises' => $this->exercises($sets),
        ]);
    }

    /**
     * One flat row per set: enough to aggregate without hydrating relations.
     */
    private function recentSets(int $userId): Collection
    {
        return WorkoutSet::query()
            ->join('workouts', 'workouts.id', '=', 'workout_sets.workout_id')
            ->join('exercises', 'exercises.id', '=', 'workout_sets.exercise_id')
            ->where('workouts.user_id', $userId)
            ->whereNull('workouts.deleted_at')
            ->where('workouts.started_at', '>=', now()->subMonths(self::EXERCISE_WINDOW_MONTHS))
            ->select([
                'workout_sets.workout_id',
                'workout_sets.reps',
                'workout_sets.weight',
                'workout_sets.form_score',
                'workouts.started_at',
                'exercises.exercise_name',
                'exercises.exercise_type',
            ])
            ->get();
    }

    /**
     * @return array<string, int|float>
     */
    private function totals(int $userId): array
    {
        $workouts = Workout::query()->where('user_id', $userId);

        $minutes = (clone $workouts)->sum('total_duration_minutes');

        $aggregate = WorkoutSet::query()
            ->join('workouts', 'workouts.id', '=', 'workout_sets.workout_id')
            ->where('workouts.user_id', $userId)
            ->whereNull('workouts.deleted_at')
            ->selectRaw('COUNT(workout_sets.id) as sets')
            ->selectRaw('COALESCE(SUM(workout_sets.reps), 0) as reps')
            ->selectRaw('COALESCE(SUM(workout_sets.reps * COALESCE(workout_sets.weight, 0)), 0) as volume')
            ->first();

        return [
            'workouts' => $workouts->count(),
            'sets' => (int) ($aggregate->sets ?? 0),
            'reps' => (int) ($aggregate->reps ?? 0),
            'volume' => round((float) ($aggregate->volume ?? 0), 2),
            'minutes' => (int) $minutes,
        ];
    }

    /**
     * Eight continuous weeks, oldest first, so the client can draw a bar chart
     * without filling gaps itself.
     *
     * @return array<int, array<string, mixed>>
     */
    private function weeklyVolume(Collection $sets): array
    {
        $weeks = [];

        for ($i = self::WEEKS - 1; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weeks[$start->toDateString()] = [
                'week_start' => $start->toDateString(),
                'sets' => 0,
                'reps' => 0,
                'volume' => 0.0,
            ];
        }

        foreach ($sets as $set) {
            if ($set->started_at === null) {
                continue;
            }

            $key = Carbon::parse($set->started_at)
                ->startOfWeek(Carbon::MONDAY)
                ->toDateString();

            if (! isset($weeks[$key])) {
                continue;
            }

            $weeks[$key]['sets']++;
            $weeks[$key]['reps'] += (int) $set->reps;
            $weeks[$key]['volume'] += (int) $set->reps * (float) $set->weight;
        }

        return array_values(array_map(function (array $week) {
            $week['volume'] = round($week['volume'], 2);

            return $week;
        }, $weeks));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exercises(Collection $sets): array
    {
        $byExercise = $sets->groupBy('exercise_name');

        $rows = $byExercise->map(function (Collection $group, string $name) {
            // Newest session first: trend and next-target both read from [0].
            $sessions = $group
                ->groupBy('workout_id')
                ->sortByDesc(fn (Collection $session) => $this->startedAt($session))
                ->values();

            $lastSessionAt = $this->startedAt($group);

            $bestWeight = $group->max(fn ($set) => $set->weight === null ? 0.0 : (float) $set->weight);
            $bestReps = (int) $group->max(fn ($set) => (int) $set->reps);

            return [
                'name' => $name,
                'type' => $group->first()->exercise_type,
                'sessions' => $sessions->count(),
                'sets' => $group->count(),
                'best_weight' => $bestWeight > 0 ? round($bestWeight, 2) : null,
                'best_reps' => $bestReps,
                'estimated_1rm' => $this->estimatedOneRepMax($group),
                'last_performed_at' => Carbon::createFromTimestamp($lastSessionAt)->toIso8601String(),
                'trend' => $this->trend($sessions),
                'next_target' => $this->nextTarget($sessions->first()),
            ];
        });

        return $rows
            ->sortByDesc('last_performed_at')
            ->take(20)
            ->values()
            ->all();
    }

    /**
     * Unix timestamp of the newest set in a group, used for ordering.
     */
    private function startedAt(Collection $sets): int
    {
        return (int) $sets->max(fn ($set) => Carbon::parse($set->started_at)->timestamp);
    }

    /**
     * Epley: weight x (1 + reps / 30), best across the exercise's sets.
     */
    private function estimatedOneRepMax(Collection $sets): ?float
    {
        $best = $sets
            ->filter(fn ($set) => (float) $set->weight > 0 && (int) $set->reps > 0)
            ->map(fn ($set) => (float) $set->weight * (1 + ((int) $set->reps) / 30))
            ->max();

        return $best === null ? null : round((float) $best, 1);
    }

    /**
     * Compares the estimated 1RM of the newest session with the one before it.
     * A 2% band keeps normal rep-to-rep noise from reading as a trend.
     */
    private function trend(Collection $sessions): string
    {
        $values = $sessions
            ->map(fn (Collection $session) => $this->estimatedOneRepMax($session))
            ->filter(fn ($value) => $value !== null)
            ->values();

        if ($values->count() < 2) {
            return 'new';
        }

        $latest = $values[0];
        $previous = $values[1];

        if ($previous <= 0) {
            return 'new';
        }

        $change = ($latest - $previous) / $previous;

        if ($change > 0.02) {
            return 'up';
        }
        if ($change < -0.02) {
            return 'down';
        }

        return 'flat';
    }

    /**
     * Double progression: once every set of the last session hit 10+ reps,
     * add 2.5 kg and drop back to 8; otherwise keep the weight and chase one
     * more rep. Bodyweight movements just chase reps.
     *
     * @return array<string, mixed>
     */
    private function nextTarget(Collection $lastSession): array
    {
        $weight = $lastSession->max(fn ($set) => $set->weight === null ? 0.0 : (float) $set->weight);
        $minReps = (int) $lastSession->min(fn ($set) => (int) $set->reps);
        $maxReps = (int) $lastSession->max(fn ($set) => (int) $set->reps);

        if ($weight <= 0) {
            return [
                'weight' => null,
                'reps' => min(20, $maxReps + 1),
                'reason' => 'add_rep',
            ];
        }

        if ($minReps >= 10) {
            return [
                'weight' => round($weight + 2.5, 2),
                'reps' => 8,
                'reason' => 'increase_weight',
            ];
        }

        return [
            'weight' => round($weight, 2),
            'reps' => min(12, $maxReps + 1),
            'reason' => 'add_rep',
        ];
    }
}
