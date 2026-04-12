<?php
/**
 * Archivo: backend/App/Http/Requests/TransicionProcesoRequest.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Requests;

use App\Enums\EstadoProcesoCD;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class TransicionProcesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'estado_destino' => ['required', 'string', new Enum(EstadoProcesoCD::class)],
            'comentario'     => 'nullable|string|max:2000',
            // Campos opcionales según el estado
            'numero_cdp'              => 'nullable|string|max:50',
            'valor_cdp'               => 'nullable|numeric|min:0',
            'numero_proceso'          => 'nullable|string|max:50',
            'numero_rpc'              => 'nullable|string|max:50',
            'numero_contrato'         => 'nullable|string|max:50',
            'observaciones'           => 'nullable|string|max:2000',
            'observaciones_juridica'  => 'nullable|string|max:2000',
            'resultado'               => 'nullable|string|max:2000',
            'fecha_inicio'            => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'estado_destino.required' => 'Debe indicar el estado al que desea transicionar.',
            'estado_destino.Illuminate\Validation\Rules\Enum' => 'Estado no válido.',
        ];
    }
}

