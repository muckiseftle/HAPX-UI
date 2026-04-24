<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'connections',
        'bytes_in',
        'bytes_out',
        'requests_per_second',
        'avg_response_ms',
    ];
}
