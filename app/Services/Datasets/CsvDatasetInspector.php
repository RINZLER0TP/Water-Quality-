<?php

namespace App\Services\Datasets;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class CsvDatasetInspector
{
    /**
     * @return array{rows_count:int, columns_count:int, headers:array<int, string>, delimiter:string}
     */
    public function inspect(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');

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
        $headers = array_values(array_filter($headers, static fn (string $header): bool => $header !== ''));

        if ($headers === []) {
            fclose($handle);
            throw new RuntimeException('El CSV no contiene columnas válidas.');
        }

        if (count(array_unique($headers)) !== count($headers)) {
            fclose($handle);
            throw new RuntimeException('El CSV contiene encabezados duplicados.');
        }

        $columnsCount = count($headers);
        $rowsCount = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            if (count($row) !== $columnsCount) {
                fclose($handle);
                throw new RuntimeException('Las filas del CSV no tienen la misma cantidad de columnas que el encabezado.');
            }

            $rowsCount++;
        }

        fclose($handle);

        if ($rowsCount === 0) {
            throw new RuntimeException('El CSV no contiene filas de datos.');
        }

        return [
            'rows_count' => $rowsCount,
            'columns_count' => $columnsCount,
            'headers' => $headers,
            'delimiter' => $delimiter,
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