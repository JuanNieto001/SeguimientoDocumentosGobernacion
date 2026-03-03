<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FlujoInstancia – Instancia de ejecución de un flujo (un proceso en curso).
 *
 * Cuando se inicia un proceso de contratación, se crea una instancia
 * que queda ligada a la versión del flujo vigente en ese momento.
 */
class FlujoInstancia extends Model
{
    protected $table = 'flujo_instancias';

    protected $fillable = [
        'codigo_proceso',
        'flujo_id',
        'flujo_version_id',
        'secretaria_id',
        'unidad_id',
        'objeto',
        'monto_estimado',
        'plazo_dias',
        'metadata',
        'estado',
        'paso_actual_id',
        'creado_por',
        'iniciado_at',
        'completado_at',
    ];

    protected $casts = [
        'monto_estimado' => 'decimal:2',
        'metadata'       => 'array',
        'iniciado_at'    => 'datetime',
        'completado_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function flujo(): BelongsTo
    {
        return $this->belongsTo(Flujo::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(FlujoVersion::class, 'flujo_version_id');
    }

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function pasoActual(): BelongsTo
    {
        return $this->belongsTo(FlujoPaso::class, 'paso_actual_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function pasos(): HasMany
    {
        return $this->hasMany(FlujoInstanciaPaso::class, 'instancia_id')->orderBy('orden');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Iniciar el flujo: crear todas las instancias de paso y marcarlo en curso.
     */
    public function iniciar(): void
    {
        $flujoVersion = $this->version;
        $pasosConfig  = $flujoVersion->pasos()->activos()->ordenado()->get();
        $datosEval    = [
            'monto_estimado' => $this->monto_estimado,
            'plazo_dias'     => $this->plazo_dias,
            ...(is_array($this->metadata) ? $this->metadata : []),
        ];

        foreach ($pasosConfig as $paso) {
            $omitido = false;
            $acciones = $paso->evaluarCondiciones($datosEval);

            foreach ($acciones as $accion) {
                if ($accion['accion'] === 'omitir') {
                    $omitido = true;
                    break;
                }
            }

            FlujoInstanciaPaso::create([
                'instancia_id'          => $this->id,
                'flujo_paso_id'         => $paso->id,
                'orden'                 => $paso->orden,
                'estado'                => $omitido ? 'omitido' : 'pendiente',
                'omitido_por_condicion' => $omitido,
            ]);
        }

        // Obtener primer paso no omitido
        $primerPaso = $this->pasos()
            ->where('estado', '!=', 'omitido')
            ->orderBy('orden')
            ->first();

        $this->update([
            'estado'        => 'en_curso',
            'paso_actual_id' => $primerPaso?->flujo_paso_id,
            'iniciado_at'   => now(),
        ]);

        if ($primerPaso) {
            $primerPaso->update(['estado' => 'en_progreso']);
        }
    }

    /**
     * Avanzar al siguiente paso.
     */
    public function avanzar(?int $userId = null, ?string $observaciones = null): ?FlujoInstanciaPaso
    {
        $pasoActual = $this->pasos()
            ->where('estado', 'en_progreso')
            ->first();

        if (!$pasoActual) {
            return null;
        }

        // Completar paso actual
        $pasoActual->update([
            'estado'        => 'completado',
            'completado_por' => $userId,
            'completado_at' => now(),
            'observaciones' => $observaciones,
        ]);

        // Buscar siguiente paso no omitido
        $siguientePaso = $this->pasos()
            ->where('orden', '>', $pasoActual->orden)
            ->where('estado', '!=', 'omitido')
            ->orderBy('orden')
            ->first();

        if ($siguientePaso) {
            $siguientePaso->update(['estado' => 'en_progreso']);
            $this->update(['paso_actual_id' => $siguientePaso->flujo_paso_id]);
        } else {
            // Flujo completado
            $this->update([
                'estado'        => 'completado',
                'paso_actual_id' => null,
                'completado_at' => now(),
            ]);
        }

        return $siguientePaso;
    }

    /**
     * Devolver a un paso anterior.
     */
    public function devolver(int $ordenDestino, int $userId, string $motivo): ?FlujoInstanciaPaso
    {
        $pasoActual = $this->pasos()
            ->where('estado', 'en_progreso')
            ->first();

        if (!$pasoActual) {
            return null;
        }

        // Marcar actual como devuelto
        $pasoActual->update([
            'estado'            => 'devuelto',
            'devuelto_por'      => $userId,
            'devuelto_at'       => now(),
            'motivo_devolucion' => $motivo,
        ]);

        // Reactivar paso destino
        $pasoDestino = $this->pasos()
            ->where('orden', $ordenDestino)
            ->first();

        if ($pasoDestino) {
            $pasoDestino->update([
                'estado'       => 'en_progreso',
                'completado_at' => null,
                'completado_por' => null,
            ]);
            $this->update([
                'estado'         => 'devuelto',
                'paso_actual_id' => $pasoDestino->flujo_paso_id,
            ]);
        }

        return $pasoDestino;
    }

    /**
     * Calcular progreso del flujo (porcentaje).
     */
    public function getProgresoAttribute(): float
    {
        $total      = $this->pasos()->where('estado', '!=', 'omitido')->count();
        $completados = $this->pasos()->where('estado', 'completado')->count();

        return $total > 0 ? round(($completados / $total) * 100, 1) : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeEnCurso($query)
    {
        return $query->where('estado', 'en_curso');
    }

    public function scopePorSecretaria($query, int $secretariaId)
    {
        return $query->where('secretaria_id', $secretariaId);
    }
}
