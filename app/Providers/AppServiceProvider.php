<?php

namespace App\Providers;

use App\Repositories\Contracts\DatasetRepositoryInterface;
use App\Repositories\Contracts\TrainingJobRepositoryInterface;
use App\Repositories\Contracts\TrainingConfigurationRepositoryInterface;
use App\Repositories\Eloquent\EloquentDatasetRepository;
use App\Repositories\Eloquent\EloquentTrainingJobRepository;
use App\Repositories\Eloquent\EloquentTrainingConfigurationRepository;
use App\Services\ML\JavaWekaClient;
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
        $this->app->bind(TrainingConfigurationRepositoryInterface::class, EloquentTrainingConfigurationRepository::class);
        $this->app->bind(TrainingJobRepositoryInterface::class, EloquentTrainingJobRepository::class);
        $this->app->bind(WaterSampleRepositoryInterface::class, EloquentWaterSampleRepository::class);

        $this->app->singleton(JavaWekaClient::class, fn () => new JavaWekaClient((string) config('weka.java_path', 'java')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
