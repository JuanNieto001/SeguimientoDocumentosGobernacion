<?php
/**
 * Archivo: backend/App/Models/DashboardWidget.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    protected $table = 'dashboard_widgets';

    protected $fillable = [
        'dashboard_plantilla_id',
        'titulo',
        'tipo',
        'metrica',
        'orden',
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

