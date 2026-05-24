<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prediction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'training_job_id',
        'input_data',
        'predicted_class',
        'confidence',
        'execution_time_ms',
    ];

    protected $casts = [
        'input_data' => 'array',
        'confidence' => 'float',
        'execution_time_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingJob(): BelongsTo
    {
        return $this->belongsTo(TrainingJob::class);
    }
}
