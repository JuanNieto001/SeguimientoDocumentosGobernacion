<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FlujoPaso – Un paso asignado a una versión de flujo con orden específico.
 *
 * Referencia a un CatalogoPaso pero puede personalizar nombre,
 * instrucciones y configuración para cada flujo.
 */
class FlujoPaso extends Model
{
    protected $table = 'flujo_pasos';

    protected $fillable = [
        'flujo_version_id',
        'catalogo_paso_id',
        'orden',
        'nombre_personalizado',
        'instrucciones',
        'es_obligatorio',
        'es_paralelo',
        'dias_estimados',
        'area_responsable_default',
        'activo',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'es_paralelo'    => 'boolean',
        'activo'         => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function version(): BelongsTo
    {
        return $this->belongsTo(FlujoVersion::class, 'flujo_version_id');
    }

    public function catalogoPaso(): BelongsTo
    {
        return $this->belongsTo(CatalogoPaso::class, 'catalogo_paso_id');
    }

    public function condiciones(): HasMany
    {
        return $this->hasMany(FlujoPasoCondicion::class, 'flujo_paso_id')
            ->where('activo', true)
            ->orderBy('prioridad');
    }

    public function responsables(): HasMany
    {
        return $this->hasMany(FlujoPasoResponsable::class, 'flujo_paso_id')
            ->where('activo', true);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(FlujoPasoDocumento::class, 'flujo_paso_id')
            ->where('activo', true)
            ->orderBy('orden');
    }

    public function instanciaPasos(): HasMany
    {
        return $this->hasMany(FlujoInstanciaPaso::class, 'flujo_paso_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Nombre efectivo: personalizado o del catálogo.
     */
    public function getNombreEfectivoAttribute(): string
    {
        return $this->nombre_personalizado ?? $this->catalogoPaso->nombre ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Evaluar condiciones contra datos del proceso.
     *
     * @param array $datos Datos del proceso (monto_estimado, tipo_persona, etc.)
     * @return array Acciones resultantes de las condiciones.
     */
    public function evaluarCondiciones(array $datos): array
    {
        $acciones = [];

        foreach ($this->condiciones as $condicion) {
            if ($condicion->evaluar($datos)) {
                $acciones[] = [
                    'accion'      => $condicion->accion,
                    'descripcion' => $condicion->descripcion,
                    'condicion_id' => $condicion->id,
                ];
            }
        }

        return $acciones;
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

    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }
}
