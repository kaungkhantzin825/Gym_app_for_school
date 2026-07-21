<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Requests\Api\V1\UpdateProfilePhotoRequest;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->profileResponse($request));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('name', $validated) && $validated['name'] !== null) {
            $request->user()->update([
                'name' => $validated['name'],
            ]);
        }

        $profile = UserProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            Arr::except($validated, ['name']),
        );

        unset($profile);

        return response()->json($this->profileResponse($request));
    }

    public function uploadPhoto(UpdateProfilePhotoRequest $request): JsonResponse
    {
        $user = $request->user();

        if (is_string($user->profile_photo) && $user->profile_photo !== '') {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $photoPath = $request->file('photo')->store('profile-photos', 'public');

        $user->update([
            'profile_photo' => $photoPath,
        ]);

        return response()->json([
            'message' => 'Profile photo uploaded successfully',
            'profile_photo' => $photoPath,
            'profile_photo_url' => $this->profilePhotoUrl($photoPath),
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function profileResponse(Request $request): array
    {
        $user = $request->user()->loadMissing('profile');
        $profile = $user->profile;

        return array_merge(
            [
                'name' => $user->name,
                'profile_photo' => $user->profile_photo,
                'profile_photo_url' => $this->profilePhotoUrl($user->profile_photo),
            ],
            $profile?->toArray() ?? [],
        );
    }

    private function profilePhotoUrl(?string $profilePhoto): ?string
    {
        if ($profilePhoto === null || $profilePhoto === '') {
            return null;
        }

        return url(Storage::disk('public')->url($profilePhoto));
    }
}
