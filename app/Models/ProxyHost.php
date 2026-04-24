<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProxyHost extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hostnames',
        'mode',
        'is_active',
        'listen_address',
        'listen_port',
        'force_https',
        'tls_termination',
        'certificate_path',
        'balance_algorithm',
        'description',
    ];

    protected $casts = [
        'hostnames' => 'array',
        'is_active' => 'boolean',
        'force_https' => 'boolean',
        'tls_termination' => 'boolean',
        'listen_port' => 'integer',
    ];

    public function backends(): HasMany
    {
        return $this->hasMany(ProxyBackend::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
