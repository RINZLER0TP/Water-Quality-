<?php

namespace App\Repositories\Eloquent;

use App\DTOs\DatasetDTO;
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
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
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
            'user_id' => $dto->userId,
            'metadata' => $dto->metadata,
        ]);
    }

    public function delete(Dataset $dataset): bool
    {
        return (bool) $dataset->delete();
    }
}
