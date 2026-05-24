<?php

namespace App\Policies;

use App\Models\Prediction;
use App\Models\User;

class PredictionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Prediction $prediction): bool
    {
        return $user->id === $prediction->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Prediction $prediction): bool
    {
        return false;
    }

    public function delete(User $user, Prediction $prediction): bool
    {
        return $user->id === $prediction->user_id;
    }
}
