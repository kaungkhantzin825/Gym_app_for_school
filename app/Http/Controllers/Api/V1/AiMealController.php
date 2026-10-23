<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\MealAnalyzer;
use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Services\AiKeyRotatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Image;

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
            $response = AiKeyRotatorService::runWithRotation(function (string $provider, string $key) use ($agent, $imagePath) {
                $agent->setProvider($provider);

                return $agent->prompt(
                    'Analyze this meal photo and identify all food items with their nutritional estimates.',
                    [Image::fromStorage($imagePath, 'public')],
                );
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($imagePath);
            Log::warning("Meal scan failed across all keys: {$exception->getMessage()}");

            return response()->json([
                'message' => 'Failed to analyze meal image: '.$exception->getMessage(),
            ], 500);
        }

        $analysisPayload = $this->resolveStructuredResponsePayload($response);

        if (empty($analysisPayload) || empty($analysisPayload['foods'])) {
            return response()->json([
                'message' => 'Could not identify any food items in the image. Please try a clearer photo.',
                'image_url' => Meal::photoUrl($imagePath),
                'image_path' => $imagePath,
            ], 422);
        }

        return response()->json([
            'foods' => $analysisPayload['foods'],
            'meal_name' => $analysisPayload['meal_name'] ?? 'Analyzed Meal',
            'confidence' => $analysisPayload['confidence'] ?? 0.8,
            // Served by the media route, not /storage/... - the latter 403s on
            // the production host, so the preview would silently break.
            'image_url' => Meal::photoUrl($imagePath),
            'image_path' => $imagePath,
            'total_calories' => array_sum(array_column($analysisPayload['foods'], 'calories')),
            'total_protein' => array_sum(array_column($analysisPayload['foods'], 'protein')),
            'total_carbs' => array_sum(array_column($analysisPayload['foods'], 'carbs')),
            'total_fat' => array_sum(array_column($analysisPayload['foods'], 'fat')),
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
