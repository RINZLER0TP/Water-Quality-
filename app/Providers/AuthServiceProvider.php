<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Dataset;
use App\Models\WaterSample;
use App\Models\TrainingConfiguration;
use App\Policies\DatasetPolicy;
use App\Policies\TrainingConfigurationPolicy;
use App\Policies\WaterSamplePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Dataset::class => DatasetPolicy::class,
        TrainingConfiguration::class => TrainingConfigurationPolicy::class,
        WaterSample::class => WaterSamplePolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
