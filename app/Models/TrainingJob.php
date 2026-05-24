<?php

namespace App\Models;

use App\Enums\TrainingAlgorithm;
use App\Enums\TrainingJobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_configuration_id',
        'created_by',
        'dataset_id',
        'algorithm',
        'target_column',
        'parameters',
        'status',
        'model_path',
        'log_path',
        'execution_log',
        'error_message',
        'metrics',
        'confusion_matrix',
        'cross_validation_folds',
        'random_seed',
        'started_at',
        'completed_at',
        'training_time_ms',
    ];

    protected $casts = [
        'algorithm' => TrainingAlgorithm::class,
        'status' => TrainingJobStatus::class,
        'parameters' => 'array',
        'metrics' => 'array',
        'confusion_matrix' => 'array',
        'cross_validation_folds' => 'integer',
        'random_seed' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'training_time_ms' => 'integer',
    ];

    public function trainingConfiguration(): BelongsTo
    {
        return $this->belongsTo(TrainingConfiguration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }
}
