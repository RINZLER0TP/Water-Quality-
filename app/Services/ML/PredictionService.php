<?php

namespace App\Services\ML;

use App\Models\TrainingJob;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PredictionService
{
    private JavaWekaClient $client;

    public function __construct(JavaWekaClient $client)
    {
        $this->client = $client;
    }

    public function predict(TrainingJob $job, array $inputData): array
    {
        $this->extendExecutionTimeLimit();
        $job->loadMissing(['trainingConfiguration.dataset']);

        // 1. Validar que el job tenga un model_path
        if (empty($job->model_path) || !File::exists(Storage::disk('local')->path($job->model_path))) {
            throw new \RuntimeException("El modelo Weka no se encontró en el servidor: {$job->model_path}");
        }

        $trainingDataset = $job->trainingConfiguration?->dataset;

        if (! $trainingDataset) {
            throw new \RuntimeException('No se encontró el dataset de entrenamiento asociado al job de predicción.');
        }

        $modelPath = Storage::disk('local')->path($job->model_path);
        $trainingCsvPath = Storage::disk('local')->path($trainingDataset->file_path);
        $runtimeModelPath = $this->copyToRuntimeTemp($modelPath, 'weka_model_' . $job->id . '_' . Str::uuid() . '.model');
        $runtimeTrainingCsvPath = $this->copyToRuntimeTemp($trainingCsvPath, 'weka_training_dataset_' . $job->id . '_' . Str::uuid() . '.csv');
        // 2. Crear el CSV en memoria para enviarlo por stdin y evitar bloqueos de archivo en Windows.
        $trainingHeaders = $this->readCsvHeaders($runtimeTrainingCsvPath);
        $csvContent = $this->buildCsvContent($inputData, $job->target_column, $trainingHeaders);

        try {
            // 3. Ejecutar el JavaWekaClient en modo predict
            $jarPath = (string) config('weka.jar_path');

            Log::info('Prediction service starting Java run', [
                'training_job_id' => $job->id,
                'jar_path' => $jarPath,
                'model_path' => $runtimeModelPath,
                'training_csv_path' => $runtimeTrainingCsvPath,
            ]);
            
            $args = [
                '--mode=predict',
                '--model=' . $runtimeModelPath,
                '--training-csv=' . $runtimeTrainingCsvPath,
                '--csv=stdin',
                '--target=' . $job->target_column
            ];

            $output = $this->client->runJar($jarPath, $args, 30, $csvContent); // 30s timeout

            Log::info('Prediction service Java run finished', [
                'training_job_id' => $job->id,
            ]);
            
            $result = json_decode($this->extractJsonPayload($output), true);
            
            if (!$result || !isset($result['success']) || !$result['success']) {
                $error = $result['error'] ?? 'Error desconocido al procesar JSON';
                throw new \RuntimeException("Fallo en predicción Weka: $error");
            }

            return $result['prediction'];

        } finally {
            if (File::exists($runtimeModelPath)) {
                File::delete($runtimeModelPath);
            }

            if (File::exists($runtimeTrainingCsvPath)) {
                File::delete($runtimeTrainingCsvPath);
            }
        }
    }

    public function predictDataset(TrainingJob $job): array
    {
        $this->extendExecutionTimeLimit();
        $job->loadMissing(['trainingConfiguration.dataset']);

        if (empty($job->model_path) || !File::exists(Storage::disk('local')->path($job->model_path))) {
            throw new \RuntimeException("El modelo Weka no se encontró en el servidor: {$job->model_path}");
        }

        $trainingDataset = $job->trainingConfiguration?->dataset;

        if (! $trainingDataset) {
            throw new \RuntimeException('No se encontró el dataset de entrenamiento asociado al job de predicción.');
        }

        $modelPath = Storage::disk('local')->path($job->model_path);
        $trainingCsvPath = Storage::disk('local')->path($trainingDataset->file_path);

        $runtimeModelPath = $this->copyToRuntimeTemp($modelPath, 'weka_model_' . $job->id . '_' . Str::uuid() . '.model');
        $runtimeTrainingCsvPath = $this->copyToRuntimeTemp($trainingCsvPath, 'weka_training_dataset_' . $job->id . '_' . Str::uuid() . '.csv');

        try {
            $jarPath = (string) config('weka.jar_path');

            $args = [
                '--mode=batch-predict',
                '--model=' . $runtimeModelPath,
                '--training-csv=' . $runtimeTrainingCsvPath,
                '--csv=' . $runtimeTrainingCsvPath,
                '--target=' . $job->target_column,
            ];

            $output = $this->client->runJar($jarPath, $args, 60);
            $result = json_decode($this->extractJsonPayload($output), true);

            if (! $result || !isset($result['success']) || ! $result['success']) {
                $error = $result['error'] ?? 'Error desconocido al procesar JSON';
                throw new \RuntimeException("Fallo en predicción del dataset Weka: $error");
            }

            return $result;
        } finally {
            if (File::exists($runtimeModelPath)) {
                File::delete($runtimeModelPath);
            }

            if (File::exists($runtimeTrainingCsvPath)) {
                File::delete($runtimeTrainingCsvPath);
            }
        }
    }

    private function buildCsvContent(array $inputData, string $targetColumn, array $trainingHeaders): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new \RuntimeException('No se pudo crear el CSV temporal en memoria para la predicción.');
        }

        $headers = [];
        $values = [];

        foreach ($trainingHeaders as $header) {
            $normalizedHeader = trim((string) $header);
            if ($normalizedHeader === '') {
                continue;
            }

            $headers[] = $normalizedHeader;

            if ($normalizedHeader === $targetColumn) {
                $values[] = '?';
                continue;
            }

            if (array_key_exists($normalizedHeader, $inputData) && $inputData[$normalizedHeader] !== '') {
                $values[] = (string) $inputData[$normalizedHeader];
                continue;
            }

            $values[] = '?';
        }

        if (! in_array($targetColumn, $headers, true)) {
            $headers[] = $targetColumn;
            $values[] = '?';
        }

        fputcsv($handle, $headers);
        fputcsv($handle, $values);
        rewind($handle);

        $csvContent = stream_get_contents($handle);
        fclose($handle);

        if (! is_string($csvContent) || $csvContent === '') {
            throw new \RuntimeException('No se pudo generar el contenido CSV de la predicción.');
        }

        return $csvContent;
    }

    private function readCsvHeaders(string $csvPath): array
    {
        $handle = fopen($csvPath, 'rb');

        if ($handle === false) {
            throw new \RuntimeException('No se pudo leer el CSV de entrenamiento para obtener los encabezados.');
        }

        $headers = fgetcsv($handle);
        fclose($handle);

        if (! is_array($headers) || $headers === []) {
            throw new \RuntimeException('No se pudieron leer los encabezados del CSV de entrenamiento.');
        }

        return array_map(static fn ($header): string => trim((string) $header), $headers);
    }

    private function extractJsonPayload(string $output): string
    {
        $trimmedOutput = trim($output);
        $firstBrace = strpos($trimmedOutput, '{');
        $lastBrace = strrpos($trimmedOutput, '}');

        if ($firstBrace === false || $lastBrace === false || $lastBrace < $firstBrace) {
            throw new \RuntimeException('La salida del engine Java no contiene un JSON válido.');
        }

        return substr($trimmedOutput, $firstBrace, $lastBrace - $firstBrace + 1);
    }

    private function runtimeTempPath(string $fileName): string
    {
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'water-quality-weka';

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return $directory . DIRECTORY_SEPARATOR . $fileName;
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

    private function copyToRuntimeTemp(string $sourcePath, string $fileName): string
    {
        $destinationPath = $this->runtimeTempPath($fileName);

        if (!@copy($sourcePath, $destinationPath)) {
            throw new \RuntimeException("No se pudo preparar el modelo temporal para la predicción: {$sourcePath}");
        }

        return $destinationPath;
    }
}
