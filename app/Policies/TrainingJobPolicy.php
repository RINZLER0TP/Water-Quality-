<?php

namespace App\Policies;

use App\Models\TrainingConfiguration;
use App\Models\TrainingJob;
use App\Models\User;

class TrainingJobPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TrainingJob $trainingJob): bool
    {
        return $this->canManageJob($user, $trainingJob);
    }

    public function create(User $user, ?TrainingConfiguration $configuration = null): bool
    {
        if ($configuration === null) {
            return true;
        }

        if ($configuration->created_by === $user->id) {
            return true;
        }

        return method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    public function delete(User $user, TrainingJob $trainingJob): bool
    {
        return $this->canManageJob($user, $trainingJob);
    }

    private function canManageJob(User $user, TrainingJob $trainingJob): bool
    {
        if ($trainingJob->created_by === $user->id) {
            return true;
        }

        return method_exists($user, 'hasRole') && $user->hasRole('admin');
    }
}
