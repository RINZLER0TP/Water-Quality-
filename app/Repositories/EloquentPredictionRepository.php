<?php

namespace App\Repositories;

use App\DTOs\PredictionDTO;
use App\Models\Prediction;
use App\Repositories\Interfaces\PredictionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentPredictionRepository implements PredictionRepositoryInterface
{
    public function create(PredictionDTO $dto): Prediction
    {
        return Prediction::create($dto->toArray());
    }

    public function findById(int $id): ?Prediction
    {
        return Prediction::with(['trainingJob.dataset'])->find($id);
    }

    public function paginateForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Prediction::with(['trainingJob.dataset'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }
}
