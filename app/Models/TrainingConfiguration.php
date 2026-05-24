<?php

namespace App\Models;

use App\Enums\TrainingAlgorithm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dataset_id',
        'created_by',
        'target_column',
        'algorithm',
        'parameters',
        'analysis',
    ];

    protected $casts = [
        'algorithm' => TrainingAlgorithm::class,
        'parameters' => 'array',
        'analysis' => 'array',
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function trainingJobs(): HasMany
    {
        return $this->hasMany(TrainingJob::class);
    }
}