<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWorkoutSetRequest;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkoutSetController extends Controller
{
    public function store(StoreWorkoutSetRequest $request, Workout $workout): JsonResponse
    {
        $this->authorize('update', $workout);

        $workoutSet = $workout->workoutSets()->create($request->validated());

        return response()->json($workoutSet, 201);
    }

    public function destroy(Request $request, Workout $workout, WorkoutSet $workoutSet): JsonResponse
    {
        $this->authorize('update', $workout);

        $workoutSet->delete();

        return response()->json(['message' => 'Workout set deleted successfully']);
    }
}
