<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WaterSample;

class WaterSamplePolicy
{
    public function view(User $user, WaterSample $sample): bool
    {
        return $user->hasRole('admin') || $user->id === $sample->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('scientist') || $user->hasRole('admin');
    }

    public function update(User $user, WaterSample $sample): bool
    {
        return $user->hasRole('admin') || $user->id === $sample->user_id;
    }

    public function delete(User $user, WaterSample $sample): bool
    {
        return $user->hasRole('admin');
    }
}
