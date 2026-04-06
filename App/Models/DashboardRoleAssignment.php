<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardRoleAssignment extends Model
{
    protected $table = 'dashboard_role_assignments';

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
