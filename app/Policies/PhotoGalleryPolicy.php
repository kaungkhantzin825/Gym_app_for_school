<?php

namespace App\Policies;

use App\Models\PhotoGallery;
use App\Models\User;

class PhotoGalleryPolicy
{
    public function delete(User $user, PhotoGallery $photoGallery): bool
    {
        return $user->id === $photoGallery->user_id;
    }
}
