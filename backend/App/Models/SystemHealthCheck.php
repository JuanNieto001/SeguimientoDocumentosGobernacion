<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemHealthCheck extends Model
{
    protected $fillable = [
        'checked_at',
        'status',
        'response_ms',
        'active_sessions',
        'target_concurrent_users',
        'checks',
        'message',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'checks' => 'array',
    ];
}
