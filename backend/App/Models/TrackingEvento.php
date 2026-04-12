<?php
/**
 * Archivo: backend/App/Models/TrackingEvento.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvento extends Model
{
    protected $table = 'tracking_eventos';

    protected $fillable = [
        'codigo_proceso',
        'proceso_id',
        'user_id',
        'tipo',
        'area_origen',
        'area_destino',
        'responsable_nombre',
        'observaciones',
        'ip_address',
    ];

    // ──────────────────────────────────────────────────────────────
    // RELACIONES
    // ──────────────────────────────────────────────────────────────

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ──────────────────────────────────────────────────────────────
    // ACCESSORS
    // ──────────────────────────────────────────────────────────────

    public function getLabelTipoAttribute(): string
    {
        return match($this->tipo) {
            'entrega'   => 'Entrega',
            'recepcion' => 'Recepción',
            'consulta'  => 'Consulta',
            default     => ucfirst($this->tipo),
        };
    }

    public function getColorTipoAttribute(): string
    {
        return match($this->tipo) {
            'entrega'   => '#1d4ed8',
            'recepcion' => '#15803d',
            'consulta'  => '#ca8a04',
            default     => '#64748b',
        };
    }

    public function getBgTipoAttribute(): string
    {
        return match($this->tipo) {
            'entrega'   => '#dbeafe',
            'recepcion' => '#dcfce7',
            'consulta'  => '#fef9c3',
            default     => '#f1f5f9',
        };
    }
}

