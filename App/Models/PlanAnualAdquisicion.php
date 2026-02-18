<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanAnualAdquisicion extends Model
{
    protected $table = 'plan_anual_adquisiciones';

    protected $fillable = [
        'anio',
        'codigo_necesidad',
        'descripcion',
        'valor_estimado',
        'modalidad_contratacion',
        'trimestre_estimado',
        'dependencia_solicitante',
        'estado',
        'activo',
        'fecha_modificacion',
        'observaciones',
    ];

    protected $casts = [
        'valor_estimado' => 'decimal:2',
        'activo' => 'boolean',
        'fecha_modificacion' => 'datetime',
    ];

    /**
     * Relación: Workflow asociado a la modalidad
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'modalidad_contratacion', 'codigo');
    }

    /**
     * Verificar si la necesidad está vigente
     */
    public function esVigente(): bool
    {
        return $this->estado === 'vigente' && $this->activo;
    }

    /**
     * Verificar si la necesidad está en uso (tiene procesos asociados)
     */
    public function tieneProcesoAsociado(): bool
    {
        // Esta relación debe implementarse cuando se vinculen procesos con PAA
        return false; // TODO: implementar
    }

    /**
     * Generar código de certificado de inclusión
     */
    public function generarCertificadoInclusionAttribute(): string
    {
        return "CERT-PAA-{$this->anio}-{$this->id}";
    }

    /**
     * Scope: PAA del año vigente
     */
    public function scopeAnioVigente($query)
    {
        return $query->where('anio', date('Y'));
    }

    /**
     * Scope: PAA activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: PAA vigentes
     */
    public function scopeVigentes($query)
    {
        return $query->where('estado', 'vigente');
    }

    /**
     * Scope: Por modalidad de contratación
     */
    public function scopeModalidad($query, string $modalidad)
    {
        return $query->where('modalidad_contratacion', $modalidad);
    }

    /**
     * Scope: Por dependencia solicitante
     */
    public function scopeDependencia($query, string $dependencia)
    {
        return $query->where('dependencia_solicitante', $dependencia);
    }
}
