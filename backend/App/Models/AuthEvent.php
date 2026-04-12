<?php
/**
 * Archivo: backend/App/Models/AuthEvent.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthEvent extends Model
{
    protected $table = 'auth_events';

    protected $fillable = [
        'user_id',
        'email',
        'event_type',
        'ip_address',
        'user_agent',
        'guard',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    // ──────────────────────────────────────────────────────────────
    // RELACIONES
    // ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS ESTÁTICOS
    // ──────────────────────────────────────────────────────────────

    /**
     * Registrar un evento de autenticación fácilmente.
     */
    public static function registrar(
        string $eventType,
        ?int $userId = null,
        ?string $email = null,
        array $extra = []
    ): self {
        return self::create([
            'user_id'    => $userId,
            'email'      => $email,
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'guard'      => 'web',
            'extra'      => !empty($extra) ? $extra : null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // SCOPES
    // ──────────────────────────────────────────────────────────────

    public function scopeExitosos($query)
    {
        return $query->where('event_type', 'login_success');
    }

    public function scopeFallidos($query)
    {
        return $query->where('event_type', 'login_failed');
    }

    public function scopeUltimos($query, int $dias = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    // ──────────────────────────────────────────────────────────────
    // ACCESSORS
    // ──────────────────────────────────────────────────────────────

    public function getLabelEventoAttribute(): string
    {
        return match($this->event_type) {
            'login_success'  => 'Ingreso exitoso',
            'login_failed'   => 'Intento fallido',
            'logout'         => 'Cierre de sesión',
            'password_changed' => 'Contraseña cambiada',
            'password_reset' => 'Contraseña restablecida',
            'account_disabled' => 'Cuenta desactivada',
            'session_expired' => 'Sesión expirada',
            default          => ucfirst(str_replace('_', ' ', $this->event_type)),
        };
    }

    public function getColorEventoAttribute(): string
    {
        return match($this->event_type) {
            'login_success'  => '#15803d',
            'login_failed'   => '#dc2626',
            'logout'         => '#2563eb',
            'password_changed', 'password_reset' => '#ca8a04',
            'account_disabled' => '#7c3aed',
            default          => '#64748b',
        };
    }

    public function getBgEventoAttribute(): string
    {
        return match($this->event_type) {
            'login_success'  => '#dcfce7',
            'login_failed'   => '#fee2e2',
            'logout'         => '#dbeafe',
            'password_changed', 'password_reset' => '#fef9c3',
            'account_disabled' => '#ede9fe',
            default          => '#f1f5f9',
        };
    }
}

