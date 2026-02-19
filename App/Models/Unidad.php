<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidad extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected $fillable = [
        'nombre',
        'secretaria_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Secretaría a la que pertenece esta unidad.
     */
    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    /**
     * Usuarios asignados a esta unidad.
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDeSecretaria($query, int $secretariaId)
    {
        return $query->where('secretaria_id', $secretariaId);
    }
}
