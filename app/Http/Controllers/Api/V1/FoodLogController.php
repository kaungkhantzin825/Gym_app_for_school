<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFoodLogRequest;
use App\Models\FoodLog;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodLogController extends Controller
{
    public function store(StoreFoodLogRequest $request, Meal $meal): JsonResponse
    {
        $this->authorize('update', $meal);

        $foodLog = $meal->foodLogs()->create($request->validated());

        $this->recalculateMealTotals($meal);

        return response()->json($foodLog, 201);
    }

    public function destroy(Request $request, Meal $meal, FoodLog $foodLog): JsonResponse
    {
        $this->authorize('update', $meal);

        $foodLog->delete();

        $this->recalculateMealTotals($meal);

        return response()->json(['message' => 'Food log deleted successfully']);
    }

    private function recalculateMealTotals(Meal $meal): void
    {
        $meal->update([
            'total_calories' => $meal->foodLogs()->sum('calories'),
            'total_protein' => $meal->foodLogs()->sum('protein'),
            'total_carbs' => $meal->foodLogs()->sum('carbs'),
            'total_fat' => $meal->foodLogs()->sum('fat'),
        ]);
    }
}
