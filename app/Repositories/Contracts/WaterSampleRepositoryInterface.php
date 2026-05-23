<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\Paginator;
use App\Models\WaterSample;

interface WaterSampleRepositoryInterface
{
    public function find(int $id): ?WaterSample;

    public function create(array $data): WaterSample;

    public function paginate(int $perPage = 15): Paginator;
}
