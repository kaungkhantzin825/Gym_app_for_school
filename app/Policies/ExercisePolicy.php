<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy
{
    /**
     * Admins moderate the whole library, including rows members created.
     * Returning null falls through to the ownership checks below, so a member
     * still only reaches their own rows.
     */
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->user_id;
    }

    public function update(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->user_id;
    }

    public function delete(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->user_id;
    }
}
