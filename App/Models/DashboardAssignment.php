<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardAssignment extends Model
{
    protected $fillable = [
        'dashboard_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'active'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'active' => 'boolean'
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}

class DashboardRoleAssignment extends Model
{
    protected $fillable = [
        'dashboard_id',
        'role_name',
        'assigned_by',
        'assigned_at',
        'active'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'active' => 'boolean'
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}

class DashboardSecretariaAssignment extends Model
{
    protected $fillable = [
        'dashboard_id',
        'secretaria_id',
        'assigned_by',
        'assigned_at',
        'active'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'active' => 'boolean'
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}