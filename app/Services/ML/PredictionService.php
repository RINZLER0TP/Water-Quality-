<?php

namespace App\Services\ML;

use App\Models\TrainingJob;
use Illuminate\Support\Facades\File;
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
        // 1. Validar que el job tenga un model_path
        if (empty($job->model_path) || !File::exists(Storage::disk('local')->path($job->model_path))) {
            throw new \RuntimeException("El modelo Weka no se encontró en el servidor: {$job->model_path}");
        }

        $modelPath = Storage::disk('local')->path($job->model_path);
        $runtimeModelPath = $this->copyToRuntimeTemp($modelPath, 'weka_model_' . $job->id . '_' . Str::uuid() . '.model');
        // 2. Crear el CSV en memoria para enviarlo por stdin y evitar bloqueos de archivo en Windows.
        $csvContent = $this->buildCsvContent($inputData, $job->target_column);

        try {
            // 3. Ejecutar el JavaWekaClient en modo predict
            $jarPath = (string) config('weka.jar_path');
            
            $args = [
                '--mode=predict',
                '--model=' . $runtimeModelPath,
                '--csv=stdin',
                '--target=' . $job->target_column
            ];

            $output = $this->client->runJar($jarPath, $args, 30, $csvContent); // 30s timeout
            
            $result = json_decode($output, true);
            
            if (!$result || !isset($result['success']) || !$result['success']) {
                $error = $result['error'] ?? 'Error desconocido al procesar JSON';
                throw new \RuntimeException("Fallo en predicción Weka: $error");
            }

            return $result['prediction'];

        } finally {
            if (File::exists($runtimeModelPath)) {
                File::delete($runtimeModelPath);
            }
        }
    }

    private function buildCsvContent(array $inputData, string $targetColumn): string
    {
        // Preparar cabeceras y valores
        // El orden de las llaves en inputData importa. Asumiremos que el frontend envía los campos en orden correcto.
        // O idealmente, sacaríamos las cabeceras del Dataset original, pero para simplificar enviamos inputData + target.
        $headers = array_keys($inputData);
        $headers[] = $targetColumn;

        $values = array_values($inputData);
        $values[] = '?'; // Weka usa '?' para missing/target en predicción

        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new \RuntimeException('No se pudo crear el CSV temporal en memoria para la predicción.');
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

    private function runtimeTempPath(string $fileName): string
    {
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'water-quality-weka';

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return $directory . DIRECTORY_SEPARATOR . $fileName;
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
