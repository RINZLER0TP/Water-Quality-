<?php

use App\Http\Controllers\DatasetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrainingConfigurationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    try {
        $datasetsCount = \Illuminate\Support\Facades\Schema::hasTable('datasets') ? \App\Models\Dataset::count() : 0;
        $alertsCount = \Illuminate\Support\Facades\Schema::hasTable('water_samples') 
            ? \App\Models\WaterSample::where('status', 'like', '%alert%')->orWhere('ph', '<', 6.5)->orWhere('ph', '>', 8.5)->count() 
            : 0;
        $recentActivity = \Illuminate\Support\Facades\Schema::hasTable('datasets') ? \App\Models\Dataset::latest()->take(3)->get() : collect();
    } catch (\Exception $e) {
        $datasetsCount = 0;
        $alertsCount = 0;
        $recentActivity = collect();
    }
    
    return view('dashboard', [
        'datasetsCount' => $datasetsCount,
        'modelsCount' => 0, // Implementación futura para Weka Models
        'accuracy' => '0.0', // Implementación futura
        'alertsCount' => $alertsCount,
        'recentActivity' => $recentActivity
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
