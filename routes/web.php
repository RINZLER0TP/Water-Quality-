<?php

use App\Http\Controllers\DatasetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrainingConfigurationController;
use App\Http\Controllers\TrainingJobController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/debug', function () {
    $job = \App\Models\TrainingJob::find(23);
    $service = app(\App\Services\TrainingJobs\TrainingJobService::class);
    
    // Simulate exactly what happens
    try {
        $job = $service->execute($job);
        return response()->json($job->toArray());
    } catch (\Throwable $e) {
        return "FAILED: " . $e->getMessage();
    }
});

Route::get('/dashboard', function () {
    try {
        $datasetsCount = \Illuminate\Support\Facades\Schema::hasTable('datasets') ? \App\Models\Dataset::count() : 0;
        $trainingConfigurationsCount = \Illuminate\Support\Facades\Schema::hasTable('training_configurations') ? \App\Models\TrainingConfiguration::count() : 0;
        $completedTrainingJobsCount = \Illuminate\Support\Facades\Schema::hasTable('training_jobs') ? \App\Models\TrainingJob::where('status', 'completed')->count() : 0;
        $alertsCount = \Illuminate\Support\Facades\Schema::hasTable('water_samples') 
            ? \App\Models\WaterSample::where('status', 'like', '%alert%')->orWhere('ph', '<', 6.5)->orWhere('ph', '>', 8.5)->count() 
            : 0;
        $recentActivity = \Illuminate\Support\Facades\Schema::hasTable('datasets') ? \App\Models\Dataset::latest()->take(3)->get() : collect();
        $averageAccuracy = $completedTrainingJobsCount > 0 && \Illuminate\Support\Facades\Schema::hasTable('training_jobs')
            ? (float) round(\App\Models\TrainingJob::where('status', 'completed')->get()->avg(fn (\App\Models\TrainingJob $job): float => (float) data_get($job->metrics, 'accuracy', 0)) * 100, 2)
            : null;
    } catch (\Exception $e) {
        $datasetsCount = 0;
        $trainingConfigurationsCount = 0;
        $completedTrainingJobsCount = 0;
        $alertsCount = 0;
        $recentActivity = collect();
        $averageAccuracy = null;
    }
    
    return view('dashboard', [
        'datasetsCount' => $datasetsCount,
        'trainingConfigurationsCount' => $trainingConfigurationsCount,
        'completedTrainingJobsCount' => $completedTrainingJobsCount,
        'averageAccuracy' => $averageAccuracy,
        'alertsCount' => $alertsCount,
        'recentActivity' => $recentActivity,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('datasets')->name('datasets.')->group(function () {
        Route::get('/', [DatasetController::class, 'index'])->name('index');
        Route::get('/create', [DatasetController::class, 'create'])->name('create');
        Route::post('/', [DatasetController::class, 'store'])->name('store');
        Route::get('/{dataset}', [DatasetController::class, 'show'])->name('show');
        Route::get('/{dataset}/download', [DatasetController::class, 'download'])->name('download');
        Route::delete('/{dataset}', [DatasetController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('training-configurations')->name('training-configurations.')->group(function () {
        Route::get('/', [TrainingConfigurationController::class, 'index'])->name('index');

        Route::get('/create', [TrainingConfigurationController::class, 'create'])->name('create');
        Route::post('/', [TrainingConfigurationController::class, 'store'])->name('store');
        Route::get('/datasets/{dataset}/preview', [TrainingConfigurationController::class, 'preview'])->name('preview');
        Route::get('/{trainingConfiguration}', [TrainingConfigurationController::class, 'show'])->name('show');
        Route::delete('/{trainingConfiguration}', [TrainingConfigurationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('training-jobs')->name('training-jobs.')->group(function () {
        Route::get('/', [TrainingJobController::class, 'index'])->name('index');
        Route::get('/create', [TrainingJobController::class, 'create'])->name('create');
        Route::post('/', [TrainingJobController::class, 'store'])->name('store');
        Route::get('/{trainingJob}', [TrainingJobController::class, 'show'])->name('show');
        Route::get('/{trainingJob}/metrics', [TrainingJobController::class, 'metrics'])->name('metrics');
        Route::delete('/{trainingJob}', [TrainingJobController::class, 'destroy'])->name('destroy');
    });

    // Route::resource('water-samples', \App\Http\Controllers\WaterSampleController::class);
    
    // Predicciones Weka
    Route::get('/predictions/dataset', [\App\Http\Controllers\PredictionController::class, 'dataset'])->name('predictions.dataset');
    Route::post('/predictions/dataset', [\App\Http\Controllers\PredictionController::class, 'storeDataset'])->name('predictions.dataset.store');
    Route::resource('predictions', \App\Http\Controllers\PredictionController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
