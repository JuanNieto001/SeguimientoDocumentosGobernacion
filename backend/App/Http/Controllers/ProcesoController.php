<?php
/**
 * Archivo: backend/App/Http/Controllers/ProcesoController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AlertaService;

class ProcesoController extends Controller
{
    private function isPrivilegedUser($user): bool
    {
        $privilegedRoles = ['admin', 'admin_general', 'admin_secretaria', 'gobernador', 'secretario'];

        foreach ($privilegedRoles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    private function activeDocumentRoles($user): array
    {
        $rolesDoc = ['compras', 'talento_humano', 'rentas', 'contabilidad', 'inversiones_publicas', 'presupuesto', 'radicacion'];

        return array_values(array_filter($rolesDoc, fn ($role) => $user->hasRole($role)));
    }

    private function canUserViewProceso($user, \App\Models\Proceso $proceso): bool
    {
        if ($this->isPrivilegedUser($user) || $user->hasRole('planeacion')) {
            return true;
        }

        if ($user->hasRole('unidad_solicitante') && $proceso->created_by == $user->id) {
            return true;
        }

        $rolesArea = ['hacienda', 'juridica', 'secop'];
        foreach ($rolesArea as $role) {
            if (!$user->hasRole($role)) {
                continue;
            }

            $wasInArea = $proceso->procesoEtapas
                ->contains(fn ($pe) => optional($pe->etapa)->area_role === $role);

            if ($wasInArea || $proceso->area_actual_role === $role) {
                return true;
            }
        }

        $rolesDocActivos = $this->activeDocumentRoles($user);
        if (!empty($rolesDocActivos)) {
            $tieneSolicitudParaSuArea = DB::table('proceso_documentos_solicitados')
                ->where('proceso_id', $proceso->id)
                ->whereIn('area_responsable_rol', $rolesDocActivos)
                ->exists();

            if ($tieneSolicitudParaSuArea) {
                return true;
            }
        }

        return false;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Roles de solicitud de documentos (etapa 1 paralela)
        $miRolDoc = collect($this->activeDocumentRoles($user))->first();
        $isPrivileged = $this->isPrivilegedUser($user);

        $query = DB::table('procesos')
            ->leftJoin('workflows', 'workflows.id', '=', 'procesos.workflow_id')
            ->leftJoin('users as creador', 'creador.id', '=', 'procesos.created_by')
            ->leftJoin('etapas', 'etapas.id', '=', 'procesos.etapa_actual_id');

        // Para roles de doc: join para obtener fechas de recibido y enviado
        if ($miRolDoc) {
            $query->leftJoin(DB::raw('(SELECT proceso_id, area_responsable_rol, MIN(solicitado_at) as fecha_recibido, MAX(subido_at) as fecha_enviado FROM proceso_documentos_solicitados GROUP BY proceso_id, area_responsable_rol) as pds_fechas'), function ($join) use ($miRolDoc) {
                $join->on('pds_fechas.proceso_id', '=', 'procesos.id')
                     ->where('pds_fechas.area_responsable_rol', '=', $miRolDoc);
            });
            $query->addSelect([
                'procesos.*',
                'workflows.nombre as workflow_nombre',
                'workflows.codigo as workflow_codigo',
                'creador.name as creado_por_nombre',
                'etapas.nombre as etapa_nombre',
                'etapas.orden as etapa_orden',
                'pds_fechas.fecha_recibido as doc_fecha_recibido',
                'pds_fechas.fecha_enviado as doc_fecha_enviado',
            ]);
        } else {
            $query->select([
                'procesos.*',
                'workflows.nombre as workflow_nombre',
                'workflows.codigo as workflow_codigo',
                'creador.name as creado_por_nombre',
                'etapas.nombre as etapa_nombre',
                'etapas.orden as etapa_orden',
            ]);
        }

        $query->orderByDesc('procesos.id');

        // Visibilidad por rol
        if (!$isPrivileged) {
            if ($user->hasRole('planeacion') && !$miRolDoc) {
                // Planeación supervisa todos los procesos — sin filtro adicional
            } elseif ($user->hasRole('unidad_solicitante')) {
                $query->where('procesos.created_by', $user->id);
            } else {
                // Roles con bandeja propia (etapa de área en el workflow)
                $rolesArea = ['hacienda', 'juridica', 'secop'];
                $miRolArea = collect($rolesArea)->first(fn ($r) => $user->hasRole($r));

                if ($miRolDoc) {
                    // Roles de solicitud de documentos tienen prioridad (etapa 1 paralela)
                    $query->whereIn('procesos.id', function ($sub) use ($miRolDoc) {
                        $sub->select('proceso_id')
                            ->from('proceso_documentos_solicitados')
                            ->where('area_responsable_rol', $miRolDoc);
                    });
                } elseif ($miRolArea) {
                    $query->where(function ($q) use ($miRolArea) {
                        $q->where('procesos.area_actual_role', $miRolArea)
                          ->orWhereIn('procesos.id', function ($sub) use ($miRolArea) {
                              $sub->select('pe.proceso_id')
                                  ->from('proceso_etapas as pe')
                                  ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                                  ->where('e.area_role', $miRolArea)
                                  ->where('pe.enviado', true);
                          });
                    });
                } else {
                    $query->whereRaw('1=0');
                }
            }
        }

        // ── Filtros desde GET ────────────────────────────────────────
        if ($buscar = $request->input('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('procesos.codigo', 'like', "%{$buscar}%")
                  ->orWhere('procesos.objeto', 'like', "%{$buscar}%")
                  ->orWhere('procesos.contratista_nombre', 'like', "%{$buscar}%");
            });
        }
        if ($estado = $request->input('estado')) {
            $query->where('procesos.estado', $estado);
        }
        if ($etapa = $request->input('etapa')) {
            $query->where('etapas.orden', $etapa);
        }
        if ($secretariaId = $request->input('secretaria_id')) {
            $query->where('procesos.secretaria_origen_id', $secretariaId);
        }
        if ($unidadId = $request->input('unidad_id')) {
            $query->where('procesos.unidad_origen_id', $unidadId);
        }

        $procesos = $query->get();

        // Datos para filtros
        $etapas = DB::table('etapas')
            ->where('workflow_id', 1)
            ->where('activa', 1)
            ->orderBy('orden')
            ->get(['id', 'orden', 'nombre']);

        return view('procesos.index', compact('procesos', 'etapas', 'miRolDoc'));
    }

    public function create()
    {
        $user = auth()->user();
        // El middleware de ruta ya valida el permiso; doble check por seguridad
        abort_unless($user->can('procesos.crear'), 403);

        // Cargar flujos del Motor de Flujos para el selector
        $flujos = DB::table('flujos')
            ->leftJoin('secretarias', 'secretarias.id', '=', 'flujos.secretaria_id')
            ->where('flujos.activo', 1)
            ->orderBy('flujos.nombre')
            ->select('flujos.*', 'secretarias.nombre as secretaria_nombre')
            ->get();

        $secretarias = DB::table('secretarias')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();

        // Pre-cargar unidades de la secretaría del usuario
        $unidadesPreload = [];
        $userSecretaria  = null;
        $userUnidad      = null;
        if ($user->secretaria_id) {
            $userSecretaria  = DB::table('secretarias')->where('id', $user->secretaria_id)->first();
            $unidadesPreload = DB::table('unidades')
                ->where('secretaria_id', $user->secretaria_id)
                ->where('activo', 1)
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
                ->toArray();
        }
        if ($user->unidad_id) {
            $userUnidad = DB::table('unidades')->where('id', $user->unidad_id)->first();
            // Si el usuario tiene unidad pero no secretaría, derivarla automáticamente
            if (!$userSecretaria && $userUnidad && $userUnidad->secretaria_id) {
                $userSecretaria  = DB::table('secretarias')->where('id', $userUnidad->secretaria_id)->first();
                $unidadesPreload = DB::table('unidades')
                    ->where('secretaria_id', $userUnidad->secretaria_id)
                    ->where('activo', 1)
                    ->orderBy('nombre')
                    ->get(['id', 'nombre'])
                    ->toArray();
            }
        }

        return view('procesos.create', compact('flujos', 'secretarias', 'unidadesPreload', 'userSecretaria', 'userUnidad'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->can('procesos.crear'), 403);

        $data = $request->validate([
            'flujo_id'                   => ['required', 'exists:flujos,id'],
            'objeto'                     => ['required', 'string', 'max:255'],
            'descripcion'                => ['nullable', 'string'],
            'secretaria_origen_id'       => ['required', 'exists:secretarias,id'],
            'unidad_origen_id'           => ['required', 'exists:unidades,id'],
            'valor_estimado'             => ['nullable', 'numeric', 'min:0'],
            'plazo_ejecucion_meses'      => ['required', 'integer', 'min:1', 'max:60'],
            'contratista_nombre'         => ['nullable', 'string', 'max:255'],
            'contratista_documento'      => ['nullable', 'string', 'max:50'],
            'contratista_tipo_documento' => ['nullable', 'string', 'max:10'],
            'estudios_previos'           => ['required', 'file', 'max:10240'],
        ], [
            'estudios_previos.required' => 'Debe cargar el archivo de Estudios Previos.',
            'estudios_previos.file' => 'El archivo de Estudios Previos no es válido.',
            'estudios_previos.max' => 'El archivo de Estudios Previos no puede superar los 10 MB.',
            'plazo_ejecucion_meses.required' => 'El plazo de ejecución es obligatorio.',
            'plazo_ejecucion_meses.min' => 'El plazo de ejecución debe ser al menos 1 mes.',
            'plazo_ejecucion_meses.max' => 'El plazo de ejecución no puede superar 60 meses.',
        ]);

        // Buscar el flujo seleccionado
        $flujo = DB::table('flujos')->where('id', $data['flujo_id'])->first();
        abort_unless($flujo, 422, 'Flujo no encontrado.');

        // Sincronizar flujo → workflow/etapas/items (bridge)
        $workflowId = $this->syncFlujoToWorkflow($flujo);

        // Autogenerar código consecutivo usando el código del flujo
        $data['codigo'] = $this->generarCodigoConsecutivoFlujo($flujo->codigo);

        return DB::transaction(function () use ($data, $user, $request, $flujo, $workflowId) {

            // 1) Buscar etapas del workflow sincronizado
            $primeraEtapa = null;
            $segundaEtapa = null;
            if ($workflowId) {
                $primeraEtapa = DB::table('etapas')
                    ->where('workflow_id', $workflowId)
                    ->where('activa', 1)
                    ->orderBy('orden')
                    ->first();

                $segundaEtapa = DB::table('etapas')
                    ->where('workflow_id', $workflowId)
                    ->where('activa', 1)
                    ->orderBy('orden')
                    ->skip(1)
                    ->first();
            }

            // 2) Crear proceso
            $procesoId = DB::table('procesos')->insertGetId([
                'workflow_id'                => $workflowId,
                'flujo_id'                   => $data['flujo_id'],
                'codigo'                     => $data['codigo'],
                'objeto'                     => $data['objeto'],
                'descripcion'                => $data['descripcion'] ?? null,
                'contratista_nombre'         => $data['contratista_nombre'] ?? null,
                'contratista_documento'      => $data['contratista_documento'] ?? null,
                'contratista_tipo_documento' => $data['contratista_tipo_documento'] ?? null,
                'valor_estimado'             => $data['valor_estimado'] ?? null,
                'plazo_ejecucion'            => $data['plazo_ejecucion_meses'] . ' meses',
                'secretaria_origen_id'       => $data['secretaria_origen_id'],
                'unidad_origen_id'           => $data['unidad_origen_id'],
                'estado'                     => 'EN_CURSO',
                'etapa_actual_id'            => $segundaEtapa ? $segundaEtapa->id : ($primeraEtapa ? $primeraEtapa->id : null),
                'area_actual_role'           => $segundaEtapa ? $segundaEtapa->area_role : ($primeraEtapa ? $primeraEtapa->area_role : null),
                'created_by'                 => $user->id,
                'created_at'                 => now(),
                'updated_at'                 => now(),
            ]);

            // 3) ✅ Crear instancia de Etapa 0 como COMPLETADA AUTOMÁTICAMENTE (si hay etapas)
            if (!$primeraEtapa) {
                // Sin workflow/etapas: solo redirigir
                return redirect()->route('procesos.index')
                    ->with('success', 'Solicitud creada correctamente.');
            }

            $procesoEtapa0Id = DB::table('proceso_etapas')->insertGetId([
                'proceso_id'   => $procesoId,
                'etapa_id'     => $primeraEtapa->id,
                'recibido'     => true,
                'recibido_por' => $user->id,
                'recibido_at'  => now(),
                'enviado'      => true,
                'enviado_por'  => $user->id,
                'enviado_at'   => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // 4) Crear checks de Etapa 0 TODOS MARCADOS
            $items = DB::table('etapa_items')
                ->where('etapa_id', $primeraEtapa->id)
                ->orderBy('orden')
                ->get(['id']);

            foreach ($items as $item) {
                DB::table('proceso_etapa_checks')->insert([
                    'proceso_etapa_id' => $procesoEtapa0Id,
                    'etapa_item_id'    => $item->id,
                    'checked'          => true,
                    'checked_by'       => $user->id,
                    'checked_at'       => now(),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
            
            // 5) ✅ Subir archivo de Estudios Previos
            if ($request->hasFile('estudios_previos')) {
                $file = $request->file('estudios_previos');
                $nombreGuardado = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                $ruta = "procesos/{$procesoId}/etapa_{$primeraEtapa->id}/{$nombreGuardado}";
                $file->storeAs('public/' . dirname($ruta), basename($ruta));
                
                DB::table('proceso_etapa_archivos')->insert([
                    'proceso_id'       => $procesoId,
                    'proceso_etapa_id' => $procesoEtapa0Id,
                    'etapa_id'         => $primeraEtapa->id,
                    'tipo_archivo'     => 'estudios_previos',
                    'nombre_original'  => $file->getClientOriginalName(),
                    'nombre_guardado'  => $nombreGuardado,
                    'ruta'             => $ruta,
                    'mime_type'        => $file->getMimeType(),
                    'tamanio'          => $file->getSize(),
                    'estado'           => 'aprobado',
                    'uploaded_by'      => $user->id,
                    'uploaded_at'      => now(),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
            
            // 6) ✅ Crear instancia de Etapa 1 (Descentralización) si existe
            if ($segundaEtapa) {
                // Si el creador es planeación (misma área), auto-marcar como recibido
                $creadoPorMismaArea = $user->hasRole('planeacion');
                $procesoEtapa1Id = DB::table('proceso_etapas')->insertGetId([
                    'proceso_id'   => $procesoId,
                    'etapa_id'     => $segundaEtapa->id,
                    'recibido'     => $creadoPorMismaArea,
                    'recibido_por' => $creadoPorMismaArea ? $user->id : null,
                    'recibido_at'  => $creadoPorMismaArea ? now() : null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                
                // Crear checks de Etapa 1
                $items1 = DB::table('etapa_items')
                    ->where('etapa_id', $segundaEtapa->id)
                    ->orderBy('orden')
                    ->get(['id']);

                foreach ($items1 as $item) {
                    DB::table('proceso_etapa_checks')->insert([
                        'proceso_etapa_id' => $procesoEtapa1Id,
                        'etapa_item_id'    => $item->id,
                        'checked'          => false,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            // 7) ✅ Notificar al área responsable de la Etapa 1 (1 alerta por área)
            if ($segundaEtapa) {
                // Convertimos el role técnico en etiqueta funcional para el mensaje.
                $areaLabel = match($segundaEtapa->area_role) {
                    'unidad_solicitante' => 'Unidad Solicitante',
                    'planeacion'         => 'Planeación',
                    'hacienda'           => 'Hacienda',
                    'juridica'           => 'Jurídica',
                    'secop'              => 'SECOP',
                    default              => ucfirst($segundaEtapa->area_role),
                };

                // Cargamos modelo para usar servicio de alertas tipado por proceso.
                $procesoModel = \App\Models\Proceso::find($procesoId);
                if ($procesoModel) {
                    // Notificación de bandeja al área que recibirá el proceso en su etapa inicial.
                    AlertaService::crearParaArea(
                        proceso: $procesoModel,
                        tipo: 'proceso_recibido',
                        titulo: 'Nuevo proceso asignado',
                        mensaje: "Nuevo proceso {$data['codigo']} recibido en {$areaLabel} - {$segundaEtapa->nombre}",
                        areaRole: $segundaEtapa->area_role,
                        prioridad: 'alta',
                        metadata: [
                            'etapa_id' => $segundaEtapa->id,
                        ],
                        accionUrl: route('procesos.show', $procesoId)
                    );
                }
            }

            // 8) Redirigir a "Mis Solicitudes" (/procesos)
            return redirect()->route('procesos.index')
                ->with('success', 'Solicitud creada y enviada a Descentralización correctamente.');
        });
    }

    public function show(int $id)
    {
        $user = auth()->user();

        $proceso = \App\Models\Proceso::with([
            'workflow',
            'etapaActual',
            'procesoEtapas.etapa',
            'procesoEtapas.checks.item',
            'archivos',
            'creador',
            'secretariaOrigen',
            'unidadOrigen',
        ])->findOrFail($id);

        // Autorización por rol (unificada para todas las bandejas)
        abort_unless($this->canUserViewProceso($user, $proceso), 403, 'No tienes acceso a este proceso.');

        // Todas las etapas del workflow (para el timeline)
        $etapas = DB::table('etapas')
            ->where('workflow_id', $proceso->workflow_id)
            ->orderBy('orden')
            ->get();

        // Índice de etapas completadas (orden de la etapa actual)
        $currentOrden = optional($proceso->etapaActual)->orden ?? -1;

        // Mapa etapa_id -> proceso_etapa para acceso rápido
        $peMap = $proceso->procesoEtapas->keyBy('etapa_id');

        // Audit log
        $auditoria = DB::table('proceso_auditoria')
            ->leftJoin('users', 'users.id', '=', 'proceso_auditoria.user_id')
            ->select('proceso_auditoria.*', 'users.name as user_name')
            ->where('proceso_auditoria.proceso_id', $id)
            ->orderByDesc('proceso_auditoria.created_at')
            ->limit(30)
            ->get();

        return view('procesos.show', compact('proceso', 'etapas', 'currentOrden', 'peMap', 'auditoria'));
    }

        /**
         * Sincroniza un flujo del Motor de Flujos al sistema viejo de workflow/etapas/etapa_items.
         * Crea (o actualiza) un workflow exclusivo para el flujo, con etapas e items que
         * reflejan los pasos y documentos configurados en el Motor de Flujos.
         * De esta forma, todas las vistas de bandejas existentes siguen funcionando.
         */
        private function syncFlujoToWorkflow($flujo): ?int
        {
            if (!$flujo || !$flujo->version_activa_id) {
                return null;
            }

            $workflowCodigo = 'FLUJO_' . $flujo->id;

            // 1) Encontrar o crear workflow exclusivo para este flujo
            $workflow = DB::table('workflows')->where('codigo', $workflowCodigo)->first();

            if (!$workflow) {
                $workflowId = DB::table('workflows')->insertGetId([
                    'codigo'     => $workflowCodigo,
                    'nombre'     => $flujo->nombre,
                    'activo'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $workflowId = $workflow->id;
                DB::table('workflows')->where('id', $workflowId)->update([
                    'nombre'     => $flujo->nombre,
                    'updated_at' => now(),
                ]);
            }

            // 2) Verificar si ya hay procesos activos usando este workflow
            //    Si los hay, NO resincrono etapas (podrían romper procesos en curso)
            $procesosActivos = DB::table('procesos')
                ->where('workflow_id', $workflowId)
                ->where('estado', 'EN_CURSO')
                ->count();

            // 3) Obtener pasos del flujo (versión activa)
            $pasos = DB::table('flujo_pasos')
                ->where('flujo_version_id', $flujo->version_activa_id)
                ->where('activo', 1)
                ->orderBy('orden')
                ->get();

            if ($pasos->isEmpty()) {
                return $workflowId;
            }

            // 4) Obtener etapas actuales del workflow
            $etapasActuales = DB::table('etapas')
                ->where('workflow_id', $workflowId)
                ->orderBy('orden')
                ->get();

            // Si no hay etapas o no hay procesos activos, reconstruir etapas
            if ($etapasActuales->isEmpty() || $procesosActivos === 0) {
                // Limpiar etapas antiguas (cascade borra etapa_items)
                if ($etapasActuales->isNotEmpty()) {
                    $etapaIds = $etapasActuales->pluck('id');
                    DB::table('etapa_items')->whereIn('etapa_id', $etapaIds)->delete();
                    DB::table('etapas')->whereIn('id', $etapaIds)->delete();
                }

                // Crear etapas desde los pasos del flujo
                $prevEtapaId = null;
                foreach ($pasos as $paso) {
                    $etapaId = DB::table('etapas')->insertGetId([
                        'workflow_id'    => $workflowId,
                        'orden'          => $paso->orden,
                        'nombre'         => $paso->nombre_personalizado ?? 'Paso ' . ($paso->orden + 1),
                        'descripcion'    => $paso->instrucciones,
                        'area_role'      => $paso->area_responsable_default ?? 'unidad_solicitante',
                        'dias_estimados' => $paso->dias_estimados,
                        'activa'         => true,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);

                    // Encadenar etapas
                    if ($prevEtapaId) {
                        DB::table('etapas')->where('id', $prevEtapaId)->update([
                            'next_etapa_id' => $etapaId,
                        ]);
                    }
                    $prevEtapaId = $etapaId;

                    // Crear etapa_items desde flujo_paso_documentos
                    $docs = DB::table('flujo_paso_documentos')
                        ->where('flujo_paso_id', $paso->id)
                        ->where('activo', 1)
                        ->orderBy('orden')
                        ->get();

                    foreach ($docs as $doc) {
                        DB::table('etapa_items')->insert([
                            'etapa_id'   => $etapaId,
                            'orden'      => $doc->orden,
                            'label'      => $doc->nombre,
                            'requerido'  => $doc->es_obligatorio,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            return $workflowId;
        }

        /**
         * Genera un código único: PREFIJO-AAAA-#### por workflow y año.
         */
        private function generarCodigoConsecutivo(int $workflowId): string
        {
            $workflow = DB::table('workflows')->where('id', $workflowId)->first();
            abort_unless($workflow, 422, 'Workflow inválido.');

            $prefijo = $workflow->codigo ?? 'WF';
            $year = now()->year;

            $like = $prefijo . '-' . $year . '-%';
            $ultimo = DB::table('procesos')
                ->where('codigo', 'like', $like)
                ->orderByDesc('codigo')
                ->value('codigo');

            $siguiente = 1;
            if ($ultimo) {
                $partes = explode('-', $ultimo);
                $numero = (int) end($partes);
                $siguiente = $numero + 1;
            }

            $numeroFmt = str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
            return "$prefijo-$year-$numeroFmt";
        }

        /**
         * Genera código consecutivo usando el código del flujo como prefijo.
         */
        private function generarCodigoConsecutivoFlujo(string $flujoCodigo): string
        {
            $prefijo = $flujoCodigo;
            $year = now()->year;

            $like = $prefijo . '-' . $year . '-%';
            $ultimo = DB::table('procesos')
                ->where('codigo', 'like', $like)
                ->orderByDesc('codigo')
                ->value('codigo');

            $siguiente = 1;
            if ($ultimo) {
                $partes = explode('-', $ultimo);
                $numero = (int) end($partes);
                $siguiente = $numero + 1;
            }

            $numeroFmt = str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
            return "$prefijo-$year-$numeroFmt";
        }
}

