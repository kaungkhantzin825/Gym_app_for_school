<?php

namespace App\Ai\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetUserProfile implements Tool
{
    public function __construct(public User $user) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieve the current user\'s fitness profile including their goals, body metrics, activity level, and daily nutrition targets.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $profile = $this->user->profile;

        if (! $profile) {
            return json_encode(['message' => 'User has not set up their profile yet.']);
        }

        return json_encode([
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
