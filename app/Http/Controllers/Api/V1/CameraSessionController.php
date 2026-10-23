<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCameraSessionRequest;
use App\Models\CameraSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Camera-trainer sessions, synced from the phone.
 *
 * The app records every session to SQLite first, so training works with no
 * connection; this endpoint is where those rows land once there is one. The
 * `client_id` is generated on the device, which makes a retried upload
 * idempotent instead of creating duplicates.
 */
class CameraSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sessions = $request->user()
            ->cameraSessions()
            ->orderByDesc('started_at')
            ->paginate(30);

        return response()->json($sessions);
    }

    public function store(StoreCameraSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $attributes = [
            'user_id' => $request->user()->id,
            'exercise_type' => $data['exercise_type'],
            'exercise_name' => $data['exercise_name'],
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'],
            'duration_seconds' => $data['duration_seconds'] ?? 0,
            'sets_count' => $data['sets_count'] ?? 0,
            'total_reps' => $data['total_reps'] ?? 0,
            'avg_form_score' => $data['avg_form_score'] ?? null,
            'sets' => $data['sets'] ?? [],
            'source' => $data['source'] ?? 'pose_tracker',
        ];

        $clientId = $data['client_id'] ?? null;

        $session = $clientId !== null
            ? CameraSession::updateOrCreate(['client_id' => $clientId], $attributes)
            : CameraSession::create($attributes);

        return response()->json([
            'message' => 'Camera session saved.',
            'session' => $session->fresh(),
        ], $session->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Totals, per-exercise personal bests and this week's activity.
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $totals = DB::table('camera_sessions')
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as sessions')
            ->selectRaw('COALESCE(SUM(total_reps), 0) as reps')
            ->selectRaw('COALESCE(SUM(duration_seconds), 0) as seconds')
            ->first();

        $personalBests = DB::table('camera_sessions')
            ->where('user_id', $userId)
            ->groupBy('exercise_name')
            ->select('exercise_name')
            ->selectRaw('MAX(total_reps) as best_reps')
            ->selectRaw('MAX(avg_form_score) as best_form_score')
            ->selectRaw('COUNT(*) as sessions')
            ->orderByDesc('best_reps')
            ->get();

        return response()->json([
            'totals' => [
                'sessions' => (int) $totals->sessions,
                'reps' => (int) $totals->reps,
                'seconds' => (int) $totals->seconds,
            ],
            'this_week' => DB::table('camera_sessions')
                ->where('user_id', $userId)
                ->where('started_at', '>=', now()->subDays(7))
                ->count(),
            'personal_bests' => $personalBests,
        ]);
    }
}
