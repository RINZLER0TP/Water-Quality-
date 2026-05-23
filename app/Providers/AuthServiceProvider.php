<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\WaterSample;
use App\Policies\WaterSamplePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
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
