<?php

namespace App\Actions;

use App\DTOs\TrainingJobDTO;
use App\Enums\TrainingAlgorithm;
use App\Models\TrainingConfiguration;
use App\Models\TrainingJob;
use App\Models\User;
use App\Services\TrainingJobs\TrainingJobService;

class RunTrainingJobAction
{
    public function __construct(private TrainingJobService $service)
    {
    }

    public function __invoke(User $user, TrainingConfiguration $configuration): TrainingJob
    {
        $configuration->loadMissing('dataset');

        $dto = new TrainingJobDTO(
            trainingConfigurationId: $configuration->id,
            createdBy: $user->id,
            datasetId: (int) $configuration->dataset_id,
            targetColumn: $configuration->target_column,
            algorithm: TrainingAlgorithm::from((string) $configuration->algorithm->value),
            parameters: $configuration->parameters ?? [],
            status: 'pending',
            crossValidationFolds: (int) ($configuration->parameters['cross_validation_folds'] ?? config('weka.cross_validation_folds', 10)),
            randomSeed: (int) ($configuration->parameters['random_seed'] ?? config('weka.random_seed', 42)),
        );

        return $this->service->launch($dto);
    }
}
