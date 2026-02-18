<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProcesoEtapaArchivo extends Model
{
    protected $table = 'proceso_etapa_archivos';

    protected $fillable = [
        'proceso_id',
        'proceso_etapa_id',
        'etapa_id',
        'tipo_archivo',
        'nombre_original',
        'nombre_guardado',
        'ruta',
        'mime_type',
        'tamanio',
        'uploaded_by',
        'uploaded_at',
        'estado',
        'observaciones',
        'fecha_vigencia',
        'aprobado_por',
        'aprobado_at',
        'version',
        'archivo_anterior_id',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'aprobado_at' => 'datetime',
        'fecha_vigencia' => 'date',
        'tamanio' => 'integer',
        'version' => 'integer',
    ];

    /**
     * Relación: Un archivo pertenece a un proceso
     */
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    /**
     * Relación: Un archivo pertenece a una instancia de etapa
     */
    public function procesoEtapa(): BelongsTo
    {
        return $this->belongsTo(ProcesoEtapa::class);
    }

    /**
     * Relación: Un archivo pertenece a una etapa
     */
    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class);
    }

    /**
     * Relación: Usuario que subió el archivo
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Relación: Usuario que aprobó o rechazó el archivo
     */
    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    /**
     * Alias para compatibilidad con vistas que usan 'tipoArchivo'
     */
    public function tipoArchivo(): BelongsTo
    {
        // No existe tabla de tipos; devolvemos la relación de etapa como contenedor
        return $this->belongsTo(Etapa::class, 'etapa_id');
    }

    /**
     * Relación: Archivo anterior (si es una nueva versión)
     */
    public function archivoAnterior(): BelongsTo
    {
        return $this->belongsTo(ProcesoEtapaArchivo::class, 'archivo_anterior_id');
    }

    /**
     * Relación: Versiones posteriores de este archivo
     */
    public function versionesPosteriores()
    {
        return $this->hasMany(ProcesoEtapaArchivo::class, 'archivo_anterior_id');
    }

    /**
     * Relación: Alertas relacionadas con este archivo
     */
    public function alertas()
    {
        return $this->hasMany(Alerta::class, 'proceso_id', 'proceso_id');
    }

    /**
     * Obtener la URL pública del archivo
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->ruta);
    }

    /**
     * Obtener el tamaño formateado
     */
    public function getTamanioFormateadoAttribute(): string
    {
        $bytes = $this->tamanio;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Verificar si el archivo existe físicamente
     */
    public function existeFisicamente(): bool
    {
        return Storage::exists('public/' . $this->ruta);
    }

    /**
     * Eliminar archivo físico y registro
     */
    public function eliminarCompleto(): bool
    {
        $fullPath = 'public/' . $this->ruta;
        
        if (Storage::exists($fullPath)) {
            Storage::delete($fullPath);
        }
        
        return $this->delete();
    }

    /**
     * Scope: Archivos de un tipo específico
     */
    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo_archivo', $tipo);
    }

    /**
     * Scope: Archivos de una etapa específica
     */
    public function scopeDeEtapa($query, int $etapaId)
    {
        return $query->where('etapa_id', $etapaId);
    }
}
