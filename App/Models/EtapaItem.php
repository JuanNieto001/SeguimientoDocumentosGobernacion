<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EtapaItem extends Model
{
    protected $table = 'etapa_items';

    protected $fillable = [
        'etapa_id',
        'orden',
        'label',
        'requerido',
    ];

    protected $casts = [
        'requerido' => 'boolean',
    ];

    /**
     * Relación: Un item pertenece a una etapa
     */
    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class);
    }

    /**
     * Relación: Un item tiene muchos checks en procesos
     */
    public function checks(): HasMany
    {
        return $this->hasMany(ProcesoEtapaCheck::class);
    }

    /**
     * Scope: Solo items requeridos
     */
    public function scopeRequeridos($query)
    {
        return $query->where('requerido', true);
    }
}
