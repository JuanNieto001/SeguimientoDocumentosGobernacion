<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'proceso_id',
        'user_id',
        'tipo',
        'prioridad',
        'titulo',
        'mensaje',
        'area_responsable',
        'leida',
        'leida_at',
        'accion_url',
        'metadata',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'leida_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relación: Una alerta pertenece a un proceso
     */
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    /**
     * Relación: Una alerta pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marcar alerta como leída
     */
    public function marcarLeida(): void
    {
        $this->update([
            'leida' => true,
            'leida_at' => now(),
        ]);
    }

    /**
     * Crear una nueva alerta
     */
    public static function crear(
        int $userId,
        string $tipo,
        string $titulo,
        string $mensaje,
        ?int $procesoId = null,
        string $prioridad = 'media',
        ?string $accionUrl = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'proceso_id' => $procesoId,
            'tipo' => $tipo,
            'prioridad' => $prioridad,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'accion_url' => $accionUrl,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Scope: Alertas no leídas
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    /**
     * Scope: Alertas por prioridad
     */
    public function scopePrioridad($query, string $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    /**
     * Scope: Alertas críticas
     */
    public function scopeCriticas($query)
    {
        return $query->where('prioridad', 'alta');
    }

    /**
     * Scope: Alertas de un usuario específico
     */
    public function scopeParaUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Alertas recientes (últimas 48 horas)
     */
    public function scopeRecientes($query)
    {
        return $query->where('created_at', '>=', now()->subDays(2));
    }
}
