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
        $this->extendExecutionTimeLimit();

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
        $tempDirectory = storage_path('app/weka/tmp');

        if (! is_dir($tempDirectory) && ! mkdir($tempDirectory, 0775, true) && ! is_dir($tempDirectory)) {
            throw new RuntimeException('No se pudo crear el directorio temporal de entrenamiento.');
        }

        $tempModelPath = $tempDirectory . DIRECTORY_SEPARATOR . 'weka_model_' . $job->id . '_' . uniqid() . '.model';
        $tempLogPath = $tempDirectory . DIRECTORY_SEPARATOR . 'weka_log_' . $job->id . '_' . uniqid() . '.log';
        $csvContent = Storage::disk('local')->get($dataset->file_path);
        $algorithmArgs = $this->buildAlgorithmArguments($job->algorithm->value, $job->parameters ?? []);
        $javaOptions = $this->javaOptionsForAlgorithm($job->algorithm->value);

        $args = [
            '--csv=stdin',
            '--target='.$job->target_column,
            '--algorithm='.$job->algorithm->value,
            '--model='.$tempModelPath,
            '--log='.$tempLogPath,
            '--folds='.$folds,
            '--seed='.$seed,
            '--job-id='.$job->id,
            ...$algorithmArgs,
        ];

        try {
            $output = $this->javaWekaClient->runJar((string) config('weka.jar_path'), $args, $timeout, $csvContent, $javaOptions);
            $cleanOutput = $this->sanitizeText($output);
            
            // Si tuvo éxito, copiar de vuelta los archivos generados
            if (file_exists($tempModelPath)) {
                copy($tempModelPath, $absoluteModelPath);
            }
            if (file_exists($tempLogPath)) {
                copy($tempLogPath, $absoluteLogPath);
            }

            $payload = $this->decodePayload($output);
            $metrics = $payload['metrics'] ?? [];
            $confusionMatrix = $payload['confusion_matrix'] ?? [];

            // Verificamos usando la ruta absoluta que nosotros le pasamos a Java,
            // ya que en Windows Java puede devolver una ruta con mezcla de
            // backslashes y forward-slashes que hace fallar file_exists().
            if (! file_exists($absoluteModelPath)) {
                throw new RuntimeException('El archivo .model no fue generado por el engine Java.');
            }

            return $this->repository->update($job, [
                'status' => TrainingJobStatus::COMPLETED->value,
                'metrics' => $metrics,
                'confusion_matrix' => $confusionMatrix,
                'model_path' => $modelPath,
                'log_path' => $logPath,
                'execution_log' => $cleanOutput,
                'completed_at' => now(),
                'training_time_ms' => (int) ($metrics['training_time_ms'] ?? 0),
            ]);
        } catch (\Throwable $throwable) {
            $cleanMessage = $this->sanitizeText($throwable->getMessage());

            Log::error('Training job failed', [
                'training_job_id' => $job->id,
                'message' => $cleanMessage,
            ]);

            return $this->repository->update($job, [
                'status' => TrainingJobStatus::FAILED->value,
                'error_message' => $cleanMessage,
                'execution_log' => trim($this->sanitizeText((string) ($job->execution_log ?? '')).PHP_EOL.$cleanMessage),
                'completed_at' => now(),
            ]);
        } finally {
            @unlink($tempModelPath);
            @unlink($tempLogPath);
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

    private function buildAlgorithmArguments(string $algorithm, array $parameters): array
    {
        return match ($algorithm) {
            'oner' => array_values(array_filter([
                isset($parameters['bucket_size']) ? '--bucket-size='.(int) $parameters['bucket_size'] : null,
            ])),
            'naive_bayes' => array_values(array_filter([
                array_key_exists('use_kernel_estimator', $parameters)
                    ? '--use-kernel-estimator=' . $this->booleanToFlag($parameters['use_kernel_estimator'])
                    : null,
                array_key_exists('use_supervised_discretization', $parameters)
                    ? '--use-supervised-discretization=' . $this->booleanToFlag($parameters['use_supervised_discretization'])
                    : null,
            ])),
            'logistic' => array_values(array_filter([
                isset($parameters['ridge']) ? '--ridge='.(string) $parameters['ridge'] : null,
                isset($parameters['max_iterations']) ? '--max-iterations='.(int) $parameters['max_iterations'] : null,
            ])),
            default => [],
        };
    }

    private function booleanToFlag(mixed $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOL) ? 'true' : 'false';
    }

    private function sanitizeText(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (function_exists('mb_check_encoding') && mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        if (function_exists('mb_convert_encoding')) {
            $converted = mb_convert_encoding($value, 'UTF-8', ['UTF-8', 'Windows-1252', 'ISO-8859-1']);

            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        if (function_exists('iconv')) {
            $converted = iconv('Windows-1252', 'UTF-8//IGNORE', $value);

            if ($converted !== false && $converted !== '') {
                return $converted;
            }
        }

        return preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? $value;
    }

    private function extendExecutionTimeLimit(): void
    {
        $timeoutSeconds = (int) config('weka.timeout_seconds', 1800);
        $executionLimit = max($timeoutSeconds + 120, 600);

        if (function_exists('set_time_limit')) {
            @set_time_limit($executionLimit);
        }

        @ini_set('max_execution_time', (string) $executionLimit);
    }

    private function javaOptionsForAlgorithm(string $algorithm): array
    {
        return match ($algorithm) {
            'logistic' => ['-Xms16m', '-Xmx640m', '-XX:+UseSerialGC'],
            default => [],
        };
    }
}
