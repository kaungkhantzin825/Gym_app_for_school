<?php

namespace App\Ai\Tools;

use App\Models\User;
use App\Models\WorkoutSet;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * "Am I actually getting stronger?"
 *
 * GetUserExerciseHistory returns the raw sets, which the model would have to
 * aggregate itself - and it usually does that badly. This tool does the maths:
 * weekly volume, and the best set plus an estimated 1RM per exercise, so the
 * coach can say "your bench went from 70 to 77.5 kg in six weeks".
 */
class GetStrengthProgress implements Tool
{
    private const WEEKS = 8;

    public function __construct(public User $user) {}

    public function description(): Stringable|string
    {
        return 'Retrieve the user\'s training progress over the last 8 weeks: volume, sets and reps per week, and for each exercise the heaviest weight lifted, the estimated one-rep max and the trend between their first and most recent session. Use this whenever the user asks whether they are improving, what to lift next, or for a progress review.';
    }

    public function handle(Request $request): Stringable|string
    {
        $sets = WorkoutSet::query()
            ->join('workouts', 'workouts.id', '=', 'workout_sets.workout_id')
            ->join('exercises', 'exercises.id', '=', 'workout_sets.exercise_id')
            ->where('workouts.user_id', $this->user->id)
            ->whereNull('workouts.deleted_at')
            ->where('workouts.started_at', '>=', now()->subWeeks(self::WEEKS))
            ->orderBy('workouts.started_at')
            ->get([
                'workout_sets.reps',
                'workout_sets.weight',
                'workout_sets.form_score',
                'workouts.started_at',
                'exercises.exercise_name',
                'exercises.exercise_type',
            ]);

        if ($sets->isEmpty()) {
            return json_encode([
                'weeks_queried' => self::WEEKS,
                'message' => 'No completed sets recorded in this period.',
            ]);
        }

        $weekly = [];
        $exercises = [];

        foreach ($sets as $set) {
            $reps = (int) ($set->reps ?? 0);
            $weight = (float) ($set->weight ?? 0);
            $volume = $reps * $weight;

            $week = Carbon::parse($set->started_at)->startOfWeek()->toDateString();

            $weekly[$week] ??= [
                'week_start' => $week,
                'sets' => 0,
                'reps' => 0,
                'volume_kg' => 0.0,
            ];
            $weekly[$week]['sets']++;
            $weekly[$week]['reps'] += $reps;
            $weekly[$week]['volume_kg'] += $volume;

            $name = (string) $set->exercise_name;

            if (! isset($exercises[$name])) {
                $exercises[$name] = [
                    'exercise' => $name,
                    'type' => $set->exercise_type,
                    'sets' => 0,
                    'best_weight_kg' => 0.0,
                    'best_set' => null,
                    'estimated_1rm_kg' => 0.0,
                    'first_session_best_kg' => null,
                    'latest_session_best_kg' => null,
                    'avg_form_score' => null,
                ];
            }

            $entry = &$exercises[$name];
            $entry['sets']++;

            // Epley: 1RM = w * (1 + reps / 30). Only meaningful with load.
            if ($weight > 0 && $reps > 0) {
                $oneRm = round($weight * (1 + $reps / 30), 1);

                if ($oneRm > $entry['estimated_1rm_kg']) {
                    $entry['estimated_1rm_kg'] = $oneRm;
                }

                if ($weight > $entry['best_weight_kg']) {
                    $entry['best_weight_kg'] = $weight;
                    $entry['best_set'] = ['weight_kg' => $weight, 'reps' => $reps];
                }

                $date = Carbon::parse($set->started_at)->toDateString();

                $entry['first_session_best_kg'] ??= $weight;
                $entry['latest_session_best_kg'] = max((float) $entry['latest_session_best_kg'], $weight);
                $entry['latest_session_date'] = $date;
            }

            if ($set->form_score !== null) {
                $scores = $entry['_scores'] ?? [];
                $scores[] = (int) $set->form_score;
                $entry['_scores'] = $scores;
            }

            unset($entry);
        }

        foreach ($exercises as $name => $entry) {
            if (isset($entry['_scores'])) {
                $exercises[$name]['avg_form_score'] = round(array_sum($entry['_scores']) / count($entry['_scores']), 1);
                unset($exercises[$name]['_scores']);
            } else {
                unset($exercises[$name]['_scores']);
            }
        }

        ksort($weekly);

        return json_encode([
            'weeks_queried' => self::WEEKS,
            'weekly' => array_values($weekly),
            'exercises' => array_values($exercises),
            'note' => 'estimated_1rm_kg uses the Epley formula and is an estimate, not a tested max.',
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
