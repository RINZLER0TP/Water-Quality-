<?php

namespace App\Services\TrainingJobs;

use App\DTOs\TrainingJobDTO;
use App\Enums\TrainingJobStatus;
use App\Models\Dataset;
use App\Models\TrainingConfiguration;
use App\Models\TrainingJob;
use App\Repositories\Contracts\TrainingJobRepositoryInterface;
use App\Services\ML\JavaWekaClient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TrainingJobService
{
    public function __construct(
        private TrainingJobRepositoryInterface $repository,
        private JavaWekaClient $javaWekaClient,
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

    public function find(int $id): ?TrainingJob
    {
        return $this->repository->find($id);
    }

    public function launch(TrainingJobDTO $dto): TrainingJob
    {
        $job = $this->repository->create($dto);

        return $this->execute($job);
    }

    public function execute(TrainingJob $job): TrainingJob
    {
        $job->loadMissing(['trainingConfiguration.dataset', 'creator']);

        $configuration = $job->trainingConfiguration;
        $dataset = $configuration?->dataset;

        if (! $configuration instanceof TrainingConfiguration || ! $dataset instanceof Dataset) {
            throw new RuntimeException('La configuración o el dataset asociado no están disponibles.');
        }

        if (! Storage::disk('local')->exists($dataset->file_path)) {
            throw new RuntimeException('El archivo CSV del dataset no existe en storage.');
        }

        $modelPath = trim((string) config('weka.models_path', 'weka/models'), '/').'/training-job-'.$job->id.'.model';
        $logPath = trim((string) config('weka.logs_path', 'weka/logs'), '/').'/training-job-'.$job->id.'.log';

        Storage::disk('local')->makeDirectory(dirname($modelPath));
        Storage::disk('local')->makeDirectory(dirname($logPath));

        $this->repository->update($job, [
            'status' => TrainingJobStatus::RUNNING->value,
            'model_path' => $modelPath,
            'log_path' => $logPath,
            'started_at' => now(),
            'error_message' => null,
            'execution_log' => null,
        ]);

        $timeout = (int) config('weka.timeout_seconds', 1800);
        $folds = (int) ($job->cross_validation_folds ?: config('weka.cross_validation_folds', 10));
        $seed = (int) ($job->random_seed ?: config('weka.random_seed', 42));
        $absoluteModelPath = $this->absoluteStoragePath($modelPath);
        $absoluteLogPath = $this->absoluteStoragePath($logPath);

        $args = [
            '--csv='.$this->absoluteStoragePath($dataset->file_path),
            '--target='.$job->target_column,
            '--algorithm='.$job->algorithm->value,
            '--model='.$absoluteModelPath,
            '--log='.$absoluteLogPath,
            '--folds='.$folds,
            '--seed='.$seed,
            '--job-id='.$job->id,
        ];

        try {
            $output = $this->javaWekaClient->runJar((string) config('weka.jar_path'), $args, $timeout);
            $payload = $this->decodePayload($output);
            $metrics = $payload['metrics'] ?? [];
            $confusionMatrix = $payload['confusion_matrix'] ?? [];
            $modelOutput = (string) ($payload['model_path'] ?? $absoluteModelPath);

            if (! str_starts_with($modelOutput, '/') && ! str_starts_with($modelOutput, '\\') && ! preg_match('#^[A-Za-z]:[\\/]#', $modelOutput)) {
                $modelOutput = $this->absoluteStoragePath($modelOutput);
            }

            if (! file_exists($modelOutput)) {
                throw new RuntimeException('El archivo .model no fue generado por el engine Java.');
            }

            return $this->repository->update($job, [
                'status' => TrainingJobStatus::COMPLETED->value,
                'metrics' => $metrics,
                'confusion_matrix' => $confusionMatrix,
                'model_path' => $modelPath,
                'log_path' => $logPath,
                'execution_log' => $output,
                'completed_at' => now(),
                'training_time_ms' => (int) ($metrics['training_time_ms'] ?? 0),
            ]);
        } catch (\Throwable $throwable) {
            Log::error('Training job failed', [
                'training_job_id' => $job->id,
                'message' => $throwable->getMessage(),
            ]);

            return $this->repository->update($job, [
                'status' => TrainingJobStatus::FAILED->value,
                'error_message' => $throwable->getMessage(),
                'execution_log' => trim(($job->execution_log ?? '').PHP_EOL.$throwable->getMessage()),
                'completed_at' => now(),
            ]);
        }
    }

    public function metrics(TrainingJob $job): array
    {
        $job->loadMissing(['trainingConfiguration.dataset.uploader', 'creator']);

        return [
            'job' => $job,
            'metrics' => $job->metrics ?? [],
            'confusion_matrix' => $job->confusion_matrix ?? [],
        ];
    }

    private function decodePayload(string $output): array
    {
        $payload = json_decode(trim($output), true);

        if (! is_array($payload)) {
            throw new RuntimeException('La salida del engine Java no es un JSON válido.');
        }

        if (($payload['success'] ?? false) !== true) {
            throw new RuntimeException((string) ($payload['error'] ?? 'El entrenamiento falló sin detalle.'));
        }

        return $payload;
    }

    private function absoluteStoragePath(string $path): string
    {
        return Storage::disk('local')->path($path);
    }
}
