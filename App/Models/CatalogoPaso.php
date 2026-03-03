<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CatalogoPaso – Catálogo general de pasos reutilizables.
 *
 * Cada paso es una plantilla que puede ser usada en múltiples flujos.
 * Ej: "Definición de la Necesidad", "Revisión Jurídica", etc.
 */
class CatalogoPaso extends Model
{
    protected $table = 'catalogo_pasos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'icono',
        'color',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Instancias de este paso en distintos flujos.
     */
    public function flujoPasos(): HasMany
    {
        return $this->hasMany(FlujoPaso::class, 'catalogo_paso_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
