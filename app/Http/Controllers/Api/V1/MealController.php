<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMealRequest;
use App\Http\Requests\Api\V1\UpdateMealRequest;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MealController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $meals = $request->user()
            ->meals()
            ->with('foodLogs')
            ->when($request->date, function ($q) use ($request) {
                $date = $request->date;
                $q->where(function ($sub) use ($date) {
                    $sub->whereDate('meal_time', $date)
                        ->orWhereDate('created_at', $date);
                });
            })
            ->orderBy('meal_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($meals);
    }

    public function store(StoreMealRequest $request): JsonResponse
    {
        $meal = $request->user()->meals()->create($request->validated());

        return response()->json($meal, 201);
    }

    public function show(Request $request, Meal $meal): JsonResponse
    {
        $this->authorize('view', $meal);

        return response()->json($meal->load('foodLogs'));
    }

    public function update(UpdateMealRequest $request, Meal $meal): JsonResponse
    {
        $this->authorize('update', $meal);

        $meal->update($request->validated());

        return response()->json($meal);
    }

    public function destroy(Request $request, Meal $meal): JsonResponse
    {
        $this->authorize('delete', $meal);

        $meal->delete();

        return response()->json(['message' => 'Meal deleted successfully']);
    }

    /**
     * Attach or replace the photo of an already-saved meal.
     *
     * The AI scan already stores its own copy, so this exists for meals logged
     * by hand and for replacing a bad photo.
     */
    public function uploadPhoto(Request $request, Meal $meal): JsonResponse
    {
        $this->authorize('update', $meal);

        $request->validate([
            'image' => ['required', 'image', 'max:10240'],
        ]);

        $previous = $meal->photo_path;
        $path = $request->file('image')->store('meals', 'public');

        $meal->update(['photo_path' => $path]);

        // Drop the file this one replaces, or a few retakes quietly fill the
        // disk. Only after the row points at the new file.
        if ($previous !== null && ! str_starts_with($previous, 'http')) {
            Storage::disk('public')->delete($previous);
        }

        return response()->json($meal->fresh()->load('foodLogs'));
    }

    public function destroyPhoto(Request $request, Meal $meal): JsonResponse
    {
        $this->authorize('update', $meal);

        if ($meal->photo_path !== null && ! str_starts_with($meal->photo_path, 'http')) {
            Storage::disk('public')->delete($meal->photo_path);
        }

        $meal->update(['photo_path' => null]);

        return response()->json($meal->fresh()->load('foodLogs'));
    }
}
