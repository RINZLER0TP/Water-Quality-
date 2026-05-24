<?php

namespace App\Actions;

use App\DTOs\TrainingConfigurationDTO;
use App\Enums\TrainingAlgorithm;
use App\Models\Dataset;
use App\Models\TrainingConfiguration;
use App\Models\User;
use App\Services\TrainingConfigurations\TrainingConfigurationService;

class StoreTrainingConfigurationAction
{
    public function __construct(private TrainingConfigurationService $service)
    {
    }

    public function __invoke(
        User $user,
        Dataset $dataset,
        string $targetColumn,
        TrainingAlgorithm $algorithm,
        array $parameters = []
    ): TrainingConfiguration {
        $analysis = $this->service->previewDataset($dataset);

        $dto = new TrainingConfigurationDTO(
            datasetId: $dataset->id,
            createdBy: $user->id,
            targetColumn: $targetColumn,
            algorithm: $algorithm,
            parameters: $parameters,
            analysis: $analysis,
        );

        return $this->service->create($dto);
    }
}