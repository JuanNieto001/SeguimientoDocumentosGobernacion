<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoAplicacion extends Model
{
    protected $table = 'contratos_aplicaciones';

    protected $fillable = [
        'aplicacion',
        'numero_contrato',
        'proveedor',
        'objeto',
        'fecha_inicio',
        'fecha_fin',
        'valor_total',
        'estado',
        'secop_proceso_id',
        'secop_url',
        'secop_metadata',
        'responsable',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'valor_total' => 'decimal:2',
        'secop_metadata' => 'array',
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeVigentes($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('fecha_fin')
                ->orWhereDate('fecha_fin', '>=', now()->toDateString());
        });
    }
}
