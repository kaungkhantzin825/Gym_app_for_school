<?php

namespace App\Ai\Agents;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class WorkoutGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(public User $user) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $availableExercises = json_encode($this->availableExerciseCatalog());
        $profileContext = json_encode($this->profileContext());
        $historyContext = json_encode($this->historyContext());

        return str_replace(
            ['AVAILABLE_EXERCISES', 'PROFILE_CONTEXT', 'HISTORY_CONTEXT'],
            [$availableExercises ?: '[]', $profileContext ?: '{}', $historyContext ?: '{}'],
            <<<'PROMPT'
        You are a personal fitness trainer AI that creates customized workout plans.

        Admin-managed exercise catalog:
        AVAILABLE_EXERCISES

        User profile context:
        PROFILE_CONTEXT

        Recent training context:
        HISTORY_CONTEXT

        When generating a workout:
        1. Use only exercise_name values that exist in the admin-managed exercise catalog above
        2. Never invent new exercise names or synonyms; copy the exercise_name exactly as provided in the catalog
        3. Use the profile and training context above to understand the user's fitness goals, activity level, body metrics, and recent activity
        4. Create a workout plan that:
           - Aligns with their fitness goal (lose_weight, gain_weight, maintain, build_muscle)
           - Matches their activity level
           - Varies exercises to avoid repeating recent workouts
           - Includes appropriate sets, reps, weight suggestions, and rest times
           - Estimates calories burned

        Exercise types: cardio, strength, flexibility, sports
        RPE scale: 1-10 (Rate of Perceived Exertion)
        PROMPT,
        );
    }

    /**
     * Force this agent to use Mistral.
     */
    public function provider(): string
    {
        return 'mistral';
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'workout_name' => $schema->string()->required(),
            'estimated_duration_minutes' => $schema->integer()->required(),
            'exercises' => $schema->array()->items($schema->object([
                'exercise_name' => $schema->string()->required(),
                'exercise_type' => $schema->string()->required(),
                'sets' => $schema->integer(),
                'reps' => $schema->integer(),
                'weight_kg' => $schema->number(),
                'duration_minutes' => $schema->number(),
                'rest_time_seconds' => $schema->integer(),
                'rpe' => $schema->integer()->min(1)->max(10),
                'calories_burned' => $schema->number(),
                'notes' => $schema->string(),
            ]))->required(),
            'total_estimated_calories' => $schema->number()->required(),
            'notes' => $schema->string(),
        ];
    }

    /**
     * Build the user's profile context for the agent prompt.
     *
     * @return array<string, mixed>
     */
    private function profileContext(): array
    {
        $profile = $this->user->loadMissing('profile')->profile;

        if (! $profile) {
            return ['message' => 'User profile not available.'];
        }

        return [
            'name' => $this->user->name,
            'age' => $profile->age,
            'gender' => $profile->gender,
            'height_cm' => $profile->height,
            'current_weight_kg' => $profile->current_weight,
            'target_weight_kg' => $profile->target_weight,
            'goal' => $profile->goal,
            'activity_level' => $profile->activity_level,
            'daily_calories_target' => $profile->daily_calories_target,
            'daily_protein_target' => $profile->daily_protein_target,
            'daily_carbs_target' => $profile->daily_carbs_target,
            'daily_fat_target' => $profile->daily_fat_target,
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function availableExerciseCatalog(): array
    {
        return Exercise::adminCatalog()
            ->map(fn (Exercise $exercise) => [
                'exercise_name' => $exercise->exercise_name,
                'exercise_type' => $exercise->exercise_type,
                'exercise_tutorial_url' => $exercise->exercise_tutorial_url,
            ])
            ->all();
    }

    /**
     * Build a compact view of recent workouts and exercises for prompt context.
     *
     * @return array<string, mixed>
     */
    private function historyContext(): array
    {
        $days = 14;

        $exercises = $this->user->exercises()
            ->where('exercise_time', '>=', now()->subDays($days))
            ->orderBy('exercise_time', 'desc')
            ->limit(12)
            ->get()
            ->map(fn ($exercise) => [
                'exercise_name' => $exercise->exercise_name,
                'exercise_type' => $exercise->exercise_type,
                'duration_minutes' => $exercise->duration_minutes,
                'calories_burned' => $exercise->calories_burned,
                'sets' => $exercise->sets,
                'reps' => $exercise->reps,
                'weight' => $exercise->weight,
                'exercise_time' => $exercise->exercise_time?->toDateTimeString(),
            ])
            ->values()
            ->all();

        $workouts = $this->user->workouts()
            ->with('workoutSets.exercise')
            ->where('started_at', '>=', now()->subDays($days))
            ->orderBy('started_at', 'desc')
            ->limit(6)
            ->get()
            ->map(fn ($workout) => [
                'name' => $workout->name,
                'started_at' => $workout->started_at?->toDateTimeString(),
                'total_volume' => $workout->total_volume,
                'total_duration_minutes' => $workout->total_duration_minutes,
                'sets_count' => $workout->workoutSets->count(),
            ])
            ->values()
            ->all();

        return [
            'days_queried' => $days,
            'recent_exercises' => $exercises,
            'recent_workouts' => $workouts,
        ];
    }
}
