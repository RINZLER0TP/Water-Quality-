<?php

namespace App\Policies;

use App\Models\Dataset;
use App\Models\User;

class DatasetPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageDatasets($user);
    }

    public function view(User $user, Dataset $dataset): bool
    {
        return $this->canManageDatasets($user) || $dataset->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->canManageDatasets($user);
    }

    public function delete(User $user, Dataset $dataset): bool
    {
        return $this->canManageDatasets($user) || $dataset->user_id === $user->id;
    }

    private function canManageDatasets(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'scientist', 'analyst']);
    }
}
