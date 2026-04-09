<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearProcesoContratacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('unidad_solicitante') ||
            $this->user()->hasRole('admin')
        );
    }

    public function rules(): array
    {
        return [
            'objeto'                     => 'required|string|max:2000',
            'valor'                      => 'required|numeric|min:0',
            'plazo_meses'                => 'required|integer|min:1',
            'estudio_previo'             => 'required|file|mimes:pdf,doc,docx|max:20480',
            'secretaria_id'              => 'required|exists:secretarias,id',
            'unidad_id'                  => 'required|exists:unidades,id',
            'contratista_nombre'         => 'nullable|string|max:255',
            'contratista_tipo_documento'  => 'nullable|in:CC,CE,NIT,PEP',
            'contratista_documento'      => 'nullable|string|max:50',
            'contratista_email'          => 'nullable|email|max:255',
            'contratista_telefono'       => 'nullable|string|max:20',
            'supervisor_id'              => 'nullable|exists:users,id',
            'ordenador_gasto_id'         => 'nullable|exists:users,id',
            'jefe_unidad_id'             => 'nullable|exists:users,id',
            'abogado_unidad_id'          => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'objeto.required'          => 'El objeto del contrato es obligatorio.',
            'valor.required'           => 'El valor del contrato es obligatorio.',
            'valor.numeric'            => 'El valor debe ser numérico.',
            'plazo_meses.required'     => 'El plazo es obligatorio.',
            'plazo_meses.integer'      => 'El plazo solo acepta un número entero en meses.',
            'plazo_meses.min'          => 'El plazo mínimo es 1 mes.',
            'estudio_previo.required'  => 'Debe subir el documento de Estudios Previos ANTES de crear la solicitud.',
            'estudio_previo.file'      => 'El estudio previo debe ser un archivo.',
            'estudio_previo.mimes'     => 'El estudio previo debe ser un archivo PDF, DOC o DOCX.',
            'secretaria_id.required'   => 'Seleccione la Secretaría de origen.',
            'unidad_id.required'       => 'Seleccione la Unidad de origen.',
        ];
    }
}
