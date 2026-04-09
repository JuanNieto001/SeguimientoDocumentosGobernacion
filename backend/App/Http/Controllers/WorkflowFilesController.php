<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ProcesoAuditoria;
use App\Models\ProcesoEtapaArchivo;
use App\Services\AlertaService;

class WorkflowFilesController extends Controller
{
    /**
     * Subir archivo a una etapa de un proceso
     * Solo admin o el área actual puede subir
     */
    public function store(Request $request, int $proceso)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        $user = auth()->user();
        $userRoles = $user->getRoleNames()->toArray();

        // ✅ Verificar si el usuario está subiendo un documento solicitado (desde otra área)
        $esSubidaSolicitud = DB::table('proceso_documentos_solicitados')
            ->where('proceso_id', $proceso->id)
            ->whereIn('area_responsable_rol', $userRoles)
            ->whereIn('estado', ['pendiente', 'bloqueado'])
            ->exists();

        // Si NO es una subida de solicitud, verificar que la etapa no haya sido enviada
        if (!$esSubidaSolicitud) {
            $procesoEtapa = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            if ($procesoEtapa && $procesoEtapa->enviado) {
                return back()->withErrors(['archivo' => 'No puedes subir archivos porque esta etapa ya fue enviada.']);
            }
        }

        $request->validate([
            'archivo' => ['required', 'file', 'max:10240'], // 10MB máximo
            'tipo_archivo' => ['required', 'string', 'in:' . implode(',', [
                // ETAPA 0 - Definición Necesidad
                'estudios_previos',
                // ETAPA 1 - Documentos Iniciales
                'paa',
                'no_planta',
                'paz_salvo_rentas',
                'paz_salvo_contabilidad',
                'compatibilidad_gasto',
                'cdp',
                'sigep',
                // ETAPA 2 - Documentos Contratista
                'hoja_vida_sigep',
                'certificado_estudio',
                'certificado_experiencia',
                'rut',
                'cedula_contratista',
                'cuenta_bancaria',
                'antecedentes',
                'seguridad_social',
                'certificado_medico',
                'tarjeta_profesional',
                'redam',
                // ETAPA 3 - Documentos Contractuales
                'invitacion_oferta',
                'solicitud_contratacion',
                'certificado_idoneidad',
                'estudios_previos_finales',
                'analisis_sector',
                'aceptacion_oferta',
                'ficha_bpin',
                'excepcion_fiscal',
                // ETAPA 4 - Carpeta Precontractual
                'carpeta_precontractual',
                // ETAPA 5 - Jurídica
                'solicitud_sharepoint',
                'numero_proceso',
                'lista_chequeo',
                'ajustado_derecho',
                'contrato_firmado',
                'minuta_contrato',
                'poliza',
                // ETAPA 6 - SECOP II
                'contrato_secop',
                'aprobacion_juridica',
                'firma_contratista',
                'firma_secretario',
                'contrato_electronico',
                'publicacion_secop',
                // ETAPA 7 - RPC
                'solicitud_rpc',
                'firma_secretario_planeacion',
                'radicado_hacienda',
                'rpc_expedido',
                'expediente_fisico',
                'certificado_rp',
                'registro_presupuestal',
                // ETAPA 8 - Radicación Final
                'radicado_final',
                'numero_contrato',
                // ETAPA 9 - Acta Inicio
                'solicitud_arl',
                'acta_inicio',
                'registro_secop',
                // General
                'anexo',
                'otro',
            ])],
        ]);

