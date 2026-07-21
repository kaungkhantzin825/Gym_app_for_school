<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMealRequest;
use App\Http\Requests\Api\V1\UpdateMealRequest;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $meals = $request->user()
            ->meals()
            ->with('foodLogs')
            ->when($request->date, fn ($q) => $q->whereDate('meal_time', $request->date))
            ->orderBy('meal_time', 'desc')
            ->paginate(20);

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
}
