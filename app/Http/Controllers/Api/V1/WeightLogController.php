<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWeightLogRequest;
use App\Models\WeightLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeightLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = $request->user()
            ->weightLogs()
            ->orderBy('logged_at', 'desc')
            ->paginate(20);

        return response()->json($logs);
    }

    public function store(StoreWeightLogRequest $request): JsonResponse
    {
        $log = $request->user()->weightLogs()->create($request->validated());

        return response()->json($log, 201);
    }

    public function destroy(Request $request, WeightLog $weightLog): JsonResponse
    {
        $this->authorize('delete', $weightLog);

        $weightLog->delete();

        return response()->json(['message' => 'Weight log deleted successfully']);
    }
}
