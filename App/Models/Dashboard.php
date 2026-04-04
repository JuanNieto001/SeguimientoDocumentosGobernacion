<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dashboard extends Model
{
    protected $fillable = [
        'name',
        'description',
        'widgets',
        'created_by',
        'active'
    ];

    protected $casts = [
        'widgets' => 'array',
        'active' => 'boolean'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userAssignments(): HasMany
    {
        return $this->hasMany(DashboardAssignment::class);
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(DashboardRoleAssignment::class);
    }

    public function secretariaAssignments(): HasMany
    {
        return $this->hasMany(DashboardSecretariaAssignment::class);
    }

    /**
     * Verifica si un usuario puede ver este dashboard
     */
    public function canBeViewedBy(User $user): bool
    {
        if (!$this->active) return false;

        // Admin siempre puede ver todos
        if ($user->hasRole(['admin', 'admin_general'])) {
            return true;
        }

        // Verificar asignación directa por usuario
        if ($this->userAssignments()->where('user_id', $user->id)->where('active', true)->exists()) {
            return true;
        }

        // Verificar asignación por rol
        $userRoles = $user->roles->pluck('name')->toArray();
        if ($this->roleAssignments()->whereIn('role_name', $userRoles)->where('active', true)->exists()) {
            return true;
        }

        // Verificar asignación por secretaría
        if ($user->secretaria_id) {
            if ($this->secretariaAssignments()->where('secretaria_id', $user->secretaria_id)->where('active', true)->exists()) {
                return true;
            }
        }

        return false;
    }
}