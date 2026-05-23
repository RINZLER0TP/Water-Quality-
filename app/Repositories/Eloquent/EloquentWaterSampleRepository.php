<?php

namespace App\Repositories\Eloquent;

use App\Models\WaterSample;
use App\Repositories\Contracts\WaterSampleRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class EloquentWaterSampleRepository implements WaterSampleRepositoryInterface
{
    public function find(int $id): ?WaterSample
    {
        return WaterSample::find($id);
    }

    public function create(array $data): WaterSample
    {
        return WaterSample::create($data);
    }

    public function paginate(int $perPage = 15): Paginator
    {
        return WaterSample::orderByDesc('created_at')->paginate($perPage);
    }
}
