<?php

namespace App\Actions;

use App\DTOs\WaterSampleDTO;
use App\Repositories\Contracts\WaterSampleRepositoryInterface;
use App\Models\WaterSample;

class CreateWaterSampleAction
{
    public function __construct(private WaterSampleRepositoryInterface $repository)
    {
    }

    public function __invoke(WaterSampleDTO $dto): WaterSample
    {
        $data = [
            'ph' => $dto->ph,
            'temperature' => $dto->temperature,
            'status' => $dto->status,
            'collected_at' => $dto->collectedAt?->toDateTimeString(),
        ];

        return $this->repository->create($data);
    }
}
