<?php
/**
 * Archivo: backend/App/Models/DashboardPlantilla.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardPlantilla extends Model
{
    protected $table = 'dashboard_plantillas';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'config_json',
        'activo',
    ];

    protected $casts = [
        'config_json' => 'array',
        'activo' => 'boolean',
    ];

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class, 'dashboard_plantilla_id')->orderBy('orden');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(DashboardRolAsignacion::class, 'dashboard_plantilla_id');
    }
}

