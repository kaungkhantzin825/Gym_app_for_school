<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class MealAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function provider(): string
    {
        return 'mistral';
    }

    public function model(): string
    {
        return 'pixtral-large-latest';
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
        You are a nutrition analysis AI specialized in identifying foods from meal photos and estimating their nutritional content.

        When analyzing a meal photo:
        1. Identify all visible food items
        2. Estimate portion sizes based on visual cues
        3. Provide nutritional estimates for each item (calories, protein, carbs, fat, fiber, sugar, sodium)
        4. Be conservative with estimates - it's better to slightly overestimate calories

        Return each food item with its estimated nutritional values. Use standard serving sizes as reference.
        If you cannot identify a food item clearly, provide your best guess with a note about uncertainty.
        PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'foods' => $schema->array()->items($schema->object([
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
            ]))->required(),
            'meal_name' => $schema->string()->required(),
            'confidence' => $schema->number()->min(0)->max(1)->required(),
        ];
    }
}
