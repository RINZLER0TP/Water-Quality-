<?php

namespace App\Providers;

use App\Repositories\Contracts\DatasetRepositoryInterface;
use App\Repositories\Eloquent\EloquentDatasetRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\WaterSampleRepositoryInterface;
use App\Repositories\Eloquent\EloquentWaterSampleRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DatasetRepositoryInterface::class, EloquentDatasetRepository::class);
        $this->app->bind(WaterSampleRepositoryInterface::class, EloquentWaterSampleRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
