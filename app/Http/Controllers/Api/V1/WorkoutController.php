<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWorkoutRequest;
use App\Http\Requests\Api\V1\UpdateWorkoutRequest;
use App\Models\Workout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkoutController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $workouts = $request->user()
            ->workouts()
            ->with('workoutSets.exercise')
            ->when($request->query('from'), fn ($query, $from) => $query->where(
                'started_at',
                '>=',
                Carbon::parse($from)->startOfDay(),
            ))
            ->when($request->query('to'), fn ($query, $to) => $query->where(
                'started_at',
                '<=',
                Carbon::parse($to)->endOfDay(),
            ))
            ->orderBy('started_at', 'desc')
            // The calendar asks for a whole month at once; the default 20 would
            // silently cut it short for anyone training most days.
            ->paginate((int) $request->query('per_page', 20));

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
