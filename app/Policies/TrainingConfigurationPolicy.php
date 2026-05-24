<?php

namespace App\Policies;

use App\Models\Dataset;
use App\Models\TrainingConfiguration;
use App\Models\User;

class TrainingConfigurationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TrainingConfiguration $trainingConfiguration): bool
    {
        return $this->canManageConfiguration($user, $trainingConfiguration);
    }

    public function create(User $user, ?Dataset $dataset = null): bool
    {
        if ($dataset === null) {
            return true;
        }

        return $this->canManageDataset($user, $dataset);
    }

    public function delete(User $user, TrainingConfiguration $trainingConfiguration): bool
    {
        return $this->canManageConfiguration($user, $trainingConfiguration);
    }

    private function canManageConfiguration(User $user, TrainingConfiguration $trainingConfiguration): bool
    {
        if ($trainingConfiguration->created_by === $user->id) {
            return true;
        }

        return method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    private function canManageDataset(User $user, Dataset $dataset): bool
    {
        if ($dataset->uploaded_by === $user->id) {
            return true;
        }

        return method_exists($user, 'hasRole') && $user->hasRole('admin');
    }
}