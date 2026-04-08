<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatalogoPaso;
use App\Models\Flujo;
use App\Models\FlujoInstancia;
use App\Models\FlujoVersion;
use App\Models\FlujoPaso;
use App\Models\Secretaria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * ╔══════════════════════════════════════════════════════════════════════════════╗
 * ║  MotorFlujoController – API para el Motor de Flujos Configurable          ║
 * ╠══════════════════════════════════════════════════════════════════════════════╣
 * ║  Gestiona flujos, pasos, versiones e instancias de ejecución.             ║
 * ║  La modificación de flujos solo puede ser realizada por administradores   ║
 * ║  de la unidad solicitante.                                                ║
 * ╚══════════════════════════════════════════════════════════════════════════════╝
 */
class MotorFlujoController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════════
    // CATÁLOGO DE PASOS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/motor-flujos/catalogo-pasos
     * Lista el catálogo general de pasos reutilizables.
     */
    public function catalogoPasos(): JsonResponse
    {
        $pasos = CatalogoPaso::activos()->orderBy('nombre')->get();

        return response()->json(['catalogo_pasos' => $pasos]);
    }

    /**
     * POST /api/motor-flujos/catalogo-pasos
     * Crear un nuevo paso en el catálogo (solo admin general).
     */
    public function crearCatalogoPaso(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo'      => ['required', 'string', 'max:50', 'unique:catalogo_pasos,codigo'],
            'nombre'      => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'icono'       => ['nullable', 'string', 'max:50'],
            'color'       => ['nullable', 'string', 'max:20'],
            'tipo'        => ['nullable', Rule::in(['secuencial', 'paralelo', 'condicional'])],
        ]);

        $paso = CatalogoPaso::create($data);

        return response()->json([
            'message'       => 'Paso creado en el catálogo.',
            'catalogo_paso' => $paso,
        ], 201);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FLUJOS POR SECRETARÍA
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/motor-flujos/secretarias/{secretariaId}/flujos
     * Lista los flujos activos de una Secretaría.
     */
    public function flujosPorSecretaria(Request $request, int $secretariaId): JsonResponse
    {
        $this->verificarAccesoSecretaria($request, $secretariaId);

        $flujos = Flujo::activos()
            ->porSecretaria($secretariaId)
            ->with(['secretaria', 'versionActiva.pasos'])
            ->get();

        return response()->json(['flujos' => $flujos]);
    }

    /**
     * GET /api/motor-flujos/secretarias-visibles
     * Retorna las secretarías que el usuario puede administrar/ver en el motor.
     */
    public function secretariasVisibles(Request $request): JsonResponse
    {
        $user = $request->user();
        $puedeVerTodas = $this->puedeVerTodasSecretarias($user);

        $query = Secretaria::query()
            ->where('activo', true)
            ->orderBy('nombre');

        if (!$puedeVerTodas) {
            $query->where('id', (int) $user->secretaria_id);
        }

        return response()->json([
            'secretarias' => $query->get(['id', 'nombre', 'activo']),
            'puede_ver_todas_secretarias' => $puedeVerTodas,
            'scope_mode' => strtolower((string) config('motor_flujos.secretaria_scope', 'roles')),
        ]);
    }

    /**
     * POST /api/motor-flujos/flujos
     * Crear un nuevo flujo para una Secretaría (solo admin de la unidad).
     */
    public function crearFlujo(Request $request): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $data = $request->validate([
            'codigo'            => ['required', 'string', 'max:50', 'unique:flujos,codigo'],
            'nombre'            => ['required', 'string', 'max:255'],
            'descripcion'       => ['nullable', 'string'],
            'tipo_contratacion' => ['nullable', 'string', 'max:50'],
            'secretaria_id'     => ['required', 'exists:secretarias,id'],
        ]);

        $this->verificarAccesoSecretaria($request, (int) $data['secretaria_id']);

        $flujo = DB::transaction(function () use ($data, $request) {
            $flujo = Flujo::create($data);

            // Crear versión inicial
            $version = FlujoVersion::create([
                'flujo_id'       => $flujo->id,
                'numero_version' => 1,
                'motivo_cambio'  => 'Versión inicial',
                'estado'         => 'activa',
                'creado_por'     => $request->user()->id,
                'publicada_at'   => now(),
            ]);

            $flujo->update(['version_activa_id' => $version->id]);
            $flujo->load('versionActiva', 'secretaria');

            return $flujo;
        });

        return response()->json([
            'message' => 'Flujo creado correctamente.',
            'flujo'   => $flujo,
        ], 201);
    }

    /**
     * PUT /api/motor-flujos/flujos/{flujoId}
     * Actualizar datos de un flujo existente.
     */
    public function actualizarFlujo(Request $request, int $flujoId): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $flujo = Flujo::findOrFail($flujoId);
        $this->verificarAccesoSecretaria($request, $flujo->secretaria_id);

        $data = $request->validate([
            'nombre'            => ['sometimes', 'string', 'max:255'],
            'descripcion'       => ['nullable', 'string'],
            'tipo_contratacion' => ['sometimes', 'string', 'max:50'],
        ]);

        $flujo->update($data);
        $flujo->load('versionActiva', 'secretaria');

        return response()->json([
            'message' => 'Flujo actualizado.',
            'flujo'   => $flujo,
        ]);
    }

    /**
     * GET /api/motor-flujos/flujos/{flujoId}/pasos
     * Obtener los pasos ordenados de la versión activa del flujo.
     */
    public function pasosDelFlujo(int $flujoId): JsonResponse
    {
        $flujo = Flujo::findOrFail($flujoId);

        $pasos = $flujo->pasosOrdenados();

        return response()->json(['pasos' => $pasos]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // VERSIONES DE FLUJO
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * POST /api/motor-flujos/flujos/{flujoId}/versiones
     * Crear nueva versión del flujo (duplica la activa).
     */
    public function crearVersion(Request $request, int $flujoId): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $flujo = Flujo::findOrFail($flujoId);
        $this->verificarAccesoSecretaria($request, $flujo);

        $data = $request->validate([
            'motivo_cambio' => ['nullable', 'string', 'max:500'],
        ]);

        $nuevaVersion = DB::transaction(function () use ($flujo, $request, $data) {
            $nuevaVersion = $flujo->crearVersion(
                $request->user()->id,
                $data['motivo_cambio'] ?? null
            );

            // Duplicar pasos de la versión activa
            if ($flujo->versionActiva) {
                $nuevaVersion->duplicarPasosDe($flujo->versionActiva);
            }

            return $nuevaVersion;
        });

        $nuevaVersion->load('pasos.catalogoPaso');

        return response()->json([
            'message' => "Versión {$nuevaVersion->numero_version} creada (borrador).",
            'version' => $nuevaVersion,
        ], 201);
    }

    /**
     * POST /api/motor-flujos/versiones/{versionId}/publicar
     * Publicar (activar) una versión borrador.
     */
    public function publicarVersion(Request $request, int $versionId): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $version = FlujoVersion::findOrFail($versionId);
        $flujo   = Flujo::findOrFail($version->flujo_id);
        $this->verificarAccesoSecretaria($request, $flujo);

        if ($version->estado !== 'borrador') {
            return response()->json([
                'message' => 'Solo se pueden publicar versiones en estado borrador.',
            ], 422);
        }

        $version->publicar();

        return response()->json([
            'message' => "Versión {$version->numero_version} publicada como activa.",
            'version' => $version->fresh(),
        ]);
    }

    /**
     * GET /api/motor-flujos/flujos/{flujoId}/versiones
     * Historial de versiones del flujo.
     */
    public function versionesDelFlujo(int $flujoId): JsonResponse
    {
        $versiones = FlujoVersion::where('flujo_id', $flujoId)
            ->with('creadoPor')
            ->withCount('pasos', 'instancias')
            ->orderByDesc('numero_version')
            ->get();

        return response()->json(['versiones' => $versiones]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // GESTIÓN DE PASOS EN UNA VERSIÓN
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * POST /api/motor-flujos/versiones/{versionId}/pasos
     * Agregar un paso a una versión borrador.
     */
    public function agregarPaso(Request $request, int $versionId): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $version = FlujoVersion::findOrFail($versionId);

        if ($version->estado !== 'borrador') {
            return response()->json(['message' => 'Solo se pueden editar versiones en borrador.'], 422);
        }

        $data = $request->validate([
            'catalogo_paso_id'         => ['required', 'exists:catalogo_pasos,id'],
            'orden'                    => ['required', 'integer', 'min:0'],
            'nombre_personalizado'     => ['nullable', 'string', 'max:255'],
            'instrucciones'            => ['nullable', 'string'],
            'es_obligatorio'           => ['boolean'],
            'es_paralelo'              => ['boolean'],
            'dias_estimados'           => ['nullable', 'integer', 'min:1'],
            'area_responsable_default' => ['nullable', 'string', 'max:100'],
        ]);

        $data['flujo_version_id'] = $versionId;
        $paso = FlujoPaso::create($data);
        $paso->load('catalogoPaso');

        return response()->json([
            'message' => 'Paso agregado a la versión.',
            'paso'    => $paso,
        ], 201);
    }

    /**
     * PUT /api/motor-flujos/pasos/{pasoId}
     * Actualizar un paso (solo en versiones borrador).
     */
    public function actualizarPaso(Request $request, int $pasoId): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $paso    = FlujoPaso::findOrFail($pasoId);
        $version = FlujoVersion::findOrFail($paso->flujo_version_id);

        if ($version->estado !== 'borrador') {
            return response()->json(['message' => 'Solo se pueden editar versiones en borrador.'], 422);
        }

        $data = $request->validate([
            'orden'                    => ['sometimes', 'integer', 'min:0'],
            'nombre_personalizado'     => ['nullable', 'string', 'max:255'],
            'instrucciones'            => ['nullable', 'string'],
            'es_obligatorio'           => ['boolean'],
            'es_paralelo'              => ['boolean'],
            'dias_estimados'           => ['nullable', 'integer', 'min:1'],
            'area_responsable_default' => ['nullable', 'string', 'max:100'],
            'activo'                   => ['boolean'],
        ]);

        $paso->update($data);

        return response()->json([
            'message' => 'Paso actualizado.',
            'paso'    => $paso->fresh()->load('catalogoPaso'),
        ]);
    }

    /**
     * DELETE /api/motor-flujos/pasos/{pasoId}
     * Eliminar un paso (solo en versiones borrador).
     */
    public function eliminarPaso(Request $request, int $pasoId): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $paso    = FlujoPaso::findOrFail($pasoId);
        $version = FlujoVersion::findOrFail($paso->flujo_version_id);

        if ($version->estado !== 'borrador') {
            return response()->json(['message' => 'Solo se pueden editar versiones en borrador.'], 422);
        }

        $paso->delete();

        return response()->json(['message' => 'Paso eliminado.']);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CONDICIONES, DOCUMENTOS Y RESPONSABLES POR PASO
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * POST /api/motor-flujos/pasos/{pasoId}/condiciones
     */
    public function agregarCondicion(Request $request, int $pasoId): JsonResponse
    {
        $this->validarAdminUnidad($request);
        $this->validarPasoEditable($pasoId);

        $data = $request->validate([
            'campo'       => ['required', 'string', 'max:100'],
            'operador'    => ['required', Rule::in(['>', '<', '>=', '<=', '==', '!=', 'in', 'not_in', 'between', 'contains'])],
            'valor'       => ['required', 'string'],
            'accion'      => ['required', Rule::in(['requerido', 'omitir', 'agregar_paso', 'notificar'])],
            'descripcion' => ['nullable', 'string'],
            'prioridad'   => ['integer', 'min:0'],
        ]);

        $data['flujo_paso_id'] = $pasoId;
        $condicion = \App\Models\FlujoPasoCondicion::create($data);

        return response()->json(['message' => 'Condición agregada.', 'condicion' => $condicion], 201);
    }

    /**
     * POST /api/motor-flujos/pasos/{pasoId}/documentos
     */
    public function agregarDocumento(Request $request, int $pasoId): JsonResponse
    {
        $this->validarAdminUnidad($request);
        $this->validarPasoEditable($pasoId);

        $data = $request->validate([
            'nombre'         => ['required', 'string', 'max:255'],
            'descripcion'    => ['nullable', 'string'],
            'tipo_archivo'   => ['nullable', 'string', 'max:50'],
            'es_obligatorio' => ['boolean'],
            'max_archivos'   => ['integer', 'min:1'],
            'max_tamano_mb'  => ['integer', 'min:1'],
            'plantilla_url'  => ['nullable', 'string'],
            'orden'          => ['integer', 'min:0'],
        ]);

        $data['flujo_paso_id'] = $pasoId;
        $doc = \App\Models\FlujoPasoDocumento::create($data);

        return response()->json(['message' => 'Documento agregado.', 'documento' => $doc], 201);
    }

    /**
     * POST /api/motor-flujos/pasos/{pasoId}/responsables
     */
    public function agregarResponsable(Request $request, int $pasoId): JsonResponse
    {
        $this->validarAdminUnidad($request);
        $this->validarPasoEditable($pasoId);

        $data = $request->validate([
            'rol'          => ['required', 'string', 'max:100'],
            'user_id'      => ['nullable', 'exists:users,id'],
            'unidad_id'    => ['nullable', 'exists:unidades,id'],
            'tipo'         => ['required', Rule::in(['ejecutor', 'revisor', 'aprobador', 'observador'])],
            'es_principal' => ['boolean'],
        ]);

        $data['flujo_paso_id'] = $pasoId;
        $resp = \App\Models\FlujoPasoResponsable::create($data);

        return response()->json(['message' => 'Responsable asignado.', 'responsable' => $resp], 201);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // ELIMINACIÓN DE SUB-ELEMENTOS DE PASO
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DELETE /api/motor-flujos/condiciones/{condicionId}
     */
    public function eliminarCondicion(Request $request, int $condicionId): JsonResponse
    {
        $this->validarAdminUnidad($request);
        $cond = \App\Models\FlujoPasoCondicion::findOrFail($condicionId);
        $this->validarPasoEditable($cond->flujo_paso_id);
        $cond->delete();
        return response()->json(['message' => 'Condición eliminada.']);
    }

    /**
     * DELETE /api/motor-flujos/documentos/{documentoId}
     */
    public function eliminarDocumento(Request $request, int $documentoId): JsonResponse
    {
        $this->validarAdminUnidad($request);
        $doc = \App\Models\FlujoPasoDocumento::findOrFail($documentoId);
        $this->validarPasoEditable($doc->flujo_paso_id);
        $doc->delete();
        return response()->json(['message' => 'Documento eliminado.']);
    }

    /**
     * DELETE /api/motor-flujos/responsables/{responsableId}
     */
    public function eliminarResponsable(Request $request, int $responsableId): JsonResponse
    {
        $this->validarAdminUnidad($request);
        $resp = \App\Models\FlujoPasoResponsable::findOrFail($responsableId);
        $this->validarPasoEditable($resp->flujo_paso_id);
        $resp->delete();
        return response()->json(['message' => 'Responsable eliminado.']);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // INSTANCIAS (PROCESOS EN EJECUCIÓN)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * POST /api/motor-flujos/instancias
     * Crear e iniciar una nueva instancia de flujo.
     */
    public function crearInstancia(Request $request): JsonResponse
    {
        $data = $request->validate([
            'flujo_id'        => ['required', 'exists:flujos,id'],
            'objeto'          => ['required', 'string'],
            'monto_estimado'  => ['nullable', 'numeric', 'min:0'],
            'plazo_dias'      => ['nullable', 'integer', 'min:1'],
            'metadata'        => ['nullable', 'array'],
        ]);

        $flujo = Flujo::findOrFail($data['flujo_id']);
        $user  = $request->user();

        if (!$flujo->version_activa_id) {
            return response()->json(['message' => 'El flujo no tiene versión activa.'], 422);
        }

        $instancia = DB::transaction(function () use ($data, $flujo, $user) {
            // Generar código de proceso
            $year  = date('Y');
            $count = FlujoInstancia::where('flujo_id', $flujo->id)->whereYear('created_at', $year)->count() + 1;
            $codigo = strtoupper($flujo->codigo) . '-' . str_pad($count, 3, '0', STR_PAD_LEFT) . '-' . $year;

            $instancia = FlujoInstancia::create([
                'codigo_proceso'   => $codigo,
                'flujo_id'         => $flujo->id,
                'flujo_version_id' => $flujo->version_activa_id,
                'secretaria_id'    => $user->secretaria_id,
                'unidad_id'        => $user->unidad_id,
                'objeto'           => $data['objeto'],
                'monto_estimado'   => $data['monto_estimado'] ?? null,
                'plazo_dias'       => $data['plazo_dias'] ?? null,
                'metadata'         => $data['metadata'] ?? null,
                'creado_por'       => $user->id,
            ]);

            // Iniciar el flujo (crea instancias de pasos, evalúa condiciones)
            $instancia->iniciar();

            return $instancia;
        });

        $instancia->load('pasos.flujoPaso.catalogoPaso');

        return response()->json([
            'message'   => "Proceso {$instancia->codigo_proceso} iniciado.",
            'instancia' => $instancia,
        ], 201);
    }

    /**
     * GET /api/motor-flujos/flujos/{flujoId}/instancia-activa
     * Obtener la instancia en curso del usuario actual.
     */
    public function instanciaActiva(Request $request, int $flujoId): JsonResponse
    {
        $instancia = FlujoInstancia::where('flujo_id', $flujoId)
            ->where('creado_por', $request->user()->id)
            ->whereIn('estado', ['borrador', 'en_curso', 'devuelto'])
            ->with(['pasos.flujoPaso.catalogoPaso', 'pasoActual.catalogoPaso'])
            ->latest()
            ->first();

        return response()->json(['instancia' => $instancia]);
    }

    /**
     * POST /api/motor-flujos/instancias/{instanciaId}/avanzar
     * Avanzar la instancia al siguiente paso.
     */
    public function avanzarInstancia(Request $request, int $instanciaId): JsonResponse
    {
        $instancia = FlujoInstancia::findOrFail($instanciaId);
        $this->autorizarAccionInstancia($request, $instancia);

        $observaciones = $request->input('observaciones');
        $siguiente = $instancia->avanzar($request->user()->id, $observaciones);

        $instancia->load('pasos.flujoPaso.catalogoPaso');

        $msg = $siguiente
            ? "Avanzado al paso: {$siguiente->flujoPaso->nombre_efectivo}"
            : 'Flujo completado.';

        return response()->json([
            'message'   => $msg,
            'instancia' => $instancia->fresh(),
        ]);
    }

    /**
     * POST /api/motor-flujos/instancias/{instanciaId}/devolver
     * Devolver la instancia a un paso anterior.
     */
    public function devolverInstancia(Request $request, int $instanciaId): JsonResponse
    {
        $instancia = FlujoInstancia::findOrFail($instanciaId);
        $this->autorizarAccionInstancia($request, $instancia);

        $data = $request->validate([
            'motivo'        => ['required', 'string', 'max:1000'],
            'orden_destino' => ['nullable', 'integer', 'min:0'],
        ]);

        // Si no se especifica orden_destino, devolver al paso anterior
        $pasoActual = $instancia->pasos()->where('estado', 'en_progreso')->first();
        $ordenDestino = $data['orden_destino'] ?? ($pasoActual ? $pasoActual->orden - 1 : 0);

        $pasoDevuelto = $instancia->devolver($ordenDestino, $request->user()->id, $data['motivo']);

        return response()->json([
            'message'   => $pasoDevuelto
                ? "Devuelto al paso orden {$ordenDestino}."
                : 'No se pudo devolver.',
            'instancia' => $instancia->fresh()->load('pasos'),
        ]);
    }

    /**
     * GET /api/motor-flujos/instancias/{instanciaId}
     * Detalle de una instancia.
     */
    public function detalleInstancia(int $instanciaId): JsonResponse
    {
        $instancia = FlujoInstancia::with([
            'flujo.secretaria',
            'version',
            'pasos.flujoPaso.catalogoPaso',
            'pasos.flujoPaso.documentos',
            'pasos.flujoPaso.responsables',
            'pasos.documentos',
            'creadoPor',
            'secretaria',
            'unidad',
        ])->findOrFail($instanciaId);

        return response()->json(['instancia' => $instancia]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // GUARDAR FLUJO COMPLETO (una sola transacción)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * POST /api/motor-flujos/flujos/guardar-completo
     *
     * Crea o actualiza un flujo con TODOS sus pasos, documentos y dependencias
     * en una sola transacción atómica. Elimina el problema de multi-request.
     */
    public function guardarFlujoCompleto(Request $request): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $data = $request->validate([
            'flujo_id'          => ['nullable', 'integer'],
            'codigo'            => ['required', 'string', 'max:50'],
            'nombre'            => ['required', 'string', 'max:255'],
            'descripcion'       => ['nullable', 'string'],
            'tipo_contratacion' => ['nullable', 'string', 'max:50'],
            'secretaria_id'     => ['required', 'exists:secretarias,id'],
            'pasos'             => ['required', 'array', 'min:1'],
            'pasos.*.nombre'              => ['required', 'string', 'max:255'],
            'pasos.*.catalogo_paso_id'    => ['nullable', 'integer'],
            'pasos.*.area_responsable'    => ['nullable', 'string', 'max:100'],
            'pasos.*.dias_estimados'      => ['nullable', 'integer', 'min:1'],
            'pasos.*.instrucciones'       => ['nullable', 'string'],
            'pasos.*.obligatorio'         => ['nullable', 'boolean'],
            'pasos.*.documentos'          => ['nullable', 'array'],
            'pasos.*.documentos.*.nombre'      => ['required', 'string', 'max:255'],
            'pasos.*.documentos.*.tipo'        => ['nullable', 'string', 'max:50'],
            'pasos.*.documentos.*.obligatorio' => ['nullable', 'boolean'],
            'pasos.*.documentos.*.depende_de_doc' => ['nullable', 'string', 'max:20'],
            'pasos.*.depende_de'          => ['nullable', 'array'],
            'pasos.*.depende_de.*'        => ['integer', 'min:0'],
        ]);

        $resultado = DB::transaction(function () use ($data, $request) {
            $userId = $request->user()->id;

            // ── 1) Crear o encontrar el flujo ──
            if (!empty($data['flujo_id'])) {
                $flujo = Flujo::findOrFail($data['flujo_id']);
                $this->verificarAccesoSecretaria($request, $flujo);
                $flujo->update([
                    'nombre'            => $data['nombre'],
                    'descripcion'       => $data['descripcion'] ?? null,
                    'tipo_contratacion' => $data['tipo_contratacion'] ?? $flujo->tipo_contratacion,
                ]);
            } else {
                // Verificar código único
                if (Flujo::where('codigo', $data['codigo'])->exists()) {
                    throw new \Exception('Ya existe un flujo con el código "' . $data['codigo'] . '".');
                }

                $this->verificarAccesoSecretaria($request, (int) $data['secretaria_id']);

                $flujo = Flujo::create([
                    'codigo'            => $data['codigo'],
                    'nombre'            => $data['nombre'],
                    'descripcion'       => $data['descripcion'] ?? null,
                    'tipo_contratacion' => $data['tipo_contratacion'] ?? null,
                    'secretaria_id'     => $data['secretaria_id'],
                    'activo'            => true,
                ]);
            }

            // ── 2) Crear nueva versión ──
            $ultimaVersion = $flujo->versiones()->max('numero_version') ?? 0;
            $version = FlujoVersion::create([
                'flujo_id'       => $flujo->id,
                'numero_version' => $ultimaVersion + 1,
                'motivo_cambio'  => $ultimaVersion === 0 ? 'Versión inicial' : 'Actualización desde constructor visual',
                'estado'         => 'activa',
                'creado_por'     => $userId,
                'publicada_at'   => now(),
            ]);

            // Archivar versiones activas anteriores
            $flujo->versiones()
                ->where('estado', 'activa')
                ->where('id', '!=', $version->id)
                ->update(['estado' => 'archivada']);

            // ── 3) Limpiar pasos de versiones anteriores tipo borrador ──
            // (no tocamos pasos de versiones archivadas por historial)

            // ── 4) Crear pasos con documentos ──
            $pasosCreados = [];
            foreach ($data['pasos'] as $orden => $pasoData) {
                // Buscar o usar el primer catálogo disponible
                $catId = $pasoData['catalogo_paso_id'] ?? null;
                if ($catId && !CatalogoPaso::where('id', $catId)->exists()) {
                    $catId = null;
                }
                if (!$catId) {
                    $catId = CatalogoPaso::first()?->id;
                }
                if (!$catId) {
                    throw new \Exception('No hay pasos en el catálogo. Cree al menos uno primero.');
                }

                $paso = FlujoPaso::create([
                    'flujo_version_id'         => $version->id,
                    'catalogo_paso_id'         => $catId,
                    'orden'                    => $orden,
                    'nombre_personalizado'     => $pasoData['nombre'],
                    'area_responsable_default' => $pasoData['area_responsable'] ?? 'unidad_solicitante',
                    'dias_estimados'           => $pasoData['dias_estimados'] ?? null,
                    'instrucciones'            => $pasoData['instrucciones'] ?? null,
                    'es_obligatorio'           => $pasoData['obligatorio'] ?? true,
                    'es_paralelo'              => false,
                    'activo'                   => true,
                ]);

                // Crear documentos del paso
                foreach (($pasoData['documentos'] ?? []) as $docOrden => $docData) {
                    \App\Models\FlujoPasoDocumento::create([
                        'flujo_paso_id'   => $paso->id,
                        'nombre'          => $docData['nombre'],
                        'tipo_archivo'    => $docData['tipo'] ?? 'pdf',
                        'es_obligatorio'  => $docData['obligatorio'] ?? true,
                        'depende_de_doc'  => $docData['depende_de_doc'] ?? null,
                        'orden'           => $docOrden,
                        'activo'          => true,
                    ]);
                }

                $pasosCreados[] = $paso;
            }

            // ── 5) Crear dependencias entre pasos ──
            foreach ($data['pasos'] as $orden => $pasoData) {
                foreach (($pasoData['depende_de'] ?? []) as $depOrden) {
                    if (isset($pasosCreados[$depOrden])) {
                        \App\Models\FlujoPasoCondicion::create([
                            'flujo_paso_id' => $pasosCreados[$orden]->id,
                            'campo'         => 'paso_previo',
                            'operador'      => '==',
                            'valor'         => 'completado',
                            'accion'        => 'requerido',
                            'descripcion'   => 'Requiere completar: ' . ($pasosCreados[$depOrden]->nombre_personalizado ?? "Paso " . ($depOrden + 1)),
                            'prioridad'     => 0,
                            'activo'        => true,
                        ]);
                    }
                }
            }

            // ── 6) Actualizar version_activa_id del flujo ──
            $flujo->update(['version_activa_id' => $version->id]);

            $flujo->load(['versionActiva.pasos.catalogoPaso', 'versionActiva.pasos.documentos', 'versionActiva.pasos.condiciones', 'secretaria']);

            return $flujo;
        });

        return response()->json([
            'message' => 'Flujo guardado correctamente con ' . count($data['pasos']) . ' pasos.',
            'flujo'   => $resultado,
        ], 201);
    }

    /**
     * POST /api/motor-flujos/flujos/{flujoId}/duplicar
     * Duplicar un flujo completo (version activa + pasos + docs + condiciones + responsables).
     */
    public function duplicarFlujo(Request $request, int $flujoId): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $flujoOrigen = Flujo::with('versionActiva')->findOrFail($flujoId);
        $this->verificarAccesoSecretaria($request, $flujoOrigen);

        $data = $request->validate([
            'codigo'        => ['nullable', 'string', 'max:50', 'unique:flujos,codigo'],
            'nombre'        => ['nullable', 'string', 'max:255'],
            'descripcion'   => ['nullable', 'string'],
            'secretaria_id' => ['nullable', 'exists:secretarias,id'],
        ]);

        $secretariaDestinoId = (int) ($data['secretaria_id'] ?? $flujoOrigen->secretaria_id);
        $this->verificarAccesoSecretaria($request, $secretariaDestinoId);

        $nuevoFlujo = DB::transaction(function () use ($data, $request, $flujoOrigen, $secretariaDestinoId) {
            $codigo = $data['codigo'] ?? $this->generarCodigoDuplicado($flujoOrigen->codigo);

            $flujo = Flujo::create([
                'codigo'            => $codigo,
                'nombre'            => $data['nombre'] ?? ($flujoOrigen->nombre . ' (Copia)'),
                'descripcion'       => array_key_exists('descripcion', $data)
                    ? $data['descripcion']
                    : $flujoOrigen->descripcion,
                'tipo_contratacion' => $flujoOrigen->tipo_contratacion,
                'secretaria_id'     => $secretariaDestinoId,
                'activo'            => true,
            ]);

            $version = FlujoVersion::create([
                'flujo_id'       => $flujo->id,
                'numero_version' => 1,
                'motivo_cambio'  => 'Duplicado de flujo ID ' . $flujoOrigen->id,
                'estado'         => 'activa',
                'creado_por'     => $request->user()->id,
                'publicada_at'   => now(),
            ]);

            if ($flujoOrigen->versionActiva) {
                $version->duplicarPasosDe($flujoOrigen->versionActiva);
            }

            $flujo->update(['version_activa_id' => $version->id]);

            return $flujo;
        });

        $nuevoFlujo->load(['secretaria', 'versionActiva.pasos.catalogoPaso', 'versionActiva.pasos.documentos']);

        return response()->json([
            'message' => 'Flujo duplicado correctamente.',
            'flujo' => $nuevoFlujo,
        ], 201);
    }

    /**
     * DELETE /api/motor-flujos/flujos/{flujoId}
     * Eliminar (desactivar) un flujo.
     */
    public function eliminarFlujo(Request $request, int $flujoId): JsonResponse
    {
        $this->validarAdminUnidad($request);

        $flujo = Flujo::findOrFail($flujoId);
        $this->verificarAccesoSecretaria($request, $flujo);

        $flujo->update(['activo' => false]);

        return response()->json(['message' => 'Flujo eliminado.']);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // VALIDACIONES Y AUTORIZACIONES PRIVADAS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Verificar que el usuario es admin (general o de unidad).
     */
    private function validarAdminUnidad(Request $request): void
    {
        $user = $request->user();

        abort_unless($user, 401, 'No autenticado.');

        // Admin general o admin de la unidad solicitante puede modificar flujos
        $esAdmin = $user->hasRole(['admin', 'admin_general', 'super_admin', 'admin_unidad', 'admin_secretaria', 'jefe_unidad']);

        abort_unless($esAdmin, 403, 'Solo usuarios autorizados pueden modificar flujos.');
    }

    /**
     * Verificar que el admin pertenece a la misma secretaría del flujo.
     * Acepta un objeto Flujo o un int (secretaria_id).
     */
    private function verificarAccesoSecretaria(Request $request, $flujo): void
    {
        $user = $request->user();

        if ($this->puedeVerTodasSecretarias($user)) {
            return;
        }

        $secretariaId = $flujo instanceof Flujo ? $flujo->secretaria_id : (int) $flujo;

        // Admin de unidad solo puede modificar flujos de su secretaría
        abort_unless(
            $user->secretaria_id === $secretariaId,
            403,
            'No tiene acceso a modificar flujos de otra Secretaría.'
        );
    }

    /**
     * Define si el usuario puede operar/ver flujos de cualquier secretaría.
     * Se controla por configuración para habilitar modo "todas" en desarrollo.
     */
    private function puedeVerTodasSecretarias($user): bool
    {
        $scopeMode = strtolower((string) config('motor_flujos.secretaria_scope', 'roles'));

        if ($scopeMode === 'all') {
            return true;
        }

        $rolesPermitidos = config('motor_flujos.roles_ver_todas_secretarias', ['admin', 'admin_general', 'super_admin']);
        if (is_array($rolesPermitidos) && !empty($rolesPermitidos) && $user->hasRole($rolesPermitidos)) {
            return true;
        }

        $permiso = trim((string) config('motor_flujos.permission_ver_todas_secretarias', ''));
        if ($permiso !== '') {
            try {
                return $user->hasPermissionTo($permiso);
            } catch (\Throwable $exception) {
                return false;
            }
        }

        return false;
    }

    /**
     * Genera un código derivado para copias de flujo.
     */
    private function generarCodigoDuplicado(string $codigoBase): string
    {
        $base = strtoupper(trim($codigoBase));
        $base = preg_replace('/[^A-Z0-9_-]/', '-', $base) ?: 'FLUJO';
        $sufijo = '-CP' . now()->format('His') . random_int(10, 99);

        return substr($base, 0, max(1, 50 - strlen($sufijo))) . $sufijo;
    }

    /**
     * Verificar que un paso pertenece a una versión borrador.
     */
    private function validarPasoEditable(int $pasoId): void
    {
        $paso    = FlujoPaso::findOrFail($pasoId);
        $version = FlujoVersion::findOrFail($paso->flujo_version_id);

        abort_unless(
            $version->estado === 'borrador',
            422,
            'Solo se pueden modificar pasos de versiones en borrador.'
        );
    }

    /**
     * Autorizar que el usuario puede operar sobre una instancia.
     */
    private function autorizarAccionInstancia(Request $request, FlujoInstancia $instancia): void
    {
        $user = $request->user();

        // Admin general siempre puede
        if ($user->hasRole(['admin', 'admin_general'])) {
            return;
        }

        // El creador del proceso puede operar
        if ($instancia->creado_por === $user->id) {
            return;
        }

        // Verificar si el usuario tiene el rol del área responsable del paso actual
        $pasoActual = $instancia->pasos()->where('estado', 'en_progreso')->first();
        if ($pasoActual) {
            $areaResponsable = $pasoActual->flujoPaso->area_responsable_default;
            if ($areaResponsable && $user->hasRole($areaResponsable)) {
                return;
            }
        }

        abort(403, 'No tiene autorización para operar este proceso.');
    }
}
