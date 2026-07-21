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
            ->when($request->gender, fn ($q) => $q->where('gender_target', $request->gender)->orWhere('gender_target', 'both'))
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
