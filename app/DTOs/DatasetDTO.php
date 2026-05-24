<?php

namespace App\DTOs;

use App\Enums\DatasetStatus;

readonly class DatasetDTO
{
    public function __construct(
        public string $name,
        public string $originalName,
        public string $filePath,
        public int $fileSize,
        public int $rowsCount,
        public int $columnsCount,
        public DatasetStatus $status,
        public int $uploadedBy,
        public array $metadata = []
    ) {
    }
}
