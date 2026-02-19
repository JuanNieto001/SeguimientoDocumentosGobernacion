<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoDocumentoSolicitado extends Model
{
    protected $table = 'proceso_documentos_solicitados';

    protected $fillable = [
        'proceso_id',
        'etapa_id',
        'tipo_documento',
        'nombre_documento',
        'area_responsable_rol',
        'area_responsable_nombre',
        'secretaria_responsable_id',
        'unidad_responsable_id',
        'estado',
        'depende_de_solicitud_id',
        'puede_subir',
        'solicitado_por',
        'solicitado_at',
        'subido_por',
        'subido_at',
        'archivo_id',
        'observaciones',
        'motivo_rechazo',
    ];

    protected $casts = [
        'solicitado_at' => 'datetime',
        'subido_at' => 'datetime',
        'puede_subir' => 'boolean',
    ];

    // Relaciones
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class);
    }

    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(ProcesoEtapaArchivo::class, 'archivo_id');
    }

    public function dependeDe(): BelongsTo
    {
        return $this->belongsTo(ProcesoDocumentoSolicitado::class, 'depende_de_solicitud_id');
    }

    public function secretariaResponsable(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class, 'secretaria_responsable_id');
    }

    public function unidadResponsable(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_responsable_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeSubidos($query)
    {
        return $query->where('estado', 'subido');
    }

    public function scopePorArea($query, string $areaRol)
    {
        return $query->where('area_responsable_rol', $areaRol);
    }

    public function scopePorProceso($query, int $procesoId)
    {
        return $query->where('proceso_id', $procesoId);
    }

    // Métodos de utilidad
    public function marcarComoSubido(int $usuarioId, int $archivoId): void
    {
        $this->update([
            'estado' => 'subido',
            'subido_por' => $usuarioId,
            'subido_at' => now(),
            'archivo_id' => $archivoId,
        ]);

        // Actualizar dependencias: si otros documentos dependen de este, habilitarlos
        $this->habilitarDocumentosDependientes();
    }

    public function habilitarDocumentosDependientes(): void
    {
        ProcesoDocumentoSolicitado::where('depende_de_solicitud_id', $this->id)
            ->where('estado', 'pendiente')
            ->update(['puede_subir' => true]);
    }

    public function marcarComoRechazado(string $motivo): void
    {
        $this->update([
            'estado' => 'rechazado',
            'motivo_rechazo' => $motivo,
        ]);
    }

    public function puedeSubirArchivo(): bool
    {
        // Solo puede subir si:
        // 1. Estado es pendiente
        // 2. puede_subir es true (no depende de otro documento)
        return $this->estado === 'pendiente' && $this->puede_subir;
    }
}
