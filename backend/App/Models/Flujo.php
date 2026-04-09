<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Flujo – Flujo de contratación configurable por Secretaría.
 *
 * Cada Secretaría puede tener múltiples flujos (CD-PN, LP, MC, etc.)
 * con pasos, orden y responsables diferentes.
 */
class Flujo extends Model
{
    protected $table = 'flujos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo_contratacion',
        'secretaria_id',
        'version_activa_id',
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

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function versiones(): HasMany
    {
        return $this->hasMany(FlujoVersion::class)->orderByDesc('numero_version');
    }

    public function versionActiva(): BelongsTo
    {
        return $this->belongsTo(FlujoVersion::class, 'version_activa_id');
    }

    public function instancias(): HasMany
    {
        return $this->hasMany(FlujoInstancia::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Obtener los pasos ordenados de la versión activa.
     */
    public function pasosOrdenados()
    {
        if (!$this->version_activa_id) {
            return collect();
        }

        return FlujoPaso::where('flujo_version_id', $this->version_activa_id)
            ->where('activo', true)
            ->orderBy('orden')
            ->with(['catalogoPaso', 'documentos', 'condiciones', 'responsables'])
            ->get();
    }

    /**
     * Crear nueva versión del flujo.
     */
    public function crearVersion(int $userId, ?string $motivo = null): FlujoVersion
    {
        $ultimaVersion = $this->versiones()->max('numero_version') ?? 0;

        return FlujoVersion::create([
            'flujo_id'        => $this->id,
            'numero_version'  => $ultimaVersion + 1,
            'motivo_cambio'   => $motivo,
            'estado'          => 'borrador',
            'creado_por'      => $userId,
        ]);
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

    public function scopePorSecretaria($query, int $secretariaId)
    {
        return $query->where('secretaria_id', $secretariaId);
    }

    public function scopePorTipoContratacion($query, string $tipo)
    {
        return $query->where('tipo_contratacion', $tipo);
    }
}
