<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FlujoPasoDocumento – Documento requerido por paso.
 */
class FlujoPasoDocumento extends Model
{
    protected $table = 'flujo_paso_documentos';

    protected $fillable = [
        'flujo_paso_id',
        'nombre',
        'descripcion',
        'tipo_archivo',
        'es_obligatorio',
        'max_archivos',
        'max_tamano_mb',
        'plantilla_url',
        'orden',
        'activo',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'activo'         => 'boolean',
        'max_archivos'   => 'integer',
        'max_tamano_mb'  => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function flujoPaso(): BelongsTo
    {
        return $this->belongsTo(FlujoPaso::class, 'flujo_paso_id');
    }
}
