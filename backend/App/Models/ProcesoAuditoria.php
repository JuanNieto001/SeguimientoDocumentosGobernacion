<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoAuditoria extends Model
{
    protected $table = 'proceso_auditoria';

    protected $fillable = [
        'proceso_id',
        'user_id',
        'accion',
        'descripcion',
        'fecha',
        'etapa_id',
        'ip_address',
        'user_agent',
        'datos_anteriores',
        'datos_nuevos',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
    ];

    /**
     * Relación: Una auditoría pertenece a un proceso
     */
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    /**
     * Relación: Una auditoría pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias en español para la relación user
     */
    public function usuario(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Relación: Una auditoría puede estar asociada a una etapa
     */
    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class);
    }

    /**
     * Registrar una acción de auditoría
     */
    public static function registrar(
        int $procesoId,
        string $accion,
        string $descripcion,
        int|string|null $etapaId = null,
        array|string|null $datosAnteriores = null,
        array|string|null $datosNuevos = null
    ): self {
        $etapaIdSanitized = is_numeric($etapaId) ? (int) $etapaId : null;
        $datosAnterioresSanitized = is_array($datosAnteriores)
            ? $datosAnteriores
            : ($datosAnteriores !== null ? ['detalle' => (string) $datosAnteriores] : null);
        $datosNuevosSanitized = is_array($datosNuevos)
            ? $datosNuevos
            : ($datosNuevos !== null ? ['detalle' => (string) $datosNuevos] : null);

        return self::create([
            'proceso_id' => $procesoId,
            'user_id' => auth()->id(),
            'accion' => $accion,
            'descripcion' => $descripcion,
            'fecha' => now(),
            'etapa_id' => $etapaIdSanitized,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'datos_anteriores' => $datosAnterioresSanitized,
            'datos_nuevos' => $datosNuevosSanitized,
        ]);
    }

    /**
     * Scope: Por tipo de acción
     */
    public function scopeAccion($query, string $accion)
    {
        return $query->where('accion', $accion);
    }

    /**
     * Scope: Por usuario (por ID)
     */
    public function scopePorUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Por etapa
     */
    public function scopeEtapa($query, int $etapaId)
    {
        return $query->where('etapa_id', $etapaId);
    }

    /**
     * Scope: Acciones recientes (últimas 24 horas)
     */
    public function scopeRecientes($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }
}
