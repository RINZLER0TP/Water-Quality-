<?php

namespace App\DTOs;

use Illuminate\Support\Carbon;

readonly class WaterSampleDTO
{
    public function __construct(
        public float $ph,
        public float $temperature,
        public string $status,
        public ?Carbon $collectedAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['ph'] ?? 0),
            (float) ($data['temperature'] ?? 0),
            (string) ($data['status'] ?? 'unknown'),
            isset($data['collected_at']) ? Carbon::parse($data['collected_at']) : null
        );
    }
}
