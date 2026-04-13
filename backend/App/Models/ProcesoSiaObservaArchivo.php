<?php
/**
 * Archivo: backend/App/Models/ProcesoSiaObservaArchivo.php
 * Proposito: Modelo de almacenamiento de archivos finales para SIA Observa.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProcesoSiaObservaArchivo extends Model
{
    protected $table = 'proceso_sia_observa_archivos';

    protected $fillable = [
        'proceso_id',
        'tipo_documento',
        'nombre_original',
        'nombre_guardado',
        'ruta',
        'mime_type',
        'tamanio',
        'version',
        'descripcion',
        'subido_por',
    ];

    protected $casts = [
        'tamanio' => 'integer',
        'version' => 'integer',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->ruta);
    }
}
