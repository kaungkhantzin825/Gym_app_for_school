<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TutorialVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorialVideoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $videos = TutorialVideo::query()
            // The unisex clause must be grouped, otherwise it escapes the
            // muscle-group filter below because OR binds looser than AND.
            ->when($request->gender, fn ($q) => $q->where(
                fn ($sub) => $sub->where('gender_target', $request->gender)
                    ->orWhere('gender_target', 'both')
            ))
            ->when($request->muscle_group, fn ($q) => $q->where('muscle_group', $request->muscle_group))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($videos);
    }

    public function show(TutorialVideo $tutorialVideo): JsonResponse
    {
        return response()->json($tutorialVideo);
    }
}
