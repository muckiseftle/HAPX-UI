<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProxyBackend extends Model
{
    use HasFactory;

    protected $fillable = [
        'proxy_host_id',
        'name',
        'address',
        'port',
        'is_backup',
        'is_active',
    ];

    protected $casts = [
        'is_backup' => 'boolean',
        'is_active' => 'boolean',
        'port' => 'integer',
    ];

    /**
     * Get the proxy host that owns the backend.
     */
    public function proxyHost(): BelongsTo
    {
        return $this->belongsTo(ProxyHost::class);
    }
}
