<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves user-uploaded images through PHP.
 *
 * The `public` disk normally reaches the browser through `public/storage`, a
 * symlink created by `php artisan storage:link`. When that link is missing, or
 * the web server refuses to follow it, every `/storage/...` URL answers 403 and
 * uploaded photos silently fail to load - which is exactly what the app shows
 * as a broken avatar after an upload "succeeds".
 *
 * PHP can always read a file it just wrote, so this route is the reliable path
 * and is what `User::profilePhotoUrl()` points at.
 */
class MediaController extends Controller
{
    /**
     * Only these directories under storage/app/public may be served.
     */
    private const DIRECTORIES = [
        'profile-photos',
        'gallery',
        'coach-images',
        'meal-scans',
        'meals',
    ];

    public function show(string $directory, string $filename): StreamedResponse
    {
        abort_unless(in_array($directory, self::DIRECTORIES, true), 404);

        // The path is rebuilt from a bare file name, so a crafted `..` cannot
        // climb out of the directory.
        abort_unless($filename === basename($filename), 404);
        abort_if(str_contains($filename, '..'), 404);

        $path = $directory.'/'.$filename;
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
