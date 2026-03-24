<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardSecretariaAsignacion extends Model
{
    protected $table = 'dashboard_secretaria_asignaciones';

    protected $fillable = [
        'secretaria_id',
        'dashboard_plantilla_id',
        'prioridad',
        'config_json',
        'activo',
    ];

    protected $casts = [
        'config_json' => 'array',
        'activo' => 'boolean',
    ];

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(DashboardPlantilla::class, 'dashboard_plantilla_id');
    }
}
