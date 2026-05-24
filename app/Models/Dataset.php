<?php

namespace App\Models;

use App\Enums\DatasetStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TrainingConfiguration;

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
        'file_size' => 'integer',
        'rows_count' => 'integer',
        'columns_count' => 'integer',
        'status' => DatasetStatus::class,
        'metadata' => 'array',
        'metrics' => 'array',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function trainingConfigurations(): HasMany
    {
        return $this->hasMany(TrainingConfiguration::class);
    }
}
