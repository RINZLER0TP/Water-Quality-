<?php

namespace App\DTOs;

use App\Enums\TrainingAlgorithm;

readonly class TrainingJobDTO
{
    public function __construct(
        public int $trainingConfigurationId,
        public int $createdBy,
        public int $datasetId,
        public string $targetColumn,
        public TrainingAlgorithm $algorithm,
        public array $parameters = [],
        public string $status = 'pending',
        public int $crossValidationFolds = 10,
        public int $randomSeed = 42,
    ) {
    }
}
