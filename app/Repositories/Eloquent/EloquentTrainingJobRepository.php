<?php

namespace App\Repositories\Eloquent;

use App\DTOs\TrainingJobDTO;
use App\Enums\TrainingJobStatus;
use App\Models\TrainingJob;
use App\Repositories\Contracts\TrainingJobRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTrainingJobRepository implements TrainingJobRepositoryInterface
{
    public function find(int $id): ?TrainingJob
    {
        return TrainingJob::query()->with(['trainingConfiguration.dataset.uploader', 'creator'])->find($id);
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

        $completedJobs = (clone $query)->where('status', TrainingJobStatus::COMPLETED->value)->get();

        return [
            'total_jobs' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', TrainingJobStatus::PENDING->value)->count(),
            'running' => (clone $query)->where('status', TrainingJobStatus::RUNNING->value)->count(),
            'completed' => (clone $query)->where('status', TrainingJobStatus::COMPLETED->value)->count(),
            'failed' => (clone $query)->where('status', TrainingJobStatus::FAILED->value)->count(),
            'average_accuracy' => (float) $completedJobs->avg(static fn (TrainingJob $job): float => (float) data_get($job->metrics, 'accuracy', 0)),
            'average_f1' => (float) $completedJobs->avg(static fn (TrainingJob $job): float => (float) data_get($job->metrics, 'f1_score', 0)),
            'latest_completed_at' => (clone $query)->whereNotNull('completed_at')->max('completed_at'),
        ];
    }

    public function create(TrainingJobDTO $dto): TrainingJob
    {
        return TrainingJob::create([
            'training_configuration_id' => $dto->trainingConfigurationId,
            'created_by' => $dto->createdBy,
            'dataset_id' => $dto->datasetId,
            'algorithm' => $dto->algorithm->value,
            'target_column' => $dto->targetColumn,
            'parameters' => $dto->parameters,
            'status' => $dto->status,
            'cross_validation_folds' => $dto->crossValidationFolds,
            'random_seed' => $dto->randomSeed,
        ]);
    }

    public function update(TrainingJob $job, array $attributes): TrainingJob
    {
        $job->fill($attributes);
        $job->save();

        return $job->refresh();
    }

    private function baseQuery(string $search = '')
    {
        return TrainingJob::query()
            ->with(['trainingConfiguration.dataset.uploader', 'creator'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('algorithm', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('target_column', 'like', "%{$search}%")
                        ->orWhereHas('trainingConfiguration', function ($configQuery) use ($search): void {
                            $configQuery->where('target_column', 'like', "%{$search}%")
                                ->orWhere('algorithm', 'like', "%{$search}%")
                                ->orWhereHas('dataset', function ($datasetQuery) use ($search): void {
                                    $datasetQuery->where('name', 'like', "%{$search}%")
                                        ->orWhere('original_name', 'like', "%{$search}%");
                                });
                        })
                        ->orWhereHas('creator', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });
    }
}
