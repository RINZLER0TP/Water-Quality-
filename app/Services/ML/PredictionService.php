<?php

namespace App\Services\ML;

use App\Models\TrainingJob;
use Illuminate\Support\Facades\File;
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
        if (empty($job->model_path) || !File::exists($job->model_path)) {
            throw new \RuntimeException("El modelo Weka no se encontró en el servidor: {$job->model_path}");
        }

        // 2. Crear un archivo CSV temporal con los datos de entrada y la misma estructura que el dataset original
        // Weka requiere que las columnas sean idénticas. Añadimos la columna objetivo al final con valor '?'.
        $tempCsvPath = storage_path('app/weka/temp/predict_' . Str::uuid() . '.csv');
        $this->createTempCsv($tempCsvPath, $inputData, $job->target_column);

        try {
            // 3. Ejecutar el JavaWekaClient en modo predict
            $jarPath = config('weka.jar_path');
            
            $args = [
                '--mode=predict',
                '--model=' . $job->model_path,
                '--csv=' . $tempCsvPath,
                '--target=' . $job->target_column
            ];

            $output = $this->client->runJar($jarPath, $args, 30); // 30s timeout
            
            $result = json_decode($output, true);
            
            if (!$result || !isset($result['success']) || !$result['success']) {
                $error = $result['error'] ?? 'Error desconocido al procesar JSON';
                throw new \RuntimeException("Fallo en predicción Weka: $error");
            }

            return $result['prediction'];

        } finally {
            // Limpiar CSV temporal
            if (File::exists($tempCsvPath)) {
                File::delete($tempCsvPath);
            }
        }
    }

    private function createTempCsv(string $path, array $inputData, string $targetColumn): void
    {
        // Asegurar que el directorio exista
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Preparar cabeceras y valores
        // El orden de las llaves en inputData importa. Asumiremos que el frontend envía los campos en orden correcto.
        // O idealmente, sacaríamos las cabeceras del Dataset original, pero para simplificar enviamos inputData + target.
        $headers = array_keys($inputData);
        $headers[] = $targetColumn;

        $values = array_values($inputData);
        $values[] = '?'; // Weka usa '?' para missing/target en predicción

        $handle = fopen($path, 'w');
        fputcsv($handle, $headers);
        fputcsv($handle, $values);
        fclose($handle);
    }
}
