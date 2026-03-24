<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardAsignacionAuditoria extends Model
{
    protected $table = 'dashboard_asignacion_auditorias';

    protected $fillable = [
        'actor_user_id',
        'tipo_objetivo',
        'role_name',
        'target_user_id',
        'accion',
        'dashboard_plantilla_anterior_id',
        'dashboard_plantilla_nueva_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function plantillaAnterior(): BelongsTo
    {
        return $this->belongsTo(DashboardPlantilla::class, 'dashboard_plantilla_anterior_id');
    }

    public function plantillaNueva(): BelongsTo
    {
        return $this->belongsTo(DashboardPlantilla::class, 'dashboard_plantilla_nueva_id');
    }
}
