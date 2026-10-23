<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BookmarkedExercise;
use App\Models\TutorialVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkedExerciseController extends Controller
{
    /**
     * List bookmarked exercises for authenticated user or guest by app_id.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth('sanctum')->id();
        $appId = $request->query('app_id') ?? $request->header('X-App-Id');

        $query = BookmarkedExercise::with('tutorialVideo');

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($appId) {
            $query->where('app_id', $appId);
        } else {
            return response()->json([
                'message' => 'app_id or user authentication is required',
                'data' => [],
            ], 200);
        }

        $bookmarks = $query->latest()->get();

        return response()->json([
            'data' => $bookmarks,
            'count' => $bookmarks->count(),
        ]);
    }

    /**
     * Toggle bookmark state for an exercise.
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exercise_title' => ['required', 'string', 'max:255'],
            'muscle_group' => ['nullable', 'string', 'max:64'],
            'tutorial_video_id' => ['nullable', 'integer'],
            'app_id' => ['nullable', 'string', 'max:64'],
        ]);

        $userId = auth('sanctum')->id();
        $appId = $validated['app_id'] ?? $request->header('X-App-Id');

        if (!$userId && !$appId) {
            return response()->json([
                'message' => 'Authentication or app_id required to save bookmark',
            ], 422);
        }

        // Try to match tutorial_video_id if not provided
        $video = null;
        if (!empty($validated['tutorial_video_id'])) {
            $video = TutorialVideo::find($validated['tutorial_video_id']);
        }
        if (!$video) {
            $video = TutorialVideo::where('title', $validated['exercise_title'])->first();
        }

        // Check if bookmark already exists
        $query = BookmarkedExercise::where('exercise_title', $validated['exercise_title']);
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('app_id', $appId);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'message' => 'Bookmark removed',
                'is_bookmarked' => false,
                'exercise_title' => $validated['exercise_title'],
            ]);
        }

        $created = BookmarkedExercise::create([
            'user_id' => $userId,
            'app_id' => $appId,
            'tutorial_video_id' => $video?->id,
            'exercise_title' => $validated['exercise_title'],
            'muscle_group' => $validated['muscle_group'] ?? $video?->muscle_group ?? 'chest',
        ]);

        return response()->json([
            'message' => 'Bookmark saved successfully',
            'is_bookmarked' => true,
            'bookmark' => $created,
        ], 201);
    }

    /**
     * Remove a bookmark by ID.
     */
    public function destroy(int $id): JsonResponse
    {
        $userId = auth('sanctum')->id();
        $bookmark = BookmarkedExercise::find($id);

        if (!$bookmark) {
            return response()->json(['message' => 'Bookmark not found'], 404);
        }

        if ($userId && $bookmark->user_id && $bookmark->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookmark->delete();

        return response()->json(['message' => 'Bookmark removed']);
    }
}
