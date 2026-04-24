<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'type',
        'validation_method',
        'status',
        'dns_provider',
        'dns_credentials',
        'path',
        'expires_at',
        'last_renewed_at',
        'error_message',
    ];

    protected $casts = [
        'dns_credentials' => 'array',
        'expires_at' => 'datetime',
        'last_renewed_at' => 'datetime',
    ];

    /**
     * Check if the certificate is nearing expiry (less than 30 days).
     */
    public function isNearingExpiry(): bool
    {
        if (!$this->expires_at) {
            return true;
        }

        return $this->expires_at->diffInDays(now()) < 30;
    }

    /**
     * Check if the certificate file exists on disk.
     */
    public function fileExists(): bool
    {
        return $this->path && file_exists($this->path);
    }
}
