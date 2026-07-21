<?php

namespace App\Ai\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetUserExerciseHistory implements Tool
{
    public function __construct(public User $user) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieve the user\'s recent exercise and workout history. Use the days parameter to specify how many days of history to retrieve.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $days = $request['days'] ?? 7;

        $exercises = $this->user->exercises()
            ->where('exercise_time', '>=', now()->subDays($days))
            ->orderBy('exercise_time', 'desc')
            ->limit(50)
            ->get();

        $workouts = $this->user->workouts()
            ->with('workoutSets.exercise')
            ->where('started_at', '>=', now()->subDays($days))
            ->orderBy('started_at', 'desc')
            ->limit(20)
            ->get();

        return json_encode([
            'days_queried' => $days,
            'exercises' => $exercises->map(fn ($e) => [
                'exercise_name' => $e->exercise_name,
                'exercise_type' => $e->exercise_type,
                'duration_minutes' => $e->duration_minutes,
                'calories_burned' => $e->calories_burned,
                'sets' => $e->sets,
                'reps' => $e->reps,
                'weight' => $e->weight,
                'exercise_time' => $e->exercise_time->toDateTimeString(),
            ])->toArray(),
            'workouts' => $workouts->map(fn ($w) => [
                'name' => $w->name,
                'started_at' => $w->started_at?->toDateTimeString(),
                'total_volume' => $w->total_volume,
                'total_duration_minutes' => $w->total_duration_minutes,
                'sets_count' => $w->workoutSets->count(),
            ])->toArray(),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'days' => $schema->integer()->min(1)->max(30),
        ];
    }
}