        return DB::transaction(function () use ($request, $proceso) {

            $file = $request->file('archivo');
            $tipoArchivo = $request->input('tipo_archivo');

            // Obtener o crear proceso_etapa actual
            $procesoEtapa = $this->getProcesoEtapaActual($proceso);

            // Generar nombre único
            $extension = $file->getClientOriginalExtension();
            $nombreGuardado = Str::uuid() . '.' . $extension;

            // Ruta: procesos/{proceso_id}/etapa_{etapa_id}/{nombre}
            $ruta = "procesos/{$proceso->id}/etapa_{$proceso->etapa_actual_id}/{$nombreGuardado}";

            // Guardar en storage/app/public
            $file->storeAs('public/' . dirname($ruta), basename($ruta));

            // Registrar en BD
            $archivoId = DB::table('proceso_etapa_archivos')->insertGetId([
                'proceso_id'       => $proceso->id,
                'proceso_etapa_id' => $procesoEtapa->id,
                'etapa_id'         => $proceso->etapa_actual_id,
                'tipo_archivo'     => $tipoArchivo,
                'nombre_original'  => $file->getClientOriginalName(),
                'nombre_guardado'  => $nombreGuardado,
                'ruta'             => $ruta,
                'mime_type'        => $file->getMimeType(),
                'tamanio'          => $file->getSize(),
                // Para flujos donde no hay fase de aprobación, se marca aprobado por defecto
                'estado'           => 'aprobado',
                'uploaded_by'      => auth()->id(),
                'uploaded_at'      => now(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            
            // ✅ NUEVO: Marcar solicitud como 'subido' si existe una pendiente para este tipo de documento
            $solicitud = DB::table('proceso_documentos_solicitados')
                ->where('proceso_id', $proceso->id)
                ->where('tipo_documento', $tipoArchivo)
                ->where('estado', 'pendiente')
                ->first();

            if ($solicitud) {
                DB::table('proceso_documentos_solicitados')
                    ->where('id', $solicitud->id)
                    ->update([
                        'estado' => 'subido',
                        'archivo_id' => $archivoId,
                        'subido_por' => auth()->id(),
                        'subido_at' => now(),
                        'updated_at' => now(),
                    ]);

                // ✅ HABILITAR DOCUMENTOS DEPENDIENTES (ej: CDP cuando se sube Compatibilidad)
                $this->habilitarDocumentosDependientes($solicitud->id);

                // Auditoría especial para solicitudes
                $etapa = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
                ProcesoAuditoria::registrar(
                    $proceso->id,
                    'solicitud_completada',
                    ucfirst(auth()->user()->roles->first()->name ?? 'Usuario'),
                    $etapa->nombre,
                    null,
                    "Solicitud completada: {$solicitud->nombre_documento} por {$solicitud->area_responsable_nombre}"
                );
            }

            // Registrar auditoría normal
            $etapa = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
            ProcesoAuditoria::registrar(
                $proceso->id,
                'archivo_subido',
                ucfirst($proceso->area_actual_role),
                $etapa->nombre,
                null,
                "Archivo subido: {$file->getClientOriginalName()} (tipo: {$tipoArchivo})"
            );

            return back()->with('success', 'Archivo subido correctamente.');
        });
    }

    /**
     * Descargar archivo
     * Admin, creador del proceso o área actual pueden descargar
     */
    public function download(int $archivo)
    {
        $archivo = DB::table('proceso_etapa_archivos')->where('id', $archivo)->first();
        abort_unless($archivo, 404, 'Archivo no encontrado.');

        // Autorización
        $proceso = DB::table('procesos')->where('id', $archivo->proceso_id)->first();
        $user = auth()->user();

        if (!$user->hasRole('admin')) {
            // El creador del proceso puede descargar cualquier archivo de su proceso
            if ($proceso->created_by !== $user->id) {
                // Roles de área de documentos pueden descargar archivos de procesos donde tienen solicitudes
                $rolesDoc = ['compras', 'talento_humano', 'rentas', 'contabilidad', 'inversiones_publicas', 'presupuesto', 'radicacion'];
                $miRolDoc = collect($rolesDoc)->first(fn($r) => $user->hasRole($r));
                $tieneAccesoDoc = false;
                if ($miRolDoc) {
                    $tieneAccesoDoc = DB::table('proceso_documentos_solicitados')
                        ->where('proceso_id', $proceso->id)
                        ->where('area_responsable_rol', $miRolDoc)
                        ->exists();
                }
                // Si no es el creador ni tiene solicitudes asignadas, debe ser del área actual
                if (!$tieneAccesoDoc) {
                    abort_unless(
                        $proceso->area_actual_role && $user->hasRole($proceso->area_actual_role),
                        403,
                        'No tienes permiso para descargar este archivo.'
                    );
                }
            }
        }

        // Verificar que el archivo existe físicamente
        $fullPath = 'public/' . $archivo->ruta;
        abort_unless(Storage::exists($fullPath), 404, 'El archivo no existe en el servidor.');

        // Si viene el parámetro inline=1, mostrar en navegador en lugar de descargar
        $inline = request()->query('inline') == '1';
        
        if ($inline) {
            // Storage::path() devuelve la ruta absoluta correcta según el disco (incluyendo /private en Laravel 11)
            return response()->file(Storage::path($fullPath));
        }

        return Storage::download($fullPath, $archivo->nombre_original);
    }

    /**
     * Eliminar archivo
     * Admin puede eliminar cualquiera
     * Usuario no-admin solo puede eliminar de la etapa ACTUAL de su área
     */
    public function destroy(int $archivo)
    {
        $archivo = DB::table('proceso_etapa_archivos')->where('id', $archivo)->first();
        abort_unless($archivo, 404, 'Archivo no encontrado.');

        $proceso = DB::table('procesos')->where('id', $archivo->proceso_id)->first();
        $user = auth()->user();

        // ✅ NUEVO: Verificar que la etapa no haya sido enviada
        $procesoEtapa = DB::table('proceso_etapas')
            ->where('proceso_id', $archivo->proceso_id)
            ->where('etapa_id', $archivo->etapa_id)
            ->first();

        if ($procesoEtapa && $procesoEtapa->enviado) {
            // Si ya se envió, verificar si la siguiente área recibió
            $archivoModel = ProcesoEtapaArchivo::find($archivo->id);
            if ($archivoModel && $this->documentoBloqueado($archivoModel)) {
                return back()->withErrors(['archivo' => 'No puedes eliminar archivos porque la siguiente área ya los recibió. Use la opción de reemplazo.']);
            }
            if (!$user->hasRole('admin')) {
                return back()->withErrors(['archivo' => 'No puedes eliminar archivos porque esta etapa ya fue enviada.']);
            }
        }

        if (!$user->hasRole('admin')) {
            // Solo puede eliminar si:
            // 1) El archivo es de la etapa actual del proceso
            // 2) El usuario tiene el rol del área actual
            abort_unless(
                (int)$archivo->etapa_id === (int)$proceso->etapa_actual_id
                && $proceso->area_actual_role
                && $user->hasRole($proceso->area_actual_role),
                403,
                'Solo puedes eliminar archivos de la etapa actual de tu área.'
            );
        }

        return DB::transaction(function () use ($archivo) {

            // Eliminar archivo físico
            $fullPath = 'public/' . $archivo->ruta;
            if (Storage::exists($fullPath)) {
                Storage::delete($fullPath);
            }

            // Eliminar registro de BD
            DB::table('proceso_etapa_archivos')->where('id', $archivo->id)->delete();
            
            // Registrar auditoría
            $proceso = DB::table('procesos')->where('id', $archivo->proceso_id)->first();
            $etapa = DB::table('etapas')->where('id', $archivo->etapa_id)->first();
            ProcesoAuditoria::registrar(
                $archivo->proceso_id,
                'archivo_eliminado',
                ucfirst($proceso->area_actual_role),
                $etapa->nombre,
                null,
                "Archivo eliminado: {$archivo->nombre_original} (tipo: {$archivo->tipo_archivo})"
            );

            return back()->with('success', 'Archivo eliminado correctamente.');
        });
    }

    /**
     * Listar archivos de un proceso (para una etapa específica o todas)
     */
    public function index(int $proceso, int $etapa = null)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeViewFiles($proceso);

        $query = DB::table('proceso_etapa_archivos as pea')
            ->join('users as u', 'u.id', '=', 'pea.uploaded_by')
            ->join('etapas as e', 'e.id', '=', 'pea.etapa_id')
            ->select([
                'pea.*',
                'u.name as uploaded_by_name',
                'e.nombre as etapa_nombre',
                'e.area_role as etapa_area'
            ])
            ->where('pea.proceso_id', $proceso)
            ->orderByDesc('pea.uploaded_at');

        if ($etapa) {
            $query->where('pea.etapa_id', $etapa);
        }

        $archivos = $query->get();

        return response()->json([
            'success' => true,
            'archivos' => $archivos
        ]);
    }

    // ======== MÉTODOS PRIVADOS ========

    private function loadProcesoOrFail(int $procesoId)
    {
        $proceso = DB::table('procesos')->where('id', $procesoId)->first();
        abort_unless($proceso, 404, 'Proceso no encontrado.');
        return $proceso;
    }

    private function authorizeAreaOrAdmin($proceso): void
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) return;

