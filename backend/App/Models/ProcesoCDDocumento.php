<?php
/**
 * Archivo: backend/App/Models/ProcesoCDDocumento.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoCDDocumento extends Model
{
    protected $table = 'proceso_cd_documentos';

    protected $fillable = [
        'proceso_cd_id',
        'tipo_documento',
        'nombre_archivo',
        'ruta_archivo',
        'mime_type',
        'tamano_bytes',
        'etapa',
        'estado_aprobacion',
        'observaciones',
        'subido_por',
        'aprobado_por',
        'fecha_aprobacion',
        'fecha_vencimiento',
        'es_obligatorio',
        'reemplaza_id',
    ];

    protected $casts = [
        'etapa'            => 'integer',
        'tamano_bytes'     => 'integer',
        'es_obligatorio'   => 'boolean',
        'fecha_aprobacion' => 'datetime',
        'fecha_vencimiento'=> 'date',
    ];

    // ── Relaciones ──
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(ProcesoContratacionDirecta::class, 'proceso_cd_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function documentoAnterior(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reemplaza_id');
    }

    // ── Helpers ──
    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento && now()->greaterThan($this->fecha_vencimiento);
    }

    public function estaAprobado(): bool
    {
        return $this->estado_aprobacion === 'aprobado';
    }

    public function estaPendiente(): bool
    {
        return $this->estado_aprobacion === 'pendiente';
    }

    // ── Scopes ──
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    public function scopePorEtapa($query, int $etapa)
    {
        return $query->where('etapa', $etapa);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado_aprobacion', 'aprobado');
    }

    public function scopeVigentes($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('fecha_vencimiento')
              ->orWhere('fecha_vencimiento', '>=', now());
        });
    }
}

