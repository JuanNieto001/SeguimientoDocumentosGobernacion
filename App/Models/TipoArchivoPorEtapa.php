<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoArchivoPorEtapa extends Model
{
    protected $table = 'tipos_archivo_por_etapa';

    protected $fillable = [
        'etapa_id',
        'tipo',
        'label',
        'requerido',
        'orden',
        'descripcion',
        'extensiones_permitidas',
        'tamanio_maximo_mb',
    ];

    protected $casts = [
        'requerido' => 'boolean',
        'tamanio_maximo_mb' => 'integer',
        'extensiones_permitidas' => 'array',
    ];

    /**
     * Relación: Un tipo de archivo pertenece a una etapa
     */
    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class);
    }

    /**
     * Verificar si una extensión es válida para este tipo
     */
    public function extensionValida(string $extension): bool
    {
        if (empty($this->extensiones_permitidas)) {
            return true; // Si no hay restricción, permitir cualquier extensión
        }

        return in_array(strtolower($extension), array_map('strtolower', $this->extensiones_permitidas));
    }

    /**
     * Verificar si un tamaño es válido para este tipo
     */
    public function tamanioValido(int $bytes): bool
    {
        if (!$this->tamanio_maximo_mb) {
            return true; // Si no hay restricción, permitir cualquier tamaño
        }

        $maxBytes = $this->tamanio_maximo_mb * 1024 * 1024;
        return $bytes <= $maxBytes;
    }

    /**
     * Obtener las extensiones permitidas como string
     */
    public function getExtensionesFormateadasAttribute(): string
    {
        if (empty($this->extensiones_permitidas)) {
            return 'Cualquier formato';
        }

        return implode(', ', array_map('strtoupper', $this->extensiones_permitidas));
    }

    /**
     * Scope: Solo tipos requeridos
     */
    public function scopeRequeridos($query)
    {
        return $query->where('requerido', true);
    }

    /**
     * Scope: Por tipo específico
     */
    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
