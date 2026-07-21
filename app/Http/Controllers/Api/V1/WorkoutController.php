<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWorkoutRequest;
use App\Http\Requests\Api\V1\UpdateWorkoutRequest;
use App\Models\Workout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $workouts = $request->user()
            ->workouts()
            ->with('workoutSets.exercise')
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        return response()->json($workouts);
    }

    public function store(StoreWorkoutRequest $request): JsonResponse
    {
        $workout = $request->user()->workouts()->create($request->validated());

        return response()->json($workout, 201);
    }

    public function show(Request $request, Workout $workout): JsonResponse
    {
        $this->authorize('view', $workout);

        return response()->json($workout->load('workoutSets.exercise'));
    }

    public function update(UpdateWorkoutRequest $request, Workout $workout): JsonResponse
    {
        $this->authorize('update', $workout);

        $workout->update($request->validated());

        return response()->json($workout);
    }

    public function destroy(Request $request, Workout $workout): JsonResponse
    {
        $this->authorize('delete', $workout);

        $workout->delete();

        return response()->json(['message' => 'Workout deleted successfully']);
    }
}
