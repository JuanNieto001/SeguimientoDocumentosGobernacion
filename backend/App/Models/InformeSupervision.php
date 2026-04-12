<?php
/**
 * Archivo: backend/App/Models/InformeSupervision.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InformeSupervision extends Model
{
    protected $table = 'informes_supervision';

    protected $fillable = [
        'proceso_id',
        'supervisor_id',
        'numero_informe',
        'periodo_inicio',
        'periodo_fin',
        'fecha_informe',
        'estado_avance',
        'porcentaje_avance',
        'descripcion_actividades',
        'observaciones',
        'archivo_soporte',
        'estado_informe',
        'observaciones_revision',
        'revisado_por',
        'fecha_revision',
    ];

    protected $casts = [
        'fecha_informe'   => 'date',
        'fecha_revision'  => 'datetime',
        'porcentaje_avance' => 'integer',
    ];

    // ──────────────────────────────────────────────────────────────
    // RELACIONES
    // ──────────────────────────────────────────────────────────────

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoContrato::class, 'informe_id');
    }

    // ──────────────────────────────────────────────────────────────
    // ACCESSORS
    // ──────────────────────────────────────────────────────────────

    public function getLabelEstadoInformeAttribute(): string
    {
        return match($this->estado_informe) {
            'borrador' => 'Borrador',
            'enviado'  => 'Enviado',
            'aprobado' => 'Aprobado',
            'devuelto' => 'Devuelto',
            default    => ucfirst($this->estado_informe),
        };
    }

    public function getLabelEstadoAvanceAttribute(): string
    {
        return match($this->estado_avance) {
            'en_ejecucion' => 'En ejecución',
            'con_retraso'  => 'Con retraso',
            'completado'   => 'Completado',
            'suspendido'   => 'Suspendido',
            default        => ucfirst($this->estado_avance),
        };
    }

    public function getColorEstadoAttribute(): string
    {
        return match($this->estado_informe) {
            'aprobado' => '#15803d',
            'enviado'  => '#2563eb',
            'devuelto' => '#dc2626',
            default    => '#64748b',
        };
    }

    public function getBgEstadoAttribute(): string
    {
        return match($this->estado_informe) {
            'aprobado' => '#dcfce7',
            'enviado'  => '#dbeafe',
            'devuelto' => '#fee2e2',
            default    => '#f1f5f9',
        };
    }
}

