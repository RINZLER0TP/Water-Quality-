<?php

namespace App\Services\TrainingConfigurations;

use App\DTOs\TrainingConfigurationDTO;
use App\Enums\TrainingAlgorithm;
use App\Models\Dataset;
use App\Models\TrainingConfiguration;
use App\Repositories\Contracts\TrainingConfigurationRepositoryInterface;
use App\Services\Datasets\CsvDatasetInspector;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TrainingConfigurationService
{
    public function __construct(
        private TrainingConfigurationRepositoryInterface $repository,
        private CsvDatasetInspector $inspector,
    ) {
    }

    public function paginate(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $perPage);
    }

    public function summary(string $search = ''): array
    {
        return $this->repository->summary($search);
    }

    public function find(int $id): ?TrainingConfiguration
    {
        return $this->repository->find($id);
    }

    public function create(TrainingConfigurationDTO $dto): TrainingConfiguration
    {
        return $this->repository->create($dto);
    }

    public function delete(TrainingConfiguration $configuration): bool
    {
        return $this->repository->delete($configuration);
    }

    public function previewDataset(Dataset $dataset, int $previewLimit = 10): array
    {
        if (! Storage::disk('local')->exists($dataset->file_path)) {
            throw new RuntimeException('El archivo del dataset no existe en storage.');
        }

        $absolutePath = Storage::disk('local')->path($dataset->file_path);

        return $this->inspector->analyzeFile($absolutePath, $previewLimit);
    }

    public function availableAlgorithms(): array
    {
        return TrainingAlgorithm::options();
    }

    public function algorithmSchema(TrainingAlgorithm $algorithm): array
    {
        return $algorithm->parameterSchema();
    }

    public function defaultParameters(TrainingAlgorithm $algorithm): array
    {
        return $algorithm->defaultParameters();
    }
}