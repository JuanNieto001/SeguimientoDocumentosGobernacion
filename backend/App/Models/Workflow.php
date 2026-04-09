<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $table = 'workflows';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
        'requiere_viabilidad_economica_inicial',
        'requiere_estudios_previos_completos',
        'observaciones',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_viabilidad_economica_inicial' => 'boolean',
        'requiere_estudios_previos_completos' => 'boolean',
    ];

    /**
     * Relación: Un workflow tiene muchas etapas
     */
    public function etapas(): HasMany
    {
        return $this->hasMany(Etapa::class)->orderBy('orden');
    }

    /**
     * Relación: Un workflow tiene muchos procesos
     */
    public function procesos(): HasMany
    {
        return $this->hasMany(Proceso::class);
    }

    /**
     * Obtener la primera etapa del workflow (menor orden)
     */
    public function primeraEtapa()
    {
        return $this->etapas()->orderBy('orden')->first();
    }

    /**
     * Obtener la última etapa del workflow (mayor orden)
     */
    public function ultimaEtapa()
    {
        return $this->etapas()->orderByDesc('orden')->first();
    }

    /**
     * Scope: Solo workflows activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
