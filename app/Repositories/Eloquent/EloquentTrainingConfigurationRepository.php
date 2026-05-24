<?php

namespace App\Repositories\Eloquent;

use App\DTOs\TrainingConfigurationDTO;
use App\Models\TrainingConfiguration;
use App\Repositories\Contracts\TrainingConfigurationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTrainingConfigurationRepository implements TrainingConfigurationRepositoryInterface
{
    public function find(int $id): ?TrainingConfiguration
    {
        return TrainingConfiguration::with(['dataset.uploader', 'creator'])->find($id);
    }

    public function paginate(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return $this->baseQuery($search)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function summary(string $search = ''): array
    {
        $query = $this->baseQuery($search);

        return [
            'total_configurations' => (clone $query)->count(),
            'total_datasets' => (clone $query)->distinct()->count('dataset_id'),
            'zeror' => (clone $query)->where('algorithm', 'zeror')->count(),
            'oner' => (clone $query)->where('algorithm', 'oner')->count(),
            'naive_bayes' => (clone $query)->where('algorithm', 'naive_bayes')->count(),
            'logistic' => (clone $query)->where('algorithm', 'logistic')->count(),
            'latest_created_at' => (clone $query)->max('created_at'),
        ];
    }

    public function create(TrainingConfigurationDTO $dto): TrainingConfiguration
    {
        return TrainingConfiguration::create([
            'dataset_id' => $dto->datasetId,
            'created_by' => $dto->createdBy,
            'target_column' => $dto->targetColumn,
            'algorithm' => $dto->algorithm->value,
            'parameters' => $dto->parameters,
            'analysis' => $dto->analysis,
        ]);
    }

    public function delete(TrainingConfiguration $configuration): bool
    {
        return (bool) $configuration->delete();
    }

    private function baseQuery(string $search = '')
    {
        return TrainingConfiguration::query()
            ->whereHas('dataset')
            ->with(['dataset.uploader', 'creator'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('target_column', 'like', "%{$search}%")
                        ->orWhere('algorithm', 'like', "%{$search}%")
                        ->orWhereHas('dataset', function ($datasetQuery) use ($search): void {
                            $datasetQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('original_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('creator', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });
    }
}