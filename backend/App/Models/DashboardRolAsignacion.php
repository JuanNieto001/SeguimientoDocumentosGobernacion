<?php
/**
 * Archivo: backend/App/Models/DashboardRolAsignacion.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardRolAsignacion extends Model
{
    protected $table = 'dashboard_rol_asignaciones';

    protected $fillable = [
        'role_name',
        'dashboard_plantilla_id',
        'prioridad',
        'config_json',
        'activo',
    ];

    protected $casts = [
        'config_json' => 'array',
        'activo' => 'boolean',
    ];

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(DashboardPlantilla::class, 'dashboard_plantilla_id');
    }
}

