<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FlujoPasoCondicion – Condición opcional para activar/omitir un paso.
 *
 * Ej: "Si monto_estimado > 50000000, requiere revisión jurídica adicional"
 */
class FlujoPasoCondicion extends Model
{
    protected $table = 'flujo_paso_condiciones';

    protected $fillable = [
        'flujo_paso_id',
        'campo',
        'operador',
        'valor',
        'accion',
        'descripcion',
        'prioridad',
        'activo',
    ];

    protected $casts = [
        'activo'    => 'boolean',
        'prioridad' => 'integer',
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

    /*
    |--------------------------------------------------------------------------
    | EVALUACIÓN DE CONDICIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Evaluar si la condición se cumple con los datos proporcionados.
     *
     * @param array $datos Datos del proceso.
     * @return bool
     */
    public function evaluar(array $datos): bool
    {
        $valorCampo = data_get($datos, $this->campo);

        if ($valorCampo === null) {
            return false;
        }

        $valorCondicion = $this->valor;

        return match ($this->operador) {
            '>'       => (float) $valorCampo > (float) $valorCondicion,
            '<'       => (float) $valorCampo < (float) $valorCondicion,
            '>='      => (float) $valorCampo >= (float) $valorCondicion,
            '<='      => (float) $valorCampo <= (float) $valorCondicion,
            '=='      => $valorCampo == $valorCondicion,
            '!='      => $valorCampo != $valorCondicion,
            'in'      => in_array($valorCampo, json_decode($valorCondicion, true) ?? []),
            'not_in'  => !in_array($valorCampo, json_decode($valorCondicion, true) ?? []),
            'between' => $this->evaluarBetween($valorCampo, $valorCondicion),
            'contains'=> str_contains((string) $valorCampo, $valorCondicion),
            default   => false,
        };
    }

    private function evaluarBetween($valor, string $rango): bool
    {
        $limites = json_decode($rango, true);
        if (!is_array($limites) || count($limites) !== 2) {
            return false;
        }

        return (float) $valor >= (float) $limites[0]
            && (float) $valor <= (float) $limites[1];
    }
}
