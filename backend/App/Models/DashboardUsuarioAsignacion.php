<?php
/**
 * Archivo: backend/App/Models/DashboardUsuarioAsignacion.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardUsuarioAsignacion extends Model
{
    protected $table = 'dashboard_usuario_asignaciones';

    protected $fillable = [
        'user_id',
        'dashboard_plantilla_id',
        'prioridad',
        'config_json',
        'activo',
    ];

    protected $casts = [
        'config_json' => 'array',
        'activo' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(DashboardPlantilla::class, 'dashboard_plantilla_id');
    }
}

