<?php

namespace App\Repositories\Contracts;

use App\DTOs\TrainingJobDTO;
use App\Models\TrainingJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TrainingJobRepositoryInterface
{
    public function find(int $id): ?TrainingJob;

    public function paginate(string $search = '', int $perPage = 10): LengthAwarePaginator;

    public function summary(string $search = ''): array;

    public function create(TrainingJobDTO $dto): TrainingJob;

    public function update(TrainingJob $job, array $attributes): TrainingJob;
}
