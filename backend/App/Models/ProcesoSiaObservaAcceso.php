<?php
/**
 * Archivo: backend/App/Models/ProcesoSiaObservaAcceso.php
 * Proposito: Asignación de acceso por rol/usuario al repositorio SIA Observa.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoSiaObservaAcceso extends Model
{
    protected $table = 'proceso_sia_observa_accesos';

    protected $fillable = [
        'proceso_id',
        'asignacion_tipo',
        'acceso_clave',
        'role_name',
        'user_id',
        'puede_ver',
        'puede_subir',
        'activo',
        'asignado_por',
    ];

    protected $casts = [
        'puede_ver' => 'boolean',
        'puede_subir' => 'boolean',
        'activo' => 'boolean',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }

    public static function claveRol(string $roleName): string
    {
        return 'rol:' . strtolower(trim($roleName));
    }

    public static function claveUsuario(int $userId): string
    {
        return 'usuario:' . $userId;
    }
}
