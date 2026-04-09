<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcesoEtapa extends Model
{
    protected $table = 'proceso_etapas';

    protected $fillable = [
        'proceso_id',
        'etapa_id',
        'recibido',
        'recibido_por',
        'recibido_at',
        'enviado',
        'enviado_por',
        'enviado_at',
    ];

    protected $casts = [
        'recibido' => 'boolean',
        'enviado' => 'boolean',
        'recibido_at' => 'datetime',
        'enviado_at' => 'datetime',
    ];

    /**
     * Relación: Una instancia pertenece a un proceso
     */
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    /**
     * Relación: Una instancia pertenece a una etapa
     */
    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class);
    }

    /**
     * Relación: Usuario que recibió la etapa
     */
    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    /**
     * Relación: Usuario que envió la etapa
     */
    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por');
    }

    /**
     * Relación: Checks de esta instancia de etapa
     */
    public function checks(): HasMany
    {
        return $this->hasMany(ProcesoEtapaCheck::class);
    }

    /**
     * Relación: Archivos de esta instancia de etapa
     */
    public function archivos(): HasMany
    {
        return $this->hasMany(ProcesoEtapaArchivo::class);
    }

    /**
     * Verificar si todos los checks requeridos están marcados
     */
    public function checksRequeridosCompletos(): bool
    {
        return $this->checks()
            ->join('etapa_items', 'etapa_items.id', '=', 'proceso_etapa_checks.etapa_item_id')
            ->where('etapa_items.requerido', true)
            ->where('proceso_etapa_checks.checked', false)
            ->count() === 0;
    }

    /**
     * Verificar si la etapa puede ser enviada
     */
    public function puedeEnviar(): bool
    {
        // Unidad Solicitante no requiere recibir
        if ($this->etapa->esUnidadSolicitante()) {
            // Validar archivos requeridos (implementar según necesidad)
            return true;
        }

        // Otras áreas: debe estar recibido y checks completos
        return $this->recibido && $this->checksRequeridosCompletos();
    }

    /**
     * Scope: Etapas recibidas
     */
    public function scopeRecibidas($query)
    {
        return $query->where('recibido', true);
    }

    /**
     * Scope: Etapas enviadas
     */
    public function scopeEnviadas($query)
    {
        return $query->where('enviado', true);
    }

    /**
     * Scope: Etapas pendientes (recibidas pero no enviadas)
     */
    public function scopePendientes($query)
    {
        return $query->where('recibido', true)->where('enviado', false);
    }

    /**
     * Calcular días transcurridos en esta etapa
     */
    public function diasEnEtapa(): int
    {
        if (!$this->recibido_at) {
            return 0;
        }

        $fechaFin = $this->enviado_at ?? now();
        return $this->recibido_at->diffInDays($fechaFin);
    }

    /**
     * Calcular si está retrasada comparado con días estimados
     */
    public function estaRetrasada(): bool
    {
        if (!$this->etapa || !$this->etapa->dias_estimados) {
            return false;
        }

        return $this->diasEnEtapa() > $this->etapa->dias_estimados;
    }

    /**
     * Obtener días de retraso (0 si no está retrasada)
     */
    public function diasRetraso(): int
    {
        if (!$this->estaRetrasada()) {
            return 0;
        }

        return $this->diasEnEtapa() - $this->etapa->dias_estimados;
    }

    /**
     * Obtener porcentaje de tiempo utilizado
     */
    public function porcentajeTiempoUtilizado(): float
    {
        if (!$this->etapa || !$this->etapa->dias_estimados || $this->etapa->dias_estimados == 0) {
            return 0;
        }

        return round(($this->diasEnEtapa() / $this->etapa->dias_estimados) * 100, 2);
    }
}
