<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreExerciseRequest;
use App\Http\Requests\Api\V1\UpdateExerciseRequest;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $exercises = $request->user()
            ->exercises()
            ->when($request->date, fn ($q) => $q->whereDate('exercise_time', $request->date))
            ->when($request->type, fn ($q) => $q->where('exercise_type', $request->type))
            ->orderBy('exercise_time', 'desc')
            ->paginate(20);

        return response()->json($exercises);
    }

    public function store(StoreExerciseRequest $request): JsonResponse
    {
        $exercise = $request->user()->exercises()->create($request->validated());

        return response()->json($exercise, 201);
    }

    public function show(Request $request, Exercise $exercise): JsonResponse
    {
        $this->authorize('view', $exercise);

        return response()->json($exercise);
    }

    public function update(UpdateExerciseRequest $request, Exercise $exercise): JsonResponse
    {
        $this->authorize('update', $exercise);

        $exercise->update($request->validated());

        return response()->json($exercise);
    }

    public function destroy(Request $request, Exercise $exercise): JsonResponse
    {
        $this->authorize('delete', $exercise);

        $exercise->delete();

        return response()->json(['message' => 'Exercise deleted successfully']);
    }
}
