<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePhotoGalleryRequest;
use App\Models\PhotoGallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoGalleryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $photos = $request->user()
            ->photoGalleries()
            ->orderBy('taken_at', 'desc')
            ->paginate(20);

        return response()->json($photos);
    }

    public function store(StorePhotoGalleryRequest $request): JsonResponse
    {
        $path = $request->file('photo')->store('gallery', 'public');

        $photo = $request->user()->photoGalleries()->create([
            'photo_url' => $path,
            'caption' => $request->validated('caption'),
            'taken_at' => $request->validated('taken_at'),
        ]);

        return response()->json($photo, 201);
    }

    public function destroy(Request $request, PhotoGallery $photoGallery): JsonResponse
    {
        $this->authorize('delete', $photoGallery);

        $photoGallery->delete();

        return response()->json(['message' => 'Photo deleted successfully']);
    }
}
