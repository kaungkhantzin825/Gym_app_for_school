<?php

namespace App\Ai\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetUserMealHistory implements Tool
{
    public function __construct(public User $user) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieve the user\'s recent meal history including food items and nutritional totals. Use the days parameter to specify how many days of history to retrieve.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $days = $request['days'] ?? 7;

        $meals = $this->user->meals()
            ->with('foodLogs')
            ->where('meal_time', '>=', now()->subDays($days))
            ->orderBy('meal_time', 'desc')
            ->limit(50)
            ->get();

        $summary = $meals->map(fn ($meal) => [
            'name' => $meal->name,
            'meal_time' => $meal->meal_time->toDateTimeString(),
            'total_calories' => $meal->total_calories,
            'total_protein' => $meal->total_protein,
            'total_carbs' => $meal->total_carbs,
            'total_fat' => $meal->total_fat,
            'foods' => $meal->foodLogs->map(fn ($log) => [
                'food_name' => $log->food_name,
                'calories' => $log->calories,
                'protein' => $log->protein,
            ])->toArray(),
        ]);

        return json_encode([
            'days_queried' => $days,
            'total_meals' => $meals->count(),
            'average_daily_calories' => $meals->count() > 0
                ? round($meals->sum('total_calories') / max(1, $days), 2)
                : 0,
            'meals' => $summary->toArray(),
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
