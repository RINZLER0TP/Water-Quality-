<?php

namespace App\Policies;

use App\Models\Dataset;
use App\Models\User;

class DatasetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Dataset $dataset): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function download(User $user, Dataset $dataset): bool
    {
        return $this->canManageDataset($user, $dataset);
    }

    public function delete(User $user, Dataset $dataset): bool
    {
        return $this->canManageDataset($user, $dataset);
    }

    private function canManageDataset(User $user, Dataset $dataset): bool
    {
        if ($dataset->uploaded_by === $user->id) {
            return true;
        }

        return method_exists($user, 'hasRole') && $user->hasRole('admin');
    }
}
