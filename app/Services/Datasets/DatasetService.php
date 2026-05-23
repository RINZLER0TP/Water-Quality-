<?php

namespace App\Services\Datasets;

use App\DTOs\DatasetDTO;
use App\Enums\DatasetStatus;
use App\Models\Dataset;
use App\Models\User;
use App\Repositories\Contracts\DatasetRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DatasetService
{
    public function __construct(private DatasetRepositoryInterface $repository)
    {
    }

    public function paginate(string $search = '', int $perPage = 10)
    {
        return $this->repository->paginate($search, $perPage);
    }

    public function find(int $id): ?Dataset
    {
        return $this->repository->find($id);
    }

    public function store(User $user, ?string $name, UploadedFile $file): Dataset
    {
        $analysis = $this->analyzeCsv($file);

        $storageDirectory = 'weka/datasets';
        Storage::disk('local')->makeDirectory($storageDirectory);

        $safeBaseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $uniqueName = now()->format('YmdHis').'-'.Str::uuid()->toString().'-'.$safeBaseName.'.csv';
        $storedPath = $file->storeAs($storageDirectory, $uniqueName, 'local');

        $dataset = new DatasetDTO(
            name: $name ?: Str::headline(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            originalName: $file->getClientOriginalName(),
            filePath: $storedPath,
            fileSize: (int) $file->getSize(),
            rowsCount: $analysis['rows_count'],
            columnsCount: $analysis['columns_count'],
            status: DatasetStatus::READY,
            userId: $user->id,
            metadata: [
                'delimiter' => $analysis['delimiter'],
                'headers' => $analysis['headers'],
                'validated_at' => now()->toDateTimeString(),
            ],
        );

        return $this->repository->create($dataset);
    }

    public function delete(Dataset $dataset): bool
    {
        if (Storage::disk('local')->exists($dataset->file_path)) {
            Storage::disk('local')->delete($dataset->file_path);
        }

        return $this->repository->delete($dataset);
    }

    public function download(Dataset $dataset)
    {
        if (! Storage::disk('local')->exists($dataset->file_path)) {
            throw new RuntimeException('El archivo del dataset no existe.');
        }

        return Storage::disk('local')->download($dataset->file_path, $dataset->original_name);
    }

    /**
     * @return array{rows_count:int, columns_count:int, headers:array<int, string>, delimiter:string}
     */
    private function analyzeCsv(UploadedFile $file): array
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

        if (! is_array($headers) || count($headers) === 0) {
            fclose($handle);
            throw new RuntimeException('El CSV no contiene encabezados válidos.');
        }

        $headers = array_map(static fn ($header): string => trim((string) $header), $headers);
        $headers = array_values(array_filter($headers, static fn (string $header): bool => $header !== ''));

        if ($headers === []) {
            fclose($handle);
            throw new RuntimeException('El CSV no contiene columnas válidas.');
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
