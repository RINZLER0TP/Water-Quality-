<?php

namespace App\Actions\Predictions;

use App\DTOs\PredictionDTO;
use App\Models\Prediction;
use App\Models\TrainingJob;
use App\Repositories\Interfaces\PredictionRepositoryInterface;
use App\Services\ML\PredictionService;

class RunPredictionAction
{
    public function __construct(
        private PredictionService $predictionService,
        private PredictionRepositoryInterface $predictionRepository
    ) {}

    public function execute(TrainingJob $job, array $inputData, int $userId): Prediction
    {
        // 1. Ejecutar predicción en el motor Weka Java
        $result = $this->predictionService->predict($job, $inputData);

        // 2. Crear el DTO con el resultado
        $dto = new PredictionDTO(
            userId: $userId,
            trainingJobId: $job->id,
            inputData: $inputData,
            predictedClass: $result['class'],
            confidence: $result['confidence'] ?? null,
            executionTimeMs: $result['execution_time_ms'] ?? null
        );

        // 3. Persistir en base de datos
        return $this->predictionRepository->create($dto);
    }
}
