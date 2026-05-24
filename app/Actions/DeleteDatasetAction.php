<?php

namespace App\Actions;

use App\Models\Dataset;
use App\Models\User;
use App\Services\Datasets\DatasetService;

class DeleteDatasetAction
{
    public function __construct(private DatasetService $service)
    {
    }

    public function __invoke(User $user, Dataset $dataset): bool
    {
        return $this->service->delete($dataset);
    }
}