<?php
/**
 * Archivo: backend/App/Http/Controllers/ProcesoSiaObservaController.php
 * Proposito: Gestion interna de repositorio final para SIA Observa (sin UI visible).
 */

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\ProcesoEtapaArchivo;
use App\Models\ProcesoSiaObservaAcceso;
use App\Models\ProcesoSiaObservaArchivo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ProcesoSiaObservaController extends Controller
{
    /**
     * Listar documentos almacenados para SIA Observa.
     */
    public function index(int $proceso)
    {
        $proceso = $this->cargarProceso($proceso);

        abort_unless($this->usuarioPuedeVer($proceso), 403, 'No tienes permiso para consultar este repositorio.');

        $archivos = $proceso->siaObservaArchivos()
            ->with('subidoPor:id,name,email')
            ->orderByDesc('created_at')
            ->get();

        $accesos = collect();
        if ($this->usuarioPuedeAsignar()) {
            $accesos = $proceso->siaObservaAccesos()
                ->orderByDesc('id')
                ->get();
        }

        return response()->json([
            'ok' => true,
            'proceso' => [
                'id' => $proceso->id,
                'codigo' => $proceso->codigo,
                'estado' => $proceso->estado,
                'etapa_actual' => optional($proceso->etapaActual)->orden,
            ],
            'archivos' => $archivos,
            'accesos' => $accesos,
        ]);
    }

    /**
     * Subir documento final al repositorio SIA Observa.
     */
    public function store(Request $request, int $proceso)
    {
        $proceso = $this->cargarProceso($proceso);

        abort_unless($this->usuarioPuedeSubir($proceso), 403, 'No tienes permiso para subir documentos a este repositorio.');

        $validated = $request->validate([
            'archivo' => 'required|file|max:20480',
            'tipo_documento' => 'nullable|string|max:120',
            'descripcion' => 'nullable|string|max:2000',
        ]);

        $file = $request->file('archivo');
        $tipoDocumento = trim((string) ($validated['tipo_documento'] ?? 'documento_final'));
        $extension = $file->getClientOriginalExtension();
        $nombreGuardado = Str::uuid() . ($extension ? '.' . $extension : '');
        $ruta = "procesos/{$proceso->id}/sia-observa/{$nombreGuardado}";

        $file->storeAs('public/' . dirname($ruta), basename($ruta));

        $versionActual = ProcesoSiaObservaArchivo::query()
            ->where('proceso_id', $proceso->id)
            ->where('tipo_documento', $tipoDocumento)
            ->max('version');

        $archivo = DB::transaction(function () use ($proceso, $file, $ruta, $nombreGuardado, $tipoDocumento, $validated, $versionActual) {
            $archivo = ProcesoSiaObservaArchivo::create([
                'proceso_id' => $proceso->id,
                'tipo_documento' => $tipoDocumento,
                'nombre_original' => $file->getClientOriginalName(),
                'nombre_guardado' => $nombreGuardado,
                'ruta' => $ruta,
                'mime_type' => $file->getMimeType(),
                'tamanio' => $file->getSize(),
                'version' => ((int) $versionActual) + 1,
                'descripcion' => $validated['descripcion'] ?? null,
                'subido_por' => auth()->id(),
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'sia_observa_archivo_subido',
                "Documento final SIA Observa subido: {$archivo->nombre_original}"
            );

            return $archivo;
        });

        return response()->json([
            'ok' => true,
            'mensaje' => 'Documento almacenado correctamente.',
            'archivo' => $archivo,
        ], 201);
    }

    /**
     * Descargar un documento almacenado en el repositorio SIA Observa.
     */
    public function descargarArchivo(ProcesoSiaObservaArchivo $archivo)
    {
        $proceso = $this->cargarProceso($archivo->proceso_id);
        abort_unless($this->usuarioPuedeVer($proceso), 403, 'No tienes permiso para descargar este documento.');

        $fullPath = 'public/' . $archivo->ruta;
        abort_unless(Storage::exists($fullPath), 404, 'El archivo no existe en el servidor.');

        ProcesoAuditoria::registrar(
            $proceso->id,
            'sia_observa_archivo_descargado',
            "Documento SIA Observa descargado: {$archivo->nombre_original}"
        );

        return Storage::download($fullPath, $archivo->nombre_original);
    }

    /**
     * Descargar un ZIP con documentos finales del flujo.
     */
    public function descargarPaqueteFinal(int $proceso)
    {
        $proceso = $this->cargarProceso($proceso);
        abort_unless($this->usuarioPuedeVer($proceso), 403, 'No tienes permiso para descargar el paquete final.');

        $archivos = ProcesoEtapaArchivo::query()
            ->where('proceso_id', $proceso->id)
            ->where('estado', '!=', 'rechazado')
            ->orderBy('etapa_id')
            ->orderBy('created_at')
            ->get();

        abort_if($archivos->isEmpty(), 404, 'No hay documentos disponibles para generar el paquete final.');
        abort_unless(class_exists(ZipArchive::class), 500, 'La extensión ZIP no está disponible en el servidor.');

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . DIRECTORY_SEPARATOR . 'sia_observa_' . $proceso->id . '_' . time() . '.zip';
        $zip = new ZipArchive();

        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        abort_unless($opened === true, 500, 'No fue posible construir el paquete ZIP.');

        foreach ($archivos as $item) {
            $fullPath = storage_path('app/public/' . $item->ruta);
            if (!is_file($fullPath)) {
                continue;
            }

            $entryName = sprintf(
                'etapa_%s/%s',
                (string) $item->etapa_id,
                $this->limpiarNombreArchivo($item->nombre_original)
            );

            $zip->addFile($fullPath, $entryName);
        }

        $zip->close();

        ProcesoAuditoria::registrar(
            $proceso->id,
            'sia_observa_paquete_descargado',
            'Paquete final de documentos descargado para carga externa (SIA Observa).'
        );

        $nombreDescarga = 'SIA_OBSERVA_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $proceso->codigo) . '.zip';

        return response()->download($zipPath, $nombreDescarga)->deleteFileAfterSend(true);
    }

    /**
     * Asignar acceso por rol al repositorio SIA Observa.
     */
    public function asignarRol(Request $request, int $proceso)
    {
        $proceso = $this->cargarProceso($proceso);
        abort_unless($this->usuarioPuedeAsignar(), 403, 'No tienes permiso para asignar accesos.');

        $validated = $request->validate([
            'role_name' => 'required|string|exists:roles,name',
            'puede_ver' => 'nullable|boolean',
            'puede_subir' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);

        $puedeSubir = (bool) ($validated['puede_subir'] ?? false);
        $puedeVer = (bool) ($validated['puede_ver'] ?? true) || $puedeSubir;

        $acceso = ProcesoSiaObservaAcceso::updateOrCreate(
            [
                'proceso_id' => $proceso->id,
                'acceso_clave' => ProcesoSiaObservaAcceso::claveRol($validated['role_name']),
            ],
            [
                'asignacion_tipo' => 'rol',
                'role_name' => $validated['role_name'],
                'user_id' => null,
                'puede_ver' => $puedeVer,
                'puede_subir' => $puedeSubir,
                'activo' => (bool) ($validated['activo'] ?? true),
                'asignado_por' => auth()->id(),
            ]
        );

        ProcesoAuditoria::registrar(
            $proceso->id,
            'sia_observa_acceso_rol_asignado',
            "Acceso SIA Observa asignado al rol {$validated['role_name']}"
        );

        return response()->json([
            'ok' => true,
            'mensaje' => 'Acceso por rol actualizado correctamente.',
            'acceso' => $acceso,
        ]);
    }

    /**
     * Asignar acceso por usuario al repositorio SIA Observa.
     */
    public function asignarUsuario(Request $request, int $proceso)
    {
        $proceso = $this->cargarProceso($proceso);
        abort_unless($this->usuarioPuedeAsignar(), 403, 'No tienes permiso para asignar accesos.');

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'puede_ver' => 'nullable|boolean',
            'puede_subir' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);

        $usuario = User::findOrFail((int) $validated['user_id']);
        $puedeSubir = (bool) ($validated['puede_subir'] ?? false);
        $puedeVer = (bool) ($validated['puede_ver'] ?? true) || $puedeSubir;

        $acceso = ProcesoSiaObservaAcceso::updateOrCreate(
            [
                'proceso_id' => $proceso->id,
                'acceso_clave' => ProcesoSiaObservaAcceso::claveUsuario($usuario->id),
            ],
            [
                'asignacion_tipo' => 'usuario',
                'role_name' => null,
                'user_id' => $usuario->id,
                'puede_ver' => $puedeVer,
                'puede_subir' => $puedeSubir,
                'activo' => (bool) ($validated['activo'] ?? true),
                'asignado_por' => auth()->id(),
            ]
        );

        ProcesoAuditoria::registrar(
            $proceso->id,
            'sia_observa_acceso_usuario_asignado',
            "Acceso SIA Observa asignado al usuario {$usuario->email}"
        );

        return response()->json([
            'ok' => true,
            'mensaje' => 'Acceso por usuario actualizado correctamente.',
            'acceso' => $acceso,
        ]);
    }

    private function cargarProceso(int $procesoId): Proceso
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($procesoId);
        $this->validarProcesoCasiFinalizado($proceso);

        return $proceso;
    }

    private function validarProcesoCasiFinalizado(Proceso $proceso): void
    {
        $ordenEtapa = (int) (optional($proceso->etapaActual)->orden ?? 0);
        $esFinalizado = strtoupper((string) $proceso->estado) === 'FINALIZADO';

        abort_unless(
            $esFinalizado || $ordenEtapa >= 8,
            422,
            'El repositorio SIA Observa solo se habilita cuando el proceso está en etapa final.'
        );
    }

    private function usuarioPuedeVer(Proceso $proceso): bool
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'admin_general'])) {
            return true;
        }

        if ((int) ($proceso->supervisor_id ?? 0) === (int) $user->id) {
            return true;
        }

        if ($user->hasRole('abogado_unidad') && (int) $user->unidad_id > 0 && (int) $user->unidad_id === (int) $proceso->unidad_origen_id) {
            return true;
        }

        if ($user->hasRole('jefe_unidad') && (int) $user->unidad_id > 0 && (int) $user->unidad_id === (int) $proceso->unidad_origen_id) {
            return true;
        }

        if ($user->hasRole('secretario') && (int) $user->secretaria_id > 0 && (int) $user->secretaria_id === (int) $proceso->secretaria_origen_id) {
            return true;
        }

        return $this->tieneAccesoAsignado($proceso, false);
    }

    private function usuarioPuedeSubir(Proceso $proceso): bool
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'admin_general'])) {
            return true;
        }

        if ($user->hasRole('abogado_unidad') && (int) $user->unidad_id > 0 && (int) $user->unidad_id === (int) $proceso->unidad_origen_id) {
            return true;
        }

        return $this->tieneAccesoAsignado($proceso, true);
    }

    private function usuarioPuedeAsignar(): bool
    {
        $user = auth()->user();

        return $user->hasRole(['admin', 'admin_general'])
            || $user->can('sia_observa.asignar');
    }

    private function tieneAccesoAsignado(Proceso $proceso, bool $requiereSubida): bool
    {
        $user = auth()->user();
        $roles = $user->getRoleNames()->toArray();

        $query = ProcesoSiaObservaAcceso::query()
            ->where('proceso_id', $proceso->id)
            ->where('activo', true)
            ->where(function ($q) use ($user, $roles) {
                $q->where(function ($uq) use ($user) {
                    $uq->where('asignacion_tipo', 'usuario')
                        ->where('user_id', $user->id);
                });

                if (!empty($roles)) {
                    $q->orWhere(function ($rq) use ($roles) {
                        $rq->where('asignacion_tipo', 'rol')
                            ->whereIn('role_name', $roles);
                    });
                }
            });

        if ($requiereSubida) {
            $query->where('puede_subir', true);
        } else {
            $query->where('puede_ver', true);
        }

        return $query->exists();
    }

    private function limpiarNombreArchivo(string $nombre): string
    {
        $nombreLimpio = preg_replace('/[^A-Za-z0-9._-]/', '_', $nombre);
        $nombreLimpio = trim((string) $nombreLimpio, '_');

        if ($nombreLimpio === '') {
            return 'archivo_' . time();
        }

        return $nombreLimpio;
    }
}
