<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class WaterSample extends Model
{
    use HasFactory;

    protected $table = 'water_samples';

    protected $fillable = [
        'ph',
        'temperature',
        'status',
        'collected_at',
    ];

    protected $casts = [
        'ph' => 'float',
        'temperature' => 'float',
        'collected_at' => 'datetime',
    ];
}
