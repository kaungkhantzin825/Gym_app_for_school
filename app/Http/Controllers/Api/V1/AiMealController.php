<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\MealAnalyzer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Image;
use Laravel\Ai\Exceptions\RateLimitedException;

class AiMealController extends Controller
{
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:10240'],
            'meal_type' => ['nullable', 'string', 'in:breakfast,lunch,dinner,snack'],
        ]);

        $imagePath = $request->file('image')->store('meal-scans', 'public');

        $agent = new MealAnalyzer;

        try {
            $response = $agent->prompt(
                'Analyze this meal photo and identify all food items with their nutritional estimates.',
                [Image::fromStorage($imagePath, 'public')],
            );
        } catch (RateLimitedException $exception) {
            Storage::disk('public')->delete($imagePath);

            return response()->json([
                'message' => 'Meal scan is temporarily unavailable because the AI provider is rate limited. Please retry shortly.',
                'provider' => 'mistral',
            ], 429);
        }

        return response()->json([
            'analysis' => $this->resolveStructuredResponsePayload($response),
            'image_path' => $imagePath,
        ]);
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
}
