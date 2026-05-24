<?php

namespace App\Services\Datasets;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class CsvDatasetInspector
{
    public function inspect(UploadedFile $file): array
    {
        return $this->analyzeFile($file->getRealPath() ?: '', 0)['summary'];
    }

    public function analyzeFile(string $absolutePath, int $previewLimit = 10): array
    {
        if ($absolutePath === '' || ! is_file($absolutePath)) {
            throw new RuntimeException('No se pudo leer el archivo CSV.');
        }

        $handle = fopen($absolutePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException('No se pudo leer el archivo CSV.');
        }

        $firstLine = '';
        while (($line = fgets($handle)) !== false) {
            if (trim($line) !== '') {
                $firstLine = $line;
                break;
            }
        }

        if ($firstLine === '') {
            fclose($handle);

            throw new RuntimeException('El CSV está vacío.');
        }

        $delimiter = $this->detectDelimiter($firstLine);
        rewind($handle);

        $headers = fgetcsv($handle, 0, $delimiter);

        if (! is_array($headers) || $headers === []) {
            fclose($handle);

            throw new RuntimeException('El CSV no contiene encabezados válidos.');
        }

        $headers = array_map(static fn ($header): string => trim((string) $header), $headers);

        if (in_array('', $headers, true)) {
            fclose($handle);

            throw new RuntimeException('El CSV contiene encabezados vacíos.');
        }

        if (count(array_unique($headers)) !== count($headers)) {
            fclose($handle);

            throw new RuntimeException('El CSV contiene encabezados duplicados.');
        }

        $columnsCount = count($headers);
        $rowsCount = 0;
        $previewRows = [];
        $columnStats = [];

        foreach ($headers as $index => $header) {
            $columnStats[$index] = [
                'index' => $index,
                'name' => $header,
                'missing_values' => 0,
                'non_empty_values' => 0,
                'numeric_values' => 0,
                'boolean_values' => 0,
                'date_values' => 0,
                'distinct_values' => [],
                'sample_values' => [],
            ];
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            if (count($row) !== $columnsCount) {
                fclose($handle);

                throw new RuntimeException('Las filas del CSV no tienen la misma cantidad de columnas que el encabezado.');
            }

            $previewRow = [];

            foreach ($headers as $index => $header) {
                $value = trim((string) ($row[$index] ?? ''));
                $previewRow[$header] = $value;

                if ($value === '') {
                    $columnStats[$index]['missing_values']++;

                    continue;
                }

                $columnStats[$index]['non_empty_values']++;
                $columnStats[$index]['distinct_values'][$value] = true;

                if (count($columnStats[$index]['sample_values']) < 5 && ! in_array($value, $columnStats[$index]['sample_values'], true)) {
                    $columnStats[$index]['sample_values'][] = $value;
                }

                if ($this->isNumericValue($value)) {
                    $columnStats[$index]['numeric_values']++;
                }

                if ($this->isBooleanValue($value)) {
                    $columnStats[$index]['boolean_values']++;
                }

                if ($this->isDateValue($value)) {
                    $columnStats[$index]['date_values']++;
                }
            }

            if (count($previewRows) < max($previewLimit, 0)) {
                $previewRows[] = $previewRow;
            }

            $rowsCount++;
        }

        fclose($handle);

        if ($rowsCount === 0) {
            throw new RuntimeException('El CSV no contiene filas de datos.');
        }

        $columns = [];
        $statistics = [
            'rows_count' => $rowsCount,
            'columns_count' => $columnsCount,
            'missing_values' => 0,
            'numeric_columns' => 0,
            'boolean_columns' => 0,
            'categorical_columns' => 0,
            'date_columns' => 0,
            'text_columns' => 0,
            'completeness_percentage' => 0.0,
        ];

        foreach ($columnStats as $stat) {
            $distinctCount = count($stat['distinct_values']);
            $missingPercentage = $rowsCount > 0 ? round(($stat['missing_values'] / $rowsCount) * 100, 2) : 0.0;
            $nonEmptyValues = $stat['non_empty_values'];

            $type = $this->detectColumnType($stat, $rowsCount);
            $isNumeric = $type === 'numeric';

            $statistics['missing_values'] += $stat['missing_values'];

            if ($type === 'numeric') {
                $statistics['numeric_columns']++;
            } elseif ($type === 'boolean') {
                $statistics['boolean_columns']++;
            } elseif ($type === 'date') {
                $statistics['date_columns']++;
            } elseif ($type === 'categorical') {
                $statistics['categorical_columns']++;
            } else {
                $statistics['text_columns']++;
            }

            $columns[] = [
                'index' => $stat['index'],
                'name' => $stat['name'],
                'type' => $type,
                'is_numeric' => $isNumeric,
                'is_categorical' => in_array($type, ['categorical', 'boolean'], true),
                'missing_values' => $stat['missing_values'],
                'missing_percentage' => $missingPercentage,
                'non_empty_values' => $nonEmptyValues,
                'distinct_values_count' => $distinctCount,
                'sample_values' => array_values($stat['sample_values']),
            ];
        }

        $statistics['completeness_percentage'] = $rowsCount > 0 && $columnsCount > 0
            ? round((1 - ($statistics['missing_values'] / ($rowsCount * $columnsCount))) * 100, 2)
            : 0.0;

        $suggestedTarget = $this->suggestTargetColumn($columns, $rowsCount);
        $compatibility = $this->validateWekaCompatibility($columns, $statistics, $suggestedTarget);

        return [
            'delimiter' => $delimiter,
            'headers' => $headers,
            'columns' => $columns,
            'preview_rows' => $previewRows,
            'preview_rows_count' => count($previewRows),
            'statistics' => $statistics,
            'suggested_target' => $suggestedTarget,
            'compatibility' => $compatibility,
            'summary' => [
                'rows_count' => $rowsCount,
                'columns_count' => $columnsCount,
                'headers' => $headers,
                'delimiter' => $delimiter,
            ],
        ];
    }

