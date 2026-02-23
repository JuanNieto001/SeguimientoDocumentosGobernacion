<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'secretaria_id',
        'unidad_id',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Secretaría a la que pertenece el usuario.
     */
    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    /**
     * Unidad a la que pertenece el usuario.
     */
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * ¿Es Administrador General?
     */
    public function esAdminGeneral(): bool
    {
        return $this->hasRole('admin_general');
    }

    /**
     * ¿Es Administrador de Secretaría?
     */
    public function esAdminSecretaria(): bool
    {
        return $this->hasRole('admin_secretaria');
    }

    /**
     * ¿Puede ver datos de una secretaría específica?
     */
    public function puedeVerSecretaria(int $secretariaId): bool
    {
        if ($this->esAdminGeneral()) {
            return true;
        }

        return $this->secretaria_id === $secretariaId;
    }

    /**
     * ¿Puede ver datos de una unidad específica?
     */
    public function puedeVerUnidad(int $unidadId): bool
    {
        if ($this->esAdminGeneral()) {
            return true;
        }

        if ($this->esAdminSecretaria()) {
            $unidad = Unidad::find($unidadId);
            return $unidad && $unidad->secretaria_id === $this->secretaria_id;
        }

        return $this->unidad_id === $unidadId;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDeSecretaria($query, int $secretariaId)
    {
        return $query->where('secretaria_id', $secretariaId);
    }

    public function scopeDeUnidad($query, int $unidadId)
    {
        return $query->where('unidad_id', $unidadId);
    }
}
