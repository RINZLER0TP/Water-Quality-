<?php

namespace App\Repositories\Eloquent;

use App\DTOs\DatasetDTO;
use App\Enums\DatasetStatus;
use App\Models\Dataset;
use App\Repositories\Contracts\DatasetRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentDatasetRepository implements DatasetRepositoryInterface
{
    public function find(int $id): ?Dataset
    {
        return Dataset::with('uploader')->find($id);
    }

    public function paginate(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return $this->baseQuery($search)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function summary(string $search = ''): array
    {
        $query = $this->baseQuery($search);

        return [
            'total_datasets' => (clone $query)->count(),
            'total_rows' => (clone $query)->sum('rows_count'),
            'average_columns' => (float) ((clone $query)->avg('columns_count') ?? 0),
            'total_size' => (clone $query)->sum('file_size'),
            'validated' => (clone $query)->where('status', DatasetStatus::VALIDATED->value)->count(),
            'processing' => (clone $query)->where('status', DatasetStatus::PROCESSING->value)->count(),
            'invalid' => (clone $query)->where('status', DatasetStatus::INVALID->value)->count(),
            'latest_uploaded_at' => (clone $query)->max('created_at'),
        ];
    }

    public function create(DatasetDTO $dto): Dataset
    {
        return Dataset::create([
            'name' => $dto->name,
            'original_name' => $dto->originalName,
            'file_path' => $dto->filePath,
            'file_size' => $dto->fileSize,
            'rows_count' => $dto->rowsCount,
            'columns_count' => $dto->columnsCount,
            'status' => $dto->status->value,
            'uploaded_by' => $dto->uploadedBy,
            'metadata' => $dto->metadata,
        ]);
    }

    public function delete(Dataset $dataset): bool
    {
        return (bool) $dataset->delete();
    }

    private function baseQuery(string $search = '')
    {
        return Dataset::query()
            ->with('uploader')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('uploader', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });
    }
}
