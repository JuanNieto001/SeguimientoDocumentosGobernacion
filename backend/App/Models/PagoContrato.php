<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoContrato extends Model
{
    protected $table = 'pagos_contrato';

    protected $fillable = [
        'proceso_id',
        'informe_id',
        'numero_pago',
        'valor',
        'fecha_solicitud',
        'fecha_estimada_pago',
        'fecha_pago_efectivo',
        'estado',
        'numero_referencia',
        'observaciones',
        'archivo_soporte',
        'registrado_por',
    ];

    protected $casts = [
        'valor'               => 'decimal:2',
        'fecha_solicitud'     => 'date',
        'fecha_estimada_pago' => 'date',
        'fecha_pago_efectivo' => 'date',
    ];

    // ──────────────────────────────────────────────────────────────
    // RELACIONES
    // ──────────────────────────────────────────────────────────────

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function informe(): BelongsTo
    {
        return $this->belongsTo(InformeSupervision::class, 'informe_id');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    // ──────────────────────────────────────────────────────────────
    // ACCESSORS
    // ──────────────────────────────────────────────────────────────

    public function getLabelEstadoAttribute(): string
    {
        return match($this->estado) {
            'pendiente'  => 'Pendiente',
            'en_tramite' => 'En trámite',
            'aprobado'   => 'Aprobado',
            'pagado'     => 'Pagado',
            'rechazado'  => 'Rechazado',
            default      => ucfirst($this->estado),
        };
    }

    public function getColorEstadoAttribute(): string
    {
        return match($this->estado) {
            'pagado'     => '#15803d',
            'aprobado'   => '#2563eb',
            'en_tramite' => '#ca8a04',
            'rechazado'  => '#dc2626',
            default      => '#64748b',
        };
    }

    public function getBgEstadoAttribute(): string
    {
        return match($this->estado) {
            'pagado'     => '#dcfce7',
            'aprobado'   => '#dbeafe',
            'en_tramite' => '#fef9c3',
            'rechazado'  => '#fee2e2',
            default      => '#f1f5f9',
        };
    }

    public function getPorcentajePagadoAttribute(): string
    {
        return '';
    }

    /**
     * ¿Está próximo a vencer? (5 días o menos a fecha estimada)
     */
    public function getProximoAttribute(): bool
    {
        if (!$this->fecha_estimada_pago || in_array($this->estado, ['pagado', 'rechazado'])) {
            return false;
        }
        return now()->diffInDays($this->fecha_estimada_pago, false) <= 5
            && $this->fecha_estimada_pago >= now()->toDateString();
    }

    /**
     * ¿Está vencido?
     */
    public function getVencidoAttribute(): bool
    {
        if (!$this->fecha_estimada_pago || in_array($this->estado, ['pagado', 'rechazado'])) {
            return false;
        }
        return $this->fecha_estimada_pago < now()->toDateString();
    }
}
