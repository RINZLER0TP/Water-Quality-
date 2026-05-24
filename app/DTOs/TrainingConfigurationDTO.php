<?php

namespace App\DTOs;

use App\Enums\TrainingAlgorithm;

readonly class TrainingConfigurationDTO
{
    public function __construct(
        public int $datasetId,
        public int $createdBy,
        public string $targetColumn,
        public TrainingAlgorithm $algorithm,
        public array $parameters = [],
        public array $analysis = [],
    ) {
    }
}