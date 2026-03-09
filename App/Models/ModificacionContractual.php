<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModificacionContractual extends Model
{
    protected $table = 'modificaciones_contractuales';

    protected $fillable = [
        'proceso_id',
        'tipo_modificacion',
        'numero_modificacion',
        'fecha_modificacion',
        'valor_modificacion',
        'plazo_modificacion_dias',
        'justificacion',
        'documento_soporte',
        'aprobado_por',
        'fecha_aprobacion',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_modificacion' => 'date',
        'fecha_aprobacion' => 'datetime',
        'valor_modificacion' => 'decimal:2',
    ];

    /**
     * Relación: Una modificación pertenece a un proceso
     */
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    /**
     * Relación: Usuario que aprobó la modificación
     */
    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    /**
     * Verificar si es una adición
     */
    public function esAdicion(): bool
    {
        return $this->tipo_modificacion === 'adicion';
    }

    /**
     * Verificar si es una prórroga
     */
    public function esProrroga(): bool
    {
        return $this->tipo_modificacion === 'prorroga';
    }

    /**
     * Verificar si es una suspensión
     */
    public function esSuspension(): bool
    {
        return $this->tipo_modificacion === 'suspension';
    }

    /**
     * Verificar si está aprobada
     */
    public function estaAprobada(): bool
    {
        return $this->estado === 'aprobada';
    }

    /**
     * Calcular el porcentaje acumulado de adiciones aprobadas sobre el valor original del contrato
     */
    public static function porcentajeAdicionesAcumuladas(int $procesoId): float
    {
        $proceso = Proceso::find($procesoId);
        if (!$proceso || !$proceso->valor_estimado || $proceso->valor_estimado == 0) {
            return 0;
        }

        $totalAdiciones = static::where('proceso_id', $procesoId)
            ->where('tipo_modificacion', 'adicion')
            ->where('estado', 'aprobada')
            ->sum('valor_modificacion');

        return ($totalAdiciones / $proceso->valor_estimado) * 100;
    }

    /**
     * Verificar si una nueva adición excedería el límite del 50%
     */
    public static function excedeLimite50(int $procesoId, float $valorNuevaAdicion): bool
    {
        $proceso = Proceso::find($procesoId);
        if (!$proceso || !$proceso->valor_estimado || $proceso->valor_estimado == 0) {
            return true;
        }

        $totalAdiciones = static::where('proceso_id', $procesoId)
            ->where('tipo_modificacion', 'adicion')
            ->where('estado', 'aprobada')
            ->sum('valor_modificacion');

        return (($totalAdiciones + $valorNuevaAdicion) / $proceso->valor_estimado) * 100 > 50;
    }

    /**
     * Scope: Por tipo de modificación
     */
    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo_modificacion', $tipo);
    }

    /**
     * Scope: Modificaciones aprobadas
     */
    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    /**
     * Scope: Modificaciones pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
}
