<?php
/**
 * Archivo: backend/App/Models/FlujoInstanciaDoc.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FlujoInstanciaDoc – Documento subido en un paso de instancia.
 */
class FlujoInstanciaDoc extends Model
{
    protected $table = 'flujo_instancia_docs';

    protected $fillable = [
        'instancia_paso_id',
        'flujo_paso_documento_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_mime',
        'tamano_bytes',
        'subido_por',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'tamano_bytes' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function instanciaPaso(): BelongsTo
    {
        return $this->belongsTo(FlujoInstanciaPaso::class, 'instancia_paso_id');
    }

    public function documentoRequerido(): BelongsTo
    {
        return $this->belongsTo(FlujoPasoDocumento::class, 'flujo_paso_documento_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}

