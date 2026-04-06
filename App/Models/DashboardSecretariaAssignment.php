<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardSecretariaAssignment extends Model
{
    protected $table = 'dashboard_secretaria_assignments';

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
