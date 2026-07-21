<?php

namespace App\Ai\Tools;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LogMealFromAnalysis implements Tool
{
    public function __construct(public User $user) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Log a meal and its food items to the user\'s nutrition history after analysis. Use this after analyzing a meal photo to save the results.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $meal = $this->user->meals()->create([
            'name' => $request['meal_type'],
            'meal_time' => now(),
            'total_calories' => 0,
            'total_protein' => 0,
            'total_carbs' => 0,
            'total_fat' => 0,
        ]);

        $totalCalories = 0;
        $totalProtein = 0;
        $totalCarbs = 0;
        $totalFat = 0;

        foreach ($request['foods'] as $food) {
            $meal->foodLogs()->create([
                'food_name' => $food['food_name'],
                'serving_size' => $food['serving_size'],
                'serving_unit' => $food['serving_unit'],
                'calories' => $food['calories'],
                'protein' => $food['protein'],
                'carbs' => $food['carbs'],
                'fat' => $food['fat'],
                'fiber' => $food['fiber'] ?? null,
                'sugar' => $food['sugar'] ?? null,
                'sodium' => $food['sodium'] ?? null,
            ]);

            $totalCalories += $food['calories'];
            $totalProtein += $food['protein'];
            $totalCarbs += $food['carbs'];
            $totalFat += $food['fat'];
        }

        $meal->update([
            'total_calories' => $totalCalories,
            'total_protein' => $totalProtein,
            'total_carbs' => $totalCarbs,
            'total_fat' => $totalFat,
        ]);

        return json_encode([
            'success' => true,
            'meal_id' => $meal->id,
            'total_calories' => $totalCalories,
            'total_protein' => $totalProtein,
            'total_carbs' => $totalCarbs,
            'total_fat' => $totalFat,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'meal_type' => $schema->string()->required(),
            'foods' => $schema->array()->items([
                'food_name' => $schema->string()->required(),
                'serving_size' => $schema->number()->required(),
                'serving_unit' => $schema->string()->required(),
                'calories' => $schema->number()->required(),
                'protein' => $schema->number()->required(),
                'carbs' => $schema->number()->required(),
                'fat' => $schema->number()->required(),
                'fiber' => $schema->number(),
                'sugar' => $schema->number(),
                'sodium' => $schema->number(),
            ])->required(),
        ];
    }
}
