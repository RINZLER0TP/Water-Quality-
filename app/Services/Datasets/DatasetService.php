<?php

namespace App\Services\Datasets;

use App\DTOs\DatasetDTO;
use App\Models\Dataset;
use App\Repositories\Contracts\DatasetRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatasetService
{
    private const DOWNLOAD_DELIMITER = ';';

    public function __construct(private DatasetRepositoryInterface $repository)
    {
    }

    public function paginate(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $perPage);
    }

    public function summary(string $search = ''): array
    {
        return $this->repository->summary($search);
    }

    public function find(int $id): ?Dataset
    {
        return $this->repository->find($id);
    }

    public function create(DatasetDTO $dto): Dataset
    {
        return $this->repository->create($dto);
    }

    public function delete(Dataset $dataset): bool
    {
        $this->deleteTrainingArtifacts($dataset);

        if (Storage::disk('local')->exists($dataset->file_path)) {
            Storage::disk('local')->delete($dataset->file_path);
        }

        return $this->repository->delete($dataset);
    }

    public function download(Dataset $dataset): BinaryFileResponse
    {
        if (! Storage::disk('local')->exists($dataset->file_path)) {
            throw new RuntimeException('El archivo del dataset no existe.');
        }

        $sortedFilePath = $this->buildSortedCsvFile($dataset->file_path);

        return response()->download(
            $sortedFilePath,
            $dataset->original_name
        )->deleteFileAfterSend(true);
    }

    private function buildSortedCsvFile(string $filePath): string
    {
        $absolutePath = Storage::disk('local')->path($filePath);
        $sourceHandle = fopen($absolutePath, 'rb');

        if ($sourceHandle === false) {
            throw new RuntimeException('No se pudo leer el archivo del dataset.');
        }

        $firstLine = '';
        while (($line = fgets($sourceHandle)) !== false) {
            if (trim($line) !== '') {
                $firstLine = $line;
                break;
            }
        }

        if ($firstLine === '') {
            fclose($sourceHandle);
            throw new RuntimeException('El archivo del dataset está vacío.');
        }

        $delimiter = $this->detectDelimiter($firstLine);
        rewind($sourceHandle);

        $headers = fgetcsv($sourceHandle, 0, $delimiter);

        if (! is_array($headers) || $headers === []) {
            fclose($sourceHandle);
            throw new RuntimeException('No se pudieron leer los encabezados del dataset.');
        }

        $rows = [];

        while (($row = fgetcsv($sourceHandle, 0, $delimiter)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $rows[] = $row;
        }

        fclose($sourceHandle);

        usort($rows, function (array $left, array $right): int {
            $maxColumns = max(count($left), count($right));

            for ($index = 0; $index < $maxColumns; $index++) {
                $leftValue = trim((string) ($left[$index] ?? ''));
                $rightValue = trim((string) ($right[$index] ?? ''));

                $comparison = strnatcasecmp($leftValue, $rightValue);

                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        });

        $sortedFilePath = tempnam(sys_get_temp_dir(), 'dataset_sorted_');

        if ($sortedFilePath === false) {
            throw new RuntimeException('No se pudo crear un archivo temporal para la descarga.');
        }

        $outputHandle = fopen($sortedFilePath, 'wb');

        if ($outputHandle === false) {
            throw new RuntimeException('No se pudo preparar la descarga del dataset.');
        }

        fwrite($outputHandle, "\xEF\xBB\xBF");
        fputcsv($outputHandle, $headers, self::DOWNLOAD_DELIMITER);

        foreach ($rows as $row) {
            fputcsv($outputHandle, $row, self::DOWNLOAD_DELIMITER);
        }

        fclose($outputHandle);

        return $sortedFilePath;
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

    private function deleteTrainingArtifacts(Dataset $dataset): void
    {
        $dataset->loadMissing('trainingConfigurations.trainingJobs');

        /** @var Collection<int, \App\Models\TrainingConfiguration> $configurations */
        $configurations = $dataset->trainingConfigurations;

        foreach ($configurations as $configuration) {
            foreach ($configuration->trainingJobs as $job) {
                if (! empty($job->model_path) && Storage::disk('local')->exists($job->model_path)) {
                    Storage::disk('local')->delete($job->model_path);
                }

                if (! empty($job->log_path) && Storage::disk('local')->exists($job->log_path)) {
                    Storage::disk('local')->delete($job->log_path);
                }
            }

            $configuration->forceDelete();
        }
    }
}
