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
    ];

    protected $casts = [
        'valor_estimado' => 'decimal:2',
        'activo'         => 'boolean',
        'anio'           => 'integer',
        'trimestre_estimado' => 'integer',
    ];

    /* ---------------------------------------------------------------- */
    /* RELACIONES                                                         */
    /* ---------------------------------------------------------------- */

    /** Procesos que referencian este ítem del PAA */
    public function procesos()
    {
        return $this->hasMany(\App\Models\Proceso::class, 'paa_id');
    }

    /* ---------------------------------------------------------------- */
    /* HELPERS                                                            */
    /* ---------------------------------------------------------------- */

    public function esVigente(): bool
    {
        return $this->estado === 'vigente' && $this->activo;
    }

    public function getCertificadoCodeAttribute(): string
    {
        return "CERT-PAA-{$this->anio}-{$this->id}";
    }

    /* ---------------------------------------------------------------- */
    /* SCOPES                                                             */
    /* ---------------------------------------------------------------- */

    public function scopeAnioVigente($query)
    {
        return $query->where('anio', date('Y'));
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

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
