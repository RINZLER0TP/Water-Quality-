<?php

namespace App\Repositories\Interfaces;

use App\DTOs\PredictionDTO;
use App\Models\Prediction;
use Illuminate\Pagination\LengthAwarePaginator;

interface PredictionRepositoryInterface
{
    public function create(PredictionDTO $dto): Prediction;
    public function findById(int $id): ?Prediction;
    public function paginateForUser(int $userId, int $perPage = 10): LengthAwarePaginator;
}
