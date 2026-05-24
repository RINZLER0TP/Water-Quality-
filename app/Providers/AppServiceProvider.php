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
        $this->app->bind(\App\Repositories\Interfaces\PredictionRepositoryInterface::class, \App\Repositories\EloquentPredictionRepository::class);

        $javaPath = (string) config('weka.java_path', 'java');
        if ((empty($javaPath) || $javaPath === 'java' || str_contains($javaPath, 'javapath')) && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Bypass the Oracle javapath wrapper which crashes with STATUS_STACK_BUFFER_OVERRUN
            // Find the real JDK executable, preferring newer versions (like jdk-21 or jdk-17)
            $realJavaPaths = glob('C:\Program Files\Java\jdk*\bin\java.exe');
            if (!empty($realJavaPaths)) {
                usort($realJavaPaths, function($a, $b) {
                    preg_match('/jdk-?(\d+)/', $a, $matchA);
                    preg_match('/jdk-?(\d+)/', $b, $matchB);
                    $verA = isset($matchA[1]) ? (int)$matchA[1] : 0;
                    $verB = isset($matchB[1]) ? (int)$matchB[1] : 0;
                    if ($verA == $verB) return strcmp($b, $a);
                    return $verB <=> $verA;
                });
                $javaPath = $realJavaPaths[0];
            } else {
                $where = trim(exec('where java'));
                if ($where) {
                    $javaPath = explode("\n", str_replace("\r", "", $where))[0];
                }
            }
        }

        $javaOptions = (array) config('weka.java_options', ['-Xms128m', '-Xmx512m']);

        $this->app->singleton(JavaWekaClient::class, fn () => new JavaWekaClient($javaPath, $javaOptions));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
