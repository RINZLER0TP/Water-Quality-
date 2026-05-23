<?php

namespace App\Repositories\Contracts;

use App\DTOs\DatasetDTO;
use App\Models\Dataset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DatasetRepositoryInterface
{
    public function find(int $id): ?Dataset;

    public function paginate(string $search = '', int $perPage = 10): LengthAwarePaginator;

    public function create(DatasetDTO $dto): Dataset;

    public function delete(Dataset $dataset): bool;
}
