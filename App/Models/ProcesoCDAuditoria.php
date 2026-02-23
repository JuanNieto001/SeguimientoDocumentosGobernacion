<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoCDAuditoria extends Model
{
    protected $table = 'proceso_cd_auditoria';

    protected $fillable = [
        'proceso_cd_id',
        'user_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'descripcion',
        'datos_extra',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'datos_extra' => 'array',
    ];

    // ── Relaciones ──
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(ProcesoContratacionDirecta::class, 'proceso_cd_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Helpers estáticos ──

    /**
     * Registra un cambio de estado.
     */
    public static function registrarCambioEstado(
        ProcesoContratacionDirecta $proceso,
        string $estadoAnterior,
        string $estadoNuevo,
        ?User $user = null,
        ?string $descripcion = null,
        array $extra = []
    ): self {
        return static::create([
            'proceso_cd_id'  => $proceso->id,
            'user_id'        => $user?->id ?? auth()->id(),
            'accion'         => 'cambio_estado',
            'estado_anterior'=> $estadoAnterior,
            'estado_nuevo'   => $estadoNuevo,
            'descripcion'    => $descripcion ?? "Estado cambiado de '{$estadoAnterior}' a '{$estadoNuevo}'",
            'datos_extra'    => $extra ?: null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }

    /**
     * Registra una acción genérica (subir documento, aprobar, etc.).
     */
    public static function registrarAccion(
        ProcesoContratacionDirecta $proceso,
        string $accion,
        string $descripcion,
        array $extra = [],
        ?User $user = null
    ): self {
        return static::create([
            'proceso_cd_id' => $proceso->id,
            'user_id'       => $user?->id ?? auth()->id(),
            'accion'        => $accion,
            'descripcion'   => $descripcion,
            'datos_extra'   => $extra ?: null,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
        ]);
    }
}
