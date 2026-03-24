<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ContratoAplicacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contratos_aplicacion';

    protected $fillable = [
        'nombre_aplicacion',
        'proveedor',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'numero_contrato',
        'valor_contrato',
        'modalidad_contratacion',
        'estado',
        'secop_id',
        'secop_url',
        'secretaria_id',
        'unidad_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_inicio'    => 'date',
        'fecha_fin'       => 'date',
        'valor_contrato'  => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeVencidos($query)
    {
        return $query->where(function ($q) {
            $q->where('estado', 'vencido')
              ->orWhere(fn ($s) => $s->where('estado', 'activo')->where('fecha_fin', '<', now()));
        });
    }

    public function scopeProximosAVencer($query, int $dias = 30)
    {
        return $query->where('estado', 'activo')
            ->whereBetween('fecha_fin', [now(), now()->addDays($dias)]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function getDiasRestantesAttribute(): int
    {
        return max(0, (int) now()->diffInDays($this->fecha_fin, false));
    }

    public function getEstaVencidoAttribute(): bool
    {
        return $this->fecha_fin->isPast();
    }

    public function getEstaProximoAVencerAttribute(): bool
    {
        if ($this->esta_vencido) {
            return false;
        }
        return (int) now()->diffInDays($this->fecha_fin, false) <= 30;
    }

    public function getEstadoEfectivoAttribute(): string
    {
        if ($this->estado === 'cancelado') {
            return 'cancelado';
        }
        if ($this->esta_vencido) {
            return 'vencido';
        }
        if ($this->esta_proximo_a_vencer) {
            return 'por_vencer';
        }
        return 'activo';
    }
}
