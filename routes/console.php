<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\TrainingJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('training-jobs:purge-orphans {--dry-run : Only show the orphan jobs without deleting them}', function () {
    $jobs = TrainingJob::query()
        ->with(['trainingConfiguration' => fn ($query) => $query->withTrashed()])
        ->get()
        ->filter(function (TrainingJob $job): bool {
            return $job->trainingConfiguration === null || $job->trainingConfiguration->trashed();
        });

    if ($jobs->isEmpty()) {
        $this->info('No se encontraron entrenamientos huérfanos.');

        return self::SUCCESS;
    }

    $this->info(sprintf('Se encontraron %d entrenamientos huérfanos.', $jobs->count()));

    if ($this->option('dry-run')) {
        $jobs->each(function (TrainingJob $job): void {
            $this->line(sprintf('#%d | dataset_id=%s | config_id=%s | status=%s', $job->id, $job->dataset_id, $job->training_configuration_id, $job->status->value));
        });

        $this->warn('Dry-run activado: no se eliminó nada.');

        return self::SUCCESS;
    }

    $deleted = 0;

    $jobs->each(function (TrainingJob $job) use (&$deleted): void {
        if (! empty($job->model_path) && Storage::disk('local')->exists($job->model_path)) {
            Storage::disk('local')->delete($job->model_path);
        }

        if (! empty($job->log_path) && Storage::disk('local')->exists($job->log_path)) {
            Storage::disk('local')->delete($job->log_path);
        }

        $job->delete();
        $deleted++;
    });

    $this->info(sprintf('Entrenamientos huérfanos eliminados: %d.', $deleted));

    return self::SUCCESS;
})->purpose('Delete orphan training jobs and their generated artifacts');
