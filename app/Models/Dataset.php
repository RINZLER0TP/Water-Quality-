<?php

namespace App\Models;

use App\Enums\DatasetStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dataset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'original_name',
        'file_path',
        'file_size',
        'rows_count',
        'columns_count',
        'status',
        'uploaded_by',
        'metadata',
        'metrics',
    ];

    protected $casts = [
        'file_size'      => 'integer',
        'rows_count'     => 'integer',
        'columns_count'  => 'integer',
        'status'         => DatasetStatus::class,
        'metadata'       => 'array',
        'metrics'        => 'array',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function trainingConfigurations(): HasMany
    {
        return $this->hasMany(TrainingConfiguration::class);
    }

    public function trainingJobs(): HasMany
    {
        return $this->hasMany(TrainingJob::class);
    }

    public function predictions(): HasManyThrough
    {
        return $this->hasManyThrough(Prediction::class, TrainingJob::class);
    }

    protected static function booted(): void
    {
        // Soft-delete en cascada: Dataset → Configuraciones → Jobs → Predicciones
        static::deleting(function (Dataset $dataset): void {
            // Primero cascadeamos las predicciones de cada job
            $dataset->trainingJobs()->each(
                fn (TrainingJob $job) => $job->predictions()->delete()
            );
            $dataset->trainingJobs()->delete();
            $dataset->trainingConfigurations()->each(
                fn (TrainingConfiguration $config) => $config->trainingJobs()->delete()
            );
            $dataset->trainingConfigurations()->delete();
        });

        // Restore en cascada inverso: Dataset → Configuraciones → Jobs → Predicciones
        static::restoring(function (Dataset $dataset): void {
            $dataset->trainingConfigurations()->withTrashed()->restore();
            $dataset->trainingJobs()->withTrashed()->each(
                fn (TrainingJob $job) => $job->predictions()->withTrashed()->restore()
            );
            $dataset->trainingJobs()->withTrashed()->restore();
        });
    }
}