        // ✅ CASO 1: Usuario es del área actual
        if ($proceso->area_actual_role && $user->hasRole($proceso->area_actual_role)) {
            return;
        }

        // ✅ CASO 2: Usuario es el creador del proceso
        if ($proceso->created_by == $user->id) {
            return;
        }

        // ✅ CASO 3: Usuario tiene una solicitud asignada para este proceso (cualquier estado)
        // Verificar contra TODOS los roles del usuario
        $userRoles = $user->getRoleNames()->toArray();
        $tienesSolicitud = DB::table('proceso_documentos_solicitados')
            ->where('proceso_id', $proceso->id)
            ->whereIn('area_responsable_rol', $userRoles)
            ->exists();

        if ($tienesSolicitud) {
            return;
        }

        // Si no cumple ninguna condición, denegar
        abort(403, 'No tienes permiso para realizar esta acción en este proceso.');
    }

    private function authorizeViewFiles($proceso): void
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) return;

        // El creador puede ver archivos de su proceso
        if ($proceso->created_by === $user->id) return;

        // O el área actual puede ver
        abort_unless(
            $proceso->area_actual_role && $user->hasRole($proceso->area_actual_role),
            403,
            'No tienes permiso para ver archivos de este proceso.'
        );
    }

    private function getProcesoEtapaActual($proceso)
    {
        // Trae la instancia de la etapa actual (si no existe, la crea)
        $procesoEtapa = DB::table('proceso_etapas')
            ->where('proceso_id', $proceso->id)
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        if ($procesoEtapa) return $procesoEtapa;

        $id = DB::table('proceso_etapas')->insertGetId([
            'proceso_id' => $proceso->id,
            'etapa_id'   => $proceso->etapa_actual_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('proceso_etapas')->where('id', $id)->first();
    }

    /**
     * Aprobar archivo
     */
    public function aprobar(Request $request, int $archivo)
    {
        $archivo = ProcesoEtapaArchivo::with(['proceso', 'etapa'])->findOrFail($archivo);
        
        // Verificar permisos (solo el área responsable puede aprobar)
        $this->authorizeAreaOrAdmin($archivo->proceso);

        $archivo->update([
            'estado' => 'aprobado',
            'aprobado_por' => auth()->id(),
            'aprobado_at' => now(),
            'observaciones' => $request->input('observaciones'),
        ]);

        // Registrar auditoría
        ProcesoAuditoria::registrar(
            $archivo->proceso_id,
            'documento_aprobado',
            $archivo->etapa->area_responsable,
            $archivo->etapa->nombre,
            null,
            "Documento aprobado: {$archivo->nombre_original}"
        );

        return redirect()->back()->with('success', 'Documento aprobado correctamente');
    }

    /**
     * Rechazar archivo
     */
    public function rechazar(Request $request, int $archivo)
    {
        $request->validate([
            'observaciones' => 'required|string|min:10',
        ]);

        $archivo = ProcesoEtapaArchivo::with(['proceso', 'etapa', 'uploadedBy'])->findOrFail($archivo);
        
        // Verificar permisos
        $this->authorizeAreaOrAdmin($archivo->proceso);

        $archivo->update([
            'estado' => 'rechazado',
            'aprobado_por' => auth()->id(),
            'aprobado_at' => now(),
            'observaciones' => $request->input('observaciones'),
        ]);

        // Registrar auditoría
        ProcesoAuditoria::registrar(
            $archivo->proceso_id,
            'documento_rechazado',
            $archivo->etapa->area_responsable,
            $archivo->etapa->nombre,
            null,
            "Documento rechazado: {$archivo->nombre_original}. Motivo: {$request->observaciones}"
        );

        // Crear alerta para el usuario que subió el archivo
        AlertaService::crear(
            proceso: $archivo->proceso,
            tipo: 'documento_rechazado',
            titulo: 'Documento rechazado',
            mensaje: "Tu documento '{$archivo->nombre_original}' fue rechazado",
            prioridad: 'alta',
            area_responsable: 'unidad_solicitante',
            user_id: $archivo->uploaded_by,
            metadata: [
                'archivo_id' => $archivo->id,
                'archivo_nombre' => $archivo->nombre_original,
                'observaciones' => $request->observaciones,
            ]
        );

        return redirect()->back()->with('success', 'Documento rechazado. Se ha notificado al responsable');
    }

    /**
     * Reemplazar archivo (nueva versión)
     * Control de versiones:
     * - Antes de enviado: libre reemplazo por el creador
     * - Después de enviado pero antes de recibido: creador puede reemplazar
     * - Después de recibido por siguiente área: BLOQUEADO (solo admin con motivo)
     */
    public function reemplazar(Request $request, int $archivo)
    {
        $rules = [
            'archivo' => ['required', 'file', 'max:10240'],
        ];

        $archivoAnterior = ProcesoEtapaArchivo::with(['proceso', 'etapa'])->findOrFail($archivo);
        
        // Solo el usuario que subió el archivo puede reemplazarlo (o admin)
        if ($archivoAnterior->uploaded_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Solo puedes reemplazar tus propios archivos');
        }

        // Verificar si el documento está bloqueado (siguiente área ya recibió)
        $bloqueado = $this->documentoBloqueado($archivoAnterior);

        if ($bloqueado) {
            if (!auth()->user()->hasRole('admin')) {
                return back()->withErrors(['archivo' => 'Este documento está bloqueado porque la siguiente área ya lo recibió. Solo un administrador puede reemplazarlo.']);
            }
            // Admin necesita justificación
            $rules['motivo_reemplazo'] = ['required', 'string', 'min:10'];
        }

        $request->validate($rules);

        return DB::transaction(function () use ($request, $archivoAnterior, $bloqueado) {
            $file = $request->file('archivo');

            // Generar nombre único
            $extension = $file->getClientOriginalExtension();
            $nombreGuardado = Str::uuid() . '.' . $extension;

            // Ruta: procesos/{proceso_id}/etapa_{etapa_id}/{nombre}
            $ruta = "procesos/{$archivoAnterior->proceso_id}/etapa_{$archivoAnterior->etapa_id}/{$nombreGuardado}";

            // Guardar nuevo archivo
            $file->storeAs('public/' . dirname($ruta), basename($ruta));

            // Crear nuevo registro
            $nuevoArchivo = ProcesoEtapaArchivo::create([
                'proceso_id'       => $archivoAnterior->proceso_id,
                'proceso_etapa_id' => $archivoAnterior->proceso_etapa_id,
                'etapa_id'         => $archivoAnterior->etapa_id,
                'tipo_archivo'     => $archivoAnterior->tipo_archivo,
                'nombre_original'  => $file->getClientOriginalName(),
                'nombre_guardado'  => $nombreGuardado,
                'ruta'             => $ruta,
                'mime_type'        => $file->getMimeType(),
                'tamanio'          => $file->getSize(),
                'uploaded_by'      => auth()->id(),
                'uploaded_at'      => now(),
                'estado'           => $bloqueado ? 'pendiente' : 'aprobado',
                'version'          => $archivoAnterior->version + 1,
                'archivo_anterior_id' => $archivoAnterior->id,
                'motivo_reemplazo' => $request->input('motivo_reemplazo'),
                'es_reemplazo_admin' => $bloqueado && auth()->user()->hasRole('admin'),
            ]);

            // Registrar auditoría
            $accion = $bloqueado ? 'reemplazo_administrativo' : 'documento_reemplazado';
            $descripcion = $bloqueado
                ? "Reemplazo administrativo: {$archivoAnterior->nombre_original} (v{$archivoAnterior->version}) → {$file->getClientOriginalName()} (v{$nuevoArchivo->version}). Motivo: {$request->motivo_reemplazo}"
                : "Documento reemplazado: {$archivoAnterior->nombre_original} (v{$archivoAnterior->version}) → {$file->getClientOriginalName()} (v{$nuevoArchivo->version})";

            ProcesoAuditoria::registrar(
                $archivoAnterior->proceso_id,
                $accion,
                $archivoAnterior->etapa->area_responsable ?? 'Sistema',
                $archivoAnterior->etapa->nombre ?? 'N/A',
                null,
                $descripcion
            );

            $msg = $bloqueado
                ? 'Archivo reemplazado por administrador. Queda en revisión.'
                : 'Archivo reemplazado. Nueva versión en revisión';

            return redirect()->back()->with('success', $msg);
        });
    }

    /**
     * Habilitar documentos que dependen de una solicitud recién completada
     * Ejemplo: cuando se sube "Compatibilidad del Gasto", habilita "CDP"
     */
    private function habilitarDocumentosDependientes(int $solicitudId): void
    {
        // Buscar todas las solicitudes que dependen de esta
        $solicitudesDependientes = DB::table('proceso_documentos_solicitados')
            ->where('depende_de_solicitud_id', $solicitudId)
            ->where('puede_subir', false)
            ->get();

        foreach ($solicitudesDependientes as $dependiente) {
            DB::table('proceso_documentos_solicitados')
                ->where('id', $dependiente->id)
                ->update([
                    'puede_subir' => true,
                    'updated_at' => now(),
                ]);

            // Registrar auditoría del desbloqueo
            ProcesoAuditoria::registrar(
                $dependiente->proceso_id,
                'documento_desbloqueado',
                'Sistema',
                'Etapa 1',
                null,
                "Documento desbloqueado: {$dependiente->nombre_documento} - Ahora {$dependiente->area_responsable_nombre} puede subirlo"
            );
        }
    }

    /**
     * Verificar si un documento está bloqueado (la siguiente área ya recibió)
     */
    private function documentoBloqueado(ProcesoEtapaArchivo $archivo): bool
    {
        // Obtener la proceso_etapa del archivo
        $procesoEtapa = DB::table('proceso_etapas')
            ->where('proceso_id', $archivo->proceso_id)
            ->where('etapa_id', $archivo->etapa_id)
            ->first();

        if (!$procesoEtapa || !$procesoEtapa->enviado) {
            return false; // Aún no se ha enviado, no está bloqueado
        }

        // Buscar la siguiente etapa
        $etapaActual = DB::table('etapas')->where('id', $archivo->etapa_id)->first();
        if (!$etapaActual || !$etapaActual->next_etapa_id) {
            return false;
        }

        // Verificar si la siguiente área ya recibió
        $siguienteProcesoEtapa = DB::table('proceso_etapas')
            ->where('proceso_id', $archivo->proceso_id)
            ->where('etapa_id', $etapaActual->next_etapa_id)
            ->first();

        return $siguienteProcesoEtapa && $siguienteProcesoEtapa->recibido;
    }

    /**
     * Preview: Devuelve datos del archivo para previsualización en modal
     */
    public function preview(int $archivo)
    {
        $archivo = ProcesoEtapaArchivo::with(['uploadedBy', 'etapa'])->findOrFail($archivo);

        // Autorización: reusar lógica de download
        $proceso = DB::table('procesos')->where('id', $archivo->proceso_id)->first();
        abort_unless($proceso, 404);
        $this->authorizeViewFiles($proceso);

        $bloqueado = $this->documentoBloqueado($archivo);
        $puedeReemplazar = !$bloqueado || auth()->user()->hasRole('admin');

        // Obtener historial de versiones
        $versiones = $this->obtenerCadenaVersiones($archivo);

        return response()->json([
            'success' => true,
            'archivo' => [
                'id' => $archivo->id,
                'nombre_original' => $archivo->nombre_original,
                'tipo_archivo' => $archivo->tipo_archivo,
                'mime_type' => $archivo->mime_type,
                'tamanio' => $archivo->tamanio_formateado,
                'version' => $archivo->version,
                'estado' => $archivo->estado,
                'uploaded_by' => $archivo->uploadedBy->name ?? 'Desconocido',
                'uploaded_at' => $archivo->uploaded_at?->format('d/m/Y H:i'),
                'observaciones' => $archivo->observaciones,
                'motivo_reemplazo' => $archivo->motivo_reemplazo,
                'es_reemplazo_admin' => (bool) $archivo->es_reemplazo_admin,
                'preview_url' => route('workflow.files.download', ['archivo' => $archivo->id, 'inline' => 1]),
                'download_url' => route('workflow.files.download', $archivo->id),
                'es_previsualizable' => $this->esPrevisualizable($archivo->mime_type),
            ],
            'bloqueado' => $bloqueado,
            'puede_reemplazar' => $puedeReemplazar,
            'versiones' => $versiones,
        ]);
    }

    /**
     * Historial de versiones de un archivo
     */
    public function historialVersiones(int $archivo)
    {
        $archivo = ProcesoEtapaArchivo::findOrFail($archivo);

        $proceso = DB::table('procesos')->where('id', $archivo->proceso_id)->first();
        abort_unless($proceso, 404);
        $this->authorizeViewFiles($proceso);

        $versiones = $this->obtenerCadenaVersiones($archivo);

        return response()->json([
            'success' => true,
            'versiones' => $versiones,
        ]);
    }

    /**
     * Obtener toda la cadena de versiones de un archivo (desde v1 hasta la actual)
     */
    private function obtenerCadenaVersiones(ProcesoEtapaArchivo $archivo): array
    {
        // Ir al origen (v1)
        $origen = $archivo;
        while ($origen->archivo_anterior_id) {
            $origen = ProcesoEtapaArchivo::find($origen->archivo_anterior_id);
            if (!$origen) break;
        }

        // Recolectar toda la cadena hacia adelante
        $versiones = [];
        $current = $origen;
        while ($current) {
            $versiones[] = [
                'id' => $current->id,
                'version' => $current->version,
                'nombre_original' => $current->nombre_original,
                'tamanio' => $current->tamanio_formateado,
                'estado' => $current->estado,
                'uploaded_by' => $current->uploadedBy->name ?? 'Desconocido',
                'uploaded_at' => $current->uploaded_at?->format('d/m/Y H:i'),
                'es_reemplazo_admin' => (bool) $current->es_reemplazo_admin,
                'motivo_reemplazo' => $current->motivo_reemplazo,
                'preview_url' => route('workflow.files.download', ['archivo' => $current->id, 'inline' => 1]),
                'download_url' => route('workflow.files.download', $current->id),
            ];

            // Buscar la siguiente versión
            $next = ProcesoEtapaArchivo::where('archivo_anterior_id', $current->id)->first();
            $current = $next;
        }

        return $versiones;
    }

    /**
     * Verificar si un tipo MIME es previsualizable en el navegador
     */
    private function esPrevisualizable(string $mimeType): bool
    {
        $previsualizables = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'text/plain',
            'text/html',
        ];

        return in_array($mimeType, $previsualizables);
    }
}
