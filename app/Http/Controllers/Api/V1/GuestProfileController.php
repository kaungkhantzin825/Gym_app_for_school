<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GuestProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuestProfileController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'app_id' => ['required', 'string', 'max:64'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'age' => ['nullable', 'integer', 'min:10', 'max:120'],
            'height' => ['nullable', 'numeric', 'min:50', 'max:280'],
            'current_weight' => ['nullable', 'numeric', 'min:20', 'max:400'],
            'goal' => ['nullable', 'string', 'max:64'],
        ]);

        $profile = GuestProfile::updateOrCreate(
            ['app_id' => $validated['app_id']],
            $validated
        );

        return response()->json([
            'message' => 'Guest profile saved successfully',
            'profile' => $profile,
        ], 200);
    }

    public function show(string $appId): JsonResponse
    {
        $profile = GuestProfile::where('app_id', $appId)->first();

        if (!$profile) {
            return response()->json([
                'message' => 'Guest profile not found',
            ], 404);
        }

        return response()->json([
            'profile' => $profile,
        ]);
    }
}
