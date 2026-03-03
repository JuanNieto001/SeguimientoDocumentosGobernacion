<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FlujoPasoResponsable – Responsable asignado por paso.
 */
class FlujoPasoResponsable extends Model
{
    protected $table = 'flujo_paso_responsables';

    protected $fillable = [
        'flujo_paso_id',
        'rol',
        'user_id',
        'unidad_id',
        'tipo',
        'es_principal',
        'activo',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'activo'       => 'boolean',
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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }
}
