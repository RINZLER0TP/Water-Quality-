<?php

namespace App\Actions;

use App\DTOs\DatasetDTO;
use App\Enums\DatasetStatus;
use App\Models\Dataset;
use App\Models\User;
use App\Services\Datasets\CsvDatasetInspector;
use App\Services\Datasets\DatasetService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StoreDatasetAction
{
    public function __construct(
        private DatasetService $service,
        private CsvDatasetInspector $inspector,
    ) {
    }

    public function __invoke(User $user, ?string $name, UploadedFile $file): Dataset
    {
        $analysis = $this->inspector->inspect($file);

        $storageDirectory = 'weka/datasets';
        Storage::disk('local')->makeDirectory($storageDirectory);

        $safeBaseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $uniqueName = now()->format('YmdHis').'-'.Str::uuid()->toString().'-'.$safeBaseName.'.csv';
        $storedPath = $file->storeAs($storageDirectory, $uniqueName, 'local');

        if ($storedPath === false) {
            throw new RuntimeException('No se pudo almacenar el dataset.');
        }

        $dto = new DatasetDTO(
            name: $name ?: Str::headline(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            originalName: $file->getClientOriginalName(),
            filePath: $storedPath,
            fileSize: (int) $file->getSize(),
            rowsCount: $analysis['rows_count'],
            columnsCount: $analysis['columns_count'],
            status: DatasetStatus::VALIDATED,
            uploadedBy: $user->id,
            metadata: [
                'delimiter' => $analysis['delimiter'],
                'headers' => $analysis['headers'],
                'validated_at' => now()->toDateTimeString(),
            ],
        );

        return $this->service->create($dto);
    }
}