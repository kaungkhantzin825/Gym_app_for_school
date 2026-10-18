<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeightLog;

class WeightLogPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function delete(User $user, WeightLog $weightLog): bool
    {
        return $user->id === $weightLog->user_id;
    }
}
