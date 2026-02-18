<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etapa extends Model
{
    protected $table = 'etapas';

    protected $fillable = [
        'workflow_id',
        'orden',
        'nombre',
        'area_role',
        'next_etapa_id',
        'activa',
        'dias_estimados',
        'area_responsable',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /**
     * Relación: Una etapa pertenece a un workflow
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relación: Una etapa tiene muchos items (checklist)
     */
    public function items(): HasMany
    {
        return $this->hasMany(EtapaItem::class)->orderBy('orden');
    }

    /**
     * Relación: Una etapa tiene muchas instancias en procesos
     */
    public function procesoEtapas(): HasMany
    {
        return $this->hasMany(ProcesoEtapa::class);
    }

    /**
     * Relación: Siguiente etapa en el flujo
     */
    public function siguienteEtapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class, 'next_etapa_id');
    }

    /**
     * Relación: Etapas que apuntan a esta como siguiente
     */
    public function etapasAnteriores(): HasMany
    {
        return $this->hasMany(Etapa::class, 'next_etapa_id');
    }

    /**
     * Relación: Tipos de archivo permitidos en esta etapa
     */
    public function tiposArchivo(): HasMany
    {
        return $this->hasMany(TipoArchivoPorEtapa::class)->orderBy('orden');
    }

    /**
     * Verificar si la etapa es de Unidad Solicitante
     */
    public function esUnidadSolicitante(): bool
    {
        return $this->area_role === 'unidad_solicitante';
    }

    /**
     * Verificar si es la primera etapa del workflow
     */
    public function esPrimera(): bool
    {
        return $this->orden === $this->workflow->etapas()->min('orden');
    }

    /**
     * Verificar si es la última etapa del workflow
     */
    public function esUltima(): bool
    {
        return $this->next_etapa_id === null;
    }

    /**
     * Scope: Solo etapas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
