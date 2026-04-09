<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FlujoVersion – Versionado de flujos.
 *
 * Permite mantener historial de cambios y que procesos en curso
 * sigan funcionando con la versión con la que se iniciaron.
 */
class FlujoVersion extends Model
{
    protected $table = 'flujo_versiones';

    protected $fillable = [
        'flujo_id',
        'numero_version',
        'motivo_cambio',
        'estado',
        'creado_por',
        'publicada_at',
    ];

    protected $casts = [
        'publicada_at' => 'datetime',
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

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function pasos(): HasMany
    {
        return $this->hasMany(FlujoPaso::class, 'flujo_version_id')->orderBy('orden');
    }

    public function instancias(): HasMany
    {
        return $this->hasMany(FlujoInstancia::class, 'flujo_version_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Publicar esta versión (la convierte en activa).
     */
    public function publicar(): void
    {
        // Archivar la versión activa anterior
        $this->flujo->versiones()
            ->where('estado', 'activa')
            ->where('id', '!=', $this->id)
            ->update(['estado' => 'archivada']);

        // Activar esta versión
        $this->update([
            'estado'       => 'activa',
            'publicada_at' => now(),
        ]);

        // Actualizar referencia en el flujo
        $this->flujo->update(['version_activa_id' => $this->id]);
    }

    /**
     * Duplicar pasos de otra versión a esta.
     */
    public function duplicarPasosDe(FlujoVersion $origen): void
    {
        foreach ($origen->pasos()->with(['condiciones', 'documentos', 'responsables'])->get() as $paso) {
            $nuevoPaso = $paso->replicate(['id', 'created_at', 'updated_at']);
            $nuevoPaso->flujo_version_id = $this->id;
            $nuevoPaso->save();

            // Duplicar condiciones
            foreach ($paso->condiciones as $cond) {
                $nuevaCond = $cond->replicate(['id', 'created_at', 'updated_at']);
                $nuevaCond->flujo_paso_id = $nuevoPaso->id;
                $nuevaCond->save();
            }

            // Duplicar documentos
            foreach ($paso->documentos as $doc) {
                $nuevoDoc = $doc->replicate(['id', 'created_at', 'updated_at']);
                $nuevoDoc->flujo_paso_id = $nuevoPaso->id;
                $nuevoDoc->save();
            }

            // Duplicar responsables
            foreach ($paso->responsables as $resp) {
                $nuevoResp = $resp->replicate(['id', 'created_at', 'updated_at']);
                $nuevoResp->flujo_paso_id = $nuevoPaso->id;
                $nuevoResp->save();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActiva($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopeBorrador($query)
    {
        return $query->where('estado', 'borrador');
    }
}