    private function detectDelimiter(string $line): string
    {
        $candidates = [
            ',' => substr_count($line, ','),
            ';' => substr_count($line, ';'),
            "\t" => substr_count($line, "\t"),
        ];

        arsort($candidates);
        $delimiter = array_key_first($candidates);

        return is_string($delimiter) ? $delimiter : ',';
    }

    private function detectColumnType(array $stat, int $rowsCount): string
    {
        if ($stat['non_empty_values'] === 0) {
            return 'empty';
        }

        if ($stat['numeric_values'] === $stat['non_empty_values']) {
            return 'numeric';
        }

        if ($stat['boolean_values'] === $stat['non_empty_values']) {
            return 'boolean';
        }

        if ($stat['date_values'] === $stat['non_empty_values']) {
            return 'date';
        }

        $distinctCount = count($stat['distinct_values']);
        $distinctRatio = $stat['non_empty_values'] > 0 ? $distinctCount / $stat['non_empty_values'] : 1;

        if ($distinctCount <= 20 || $distinctRatio <= 0.5 || $rowsCount <= 25) {
            return 'categorical';
        }

        return 'text';
    }

    private function suggestTargetColumn(array $columns, int $rowsCount): ?array
    {
        $priorityTerms = ['target', 'class', 'label', 'status', 'result', 'outcome', 'quality', 'potability', 'potable'];
        $best = null;

        foreach ($columns as $column) {
            if (! in_array($column['type'], ['categorical', 'boolean'], true)) {
                continue;
            }

            $score = 0;
            $normalizedName = strtolower($column['name']);

            foreach ($priorityTerms as $term) {
                if (str_contains($normalizedName, $term)) {
                    $score += 100;
                }
            }

            $distinctCount = $column['distinct_values_count'];

            if ($distinctCount >= 2 && $distinctCount <= min(12, max(2, intdiv(max($rowsCount, 2), 2)))) {
                $score += 30;
            }

            if ($column['missing_percentage'] <= 20) {
                $score += 10;
            }

            if ($best === null || $score > $best['score']) {
                $best = [
                    'name' => $column['name'],
                    'type' => $column['type'],
                    'score' => $score,
                    'reason' => str_contains($normalizedName, 'status') || str_contains($normalizedName, 'class') || str_contains($normalizedName, 'label')
                        ? 'Coincide con un nombre habitual de variable objetivo.'
                        : 'Es una columna categórica con baja cardinalidad y poca ausencia de datos.',
                ];
            }
        }

        return $best ? array_diff_key($best, ['score' => true]) : null;
    }

    private function validateWekaCompatibility(array $columns, array $statistics, ?array $suggestedTarget): array
    {
        $issues = [];
        $warnings = [];

        if ($statistics['rows_count'] < 2) {
            $issues[] = 'El dataset no tiene suficientes filas para entrenar.';
        }

        if ($statistics['columns_count'] < 2) {
            $issues[] = 'El dataset debe tener al menos dos columnas.';
        }

        if ($suggestedTarget === null) {
            $issues[] = 'No se detectó una columna objetivo nominal compatible con clasificación.';
        }

        if ($statistics['missing_values'] > 0) {
            $warnings[] = 'El dataset contiene valores faltantes; Weka puede requerir limpieza o filtros adicionales.';
        }

        if ($statistics['numeric_columns'] === 0) {
            $warnings[] = 'No se detectaron columnas numéricas; algunos algoritmos podrían aprovechar variables continuas.';
        }

        foreach ($columns as $column) {
            if (($column['missing_percentage'] ?? 0) >= 100) {
                $issues[] = 'Existe una columna completamente vacía.';
            }
        }

        return [
            'is_compatible' => $issues === [],
            'issues' => array_values(array_unique($issues)),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    private function isNumericValue(string $value): bool
    {
        return (bool) preg_match('/^-?(?:\d+\.?\d*|\d*\.\d+)$/', $value);
    }

    private function isBooleanValue(string $value): bool
    {
        $normalized = strtolower($value);

        return in_array($normalized, ['true', 'false', '1', '0', 'yes', 'no', 'si', 'sí'], true);
    }

    private function isDateValue(string $value): bool
    {
        if ($this->isNumericValue($value)) {
            return false;
        }

        return strtotime($value) !== false && preg_match('/[-\/]/', $value) === 1;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}