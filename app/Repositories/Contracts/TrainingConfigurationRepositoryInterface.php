<?php

namespace App\Repositories\Contracts;

use App\DTOs\TrainingConfigurationDTO;
use App\Models\TrainingConfiguration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TrainingConfigurationRepositoryInterface
{
    public function find(int $id): ?TrainingConfiguration;

    public function paginate(string $search = '', int $perPage = 10): LengthAwarePaginator;

    public function summary(string $search = ''): array;

    public function create(TrainingConfigurationDTO $dto): TrainingConfiguration;

    public function delete(TrainingConfiguration $configuration): bool;
}