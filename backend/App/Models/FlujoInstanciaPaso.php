<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FlujoInstanciaPaso – Estado de un paso en una instancia de flujo.
 */
class FlujoInstanciaPaso extends Model
{
    protected $table = 'flujo_instancia_pasos';

    protected $fillable = [
        'instancia_id',
        'flujo_paso_id',
        'orden',
        'estado',
        'omitido_por_condicion',
        'observaciones',
        'recibido_por',
        'recibido_at',
        'completado_por',
        'completado_at',
        'devuelto_por',
        'devuelto_at',
        'motivo_devolucion',
    ];

    protected $casts = [
        'omitido_por_condicion' => 'boolean',
        'recibido_at'           => 'datetime',
        'completado_at'         => 'datetime',
        'devuelto_at'           => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function instancia(): BelongsTo
    {
        return $this->belongsTo(FlujoInstancia::class, 'instancia_id');
    }

    public function flujoPaso(): BelongsTo
    {
        return $this->belongsTo(FlujoPaso::class, 'flujo_paso_id');
    }

    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    public function completadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completado_por');
    }

    public function devueltoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'devuelto_por');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(FlujoInstanciaDoc::class, 'instancia_paso_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * ¿Todos los documentos obligatorios están subidos?
     */
    public function documentosCompletos(): bool
    {
        $docsRequeridos = $this->flujoPaso->documentos()
            ->where('es_obligatorio', true)
            ->pluck('id');

        if ($docsRequeridos->isEmpty()) {
            return true;
        }

        $docsSubidos = $this->documentos()
            ->whereIn('flujo_paso_documento_id', $docsRequeridos)
            ->pluck('flujo_paso_documento_id')
            ->unique();

        return $docsSubidos->count() >= $docsRequeridos->count();
    }
}
