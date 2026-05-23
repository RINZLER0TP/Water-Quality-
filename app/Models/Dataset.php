<?php

namespace App\Models;

use App\Enums\DatasetStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dataset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_name',
        'file_path',
        'file_size',
        'rows_count',
        'columns_count',
        'status',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'rows_count' => 'integer',
        'columns_count' => 'integer',
        'status' => DatasetStatus::class,
        'metadata' => 'array',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
