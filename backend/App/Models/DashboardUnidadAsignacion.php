<?php
/**
 * Archivo: backend/App/Models/DashboardUnidadAsignacion.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardUnidadAsignacion extends Model
{
    protected $table = 'dashboard_unidad_asignaciones';

    protected $fillable = [
        'unidad_id',
        'dashboard_plantilla_id',
        'prioridad',
        'config_json',
        'activo',
    ];

    protected $casts = [
        'config_json' => 'array',
        'activo' => 'boolean',
    ];

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(DashboardPlantilla::class, 'dashboard_plantilla_id');
    }
}

