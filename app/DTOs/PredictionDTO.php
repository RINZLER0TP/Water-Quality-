<?php

namespace App\DTOs;

class PredictionDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $trainingJobId,
        public readonly array $inputData,
        public readonly string $predictedClass,
        public readonly ?float $confidence = null,
        public readonly ?int $executionTimeMs = null
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'training_job_id' => $this->trainingJobId,
            'input_data' => $this->inputData,
            'predicted_class' => $this->predictedClass,
            'confidence' => $this->confidence,
            'execution_time_ms' => $this->executionTimeMs,
        ];
    }
}
