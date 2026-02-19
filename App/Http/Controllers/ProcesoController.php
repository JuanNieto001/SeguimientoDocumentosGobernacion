<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcesoController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = DB::table('procesos')
            ->leftJoin('workflows', 'workflows.id', '=', 'procesos.workflow_id')
            ->select([
                'procesos.*',
                'workflows.nombre as workflow_nombre',
                'workflows.codigo as workflow_codigo',
            ])
            ->orderByDesc('procesos.id');

        // Admin ve todo
        if (!$user->hasRole('admin')) {

            if ($user->hasRole('planeacion')) {
                // Planeación gestiona TODOS los procesos del sistema
                // (los crea y supervisa de inicio a fin)
                // No se aplica filtro adicional
            } elseif ($user->hasRole('unidad_solicitante')) {
                // Unidad solicitante: ve los procesos que creó
                $query->where('procesos.created_by', $user->id);
            } else {
                // Áreas (hacienda, juridica, secop):
                // ven lo que esté en su bandeja O ya hayan procesado
                $rolesArea = ['hacienda', 'juridica', 'secop'];
                $miRolArea = collect($rolesArea)->first(fn ($r) => $user->hasRole($r));

                if (!$miRolArea) {
                    $query->whereRaw('1=0');
                } else {
                    $query->where(function ($q) use ($miRolArea) {
                        // En bandeja ahora
                        $q->where('procesos.area_actual_role', $miRolArea)
                          // O ya pasó por esta área (enviado)
                          ->orWhereIn('procesos.id', function ($sub) use ($miRolArea) {
                              $sub->select('pe.proceso_id')
                                  ->from('proceso_etapas as pe')
                                  ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                                  ->where('e.area_role', $miRolArea)
                                  ->where('pe.enviado', true);
                          });
                    });
                }
            }
        }

        $procesos = $query->get();

        return view('procesos.index', compact('procesos'));
    }

    public function create()
    {
        $user = auth()->user();
        // El middleware de ruta ya valida el rol; doble check por seguridad
        abort_unless(
            $user->hasRole('admin') || $user->hasRole('admin_general')
            || $user->hasRole('planeacion') || $user->hasRole('unidad_solicitante'),
            403
        );

        $workflows = DB::table('workflows')
            ->where('activo', 1)
            ->orderBy('nombre')
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
        }

        return view('procesos.create', compact('workflows', 'secretarias', 'unidadesPreload', 'userSecretaria', 'userUnidad'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('admin') || $user->hasRole('admin_general')
            || $user->hasRole('planeacion') || $user->hasRole('unidad_solicitante'),
            403
        );

        $data = $request->validate([
            'workflow_id'                => ['required', 'exists:workflows,id'],
            'objeto'                     => ['required', 'string', 'max:255'],
            'descripcion'                => ['nullable', 'string'],
            'secretaria_origen_id'       => ['required', 'exists:secretarias,id'],
            'unidad_origen_id'           => ['required', 'exists:unidades,id'],
            'valor_estimado'             => ['nullable', 'numeric', 'min:0'],
            'plazo_ejecucion'            => ['nullable', 'string', 'max:100'],
            'contratista_nombre'         => ['nullable', 'string', 'max:255'],
            'contratista_documento'      => ['nullable', 'string', 'max:50'],
            'contratista_tipo_documento' => ['nullable', 'string', 'max:10'],
        ]);

        // Autogenerar código consecutivo por workflow y año
        $data['codigo'] = $this->generarCodigoConsecutivo((int) $data['workflow_id']);

        return DB::transaction(function () use ($data, $user) {

            // 1) Buscar etapa inicial del workflow: la de menor orden (incluye 0 si existe)
            $primeraEtapa = DB::table('etapas')
                ->where('workflow_id', $data['workflow_id'])
                ->where('activa', 1)
                ->orderBy('orden')
                ->first();

            if (!$primeraEtapa) {
                abort(422, 'El workflow seleccionado no tiene etapas activas.');
            }

            // 2) Crear proceso
            $procesoId = DB::table('procesos')->insertGetId([
                'workflow_id'                => $data['workflow_id'],
                'codigo'                     => $data['codigo'],
                'objeto'                     => $data['objeto'],
                'descripcion'                => $data['descripcion'] ?? null,
                'contratista_nombre'         => $data['contratista_nombre'] ?? null,
                'contratista_documento'      => $data['contratista_documento'] ?? null,
                'contratista_tipo_documento' => $data['contratista_tipo_documento'] ?? null,
                'valor_estimado'             => $data['valor_estimado'] ?? null,
                'plazo_ejecucion'            => $data['plazo_ejecucion'] ?? null,
                'secretaria_origen_id'       => $data['secretaria_origen_id'],
                'unidad_origen_id'           => $data['unidad_origen_id'],
                'estado'                     => 'EN_CURSO',
                'etapa_actual_id'            => $primeraEtapa->id,
                'area_actual_role'           => $primeraEtapa->area_role,
                'created_by'                 => $user->id,
                'created_at'                 => now(),
                'updated_at'                 => now(),
            ]);

            // 3) Crear instancia de etapa inicial
            $creadoPorUnidad = $user->hasRole('unidad_solicitante') && $primeraEtapa->area_role === 'unidad_solicitante';
            $procesoEtapaId = DB::table('proceso_etapas')->insertGetId([
                'proceso_id'   => $procesoId,
                'etapa_id'     => $primeraEtapa->id,
                'recibido'     => $creadoPorUnidad,
                'recibido_por' => $creadoPorUnidad ? $user->id : null,
                'recibido_at'  => $creadoPorUnidad ? now() : null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // 4) Crear checks de esa etapa
            $items = DB::table('etapa_items')
                ->where('etapa_id', $primeraEtapa->id)
                ->orderBy('orden')
                ->get(['id']);

            foreach ($items as $item) {
                DB::table('proceso_etapa_checks')->insert([
                    'proceso_etapa_id' => $procesoEtapaId,
                    'etapa_item_id'    => $item->id,
                    'checked'          => false,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            // 5) Redirigir a la bandeja correcta (según el área actual)
            $url = match ($primeraEtapa->area_role) {
                'unidad_solicitante' => url('/unidad?proceso_id=' . $procesoId),
                'planeacion'         => url('/planeacion?proceso_id=' . $procesoId),
                'hacienda'           => url('/hacienda?proceso_id=' . $procesoId),
                'juridica'           => url('/juridica?proceso_id=' . $procesoId),
                'secop'              => url('/secop?proceso_id=' . $procesoId),
                default              => route('procesos.index'),
            };

            return redirect($url)->with('success', 'Solicitud creada correctamente.');
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

        // Autorización por rol
        if (!$user->hasRole('admin') && !$user->hasRole('planeacion')) {
            $canView = false;

            if ($user->hasRole('unidad_solicitante') && $proceso->created_by == $user->id) {
                $canView = true;
            }

            if (!$canView) {
                $areaRoles = ['hacienda', 'juridica', 'secop'];
                foreach ($areaRoles as $role) {
                    if ($user->hasRole($role)) {
                        $wasInArea = $proceso->procesoEtapas
                            ->contains(fn ($pe) => optional($pe->etapa)->area_role === $role);
                        if ($wasInArea || $proceso->area_actual_role === $role) {
                            $canView = true;
                        }
                        break;
                    }
                }
            }

            abort_unless($canView, 403, 'No tienes acceso a este proceso.');
        }

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
         * Genera un código único: PREFIJO-AAAA-#### por workflow y año.
         */
        private function generarCodigoConsecutivo(int $workflowId): string
        {
            $workflow = DB::table('workflows')->where('id', $workflowId)->first();
            abort_unless($workflow, 422, 'Workflow inválido.');

            $prefijo = $workflow->codigo ?? 'WF';
            $year = now()->year;

            // Buscar el mayor consecutivo existente para ese prefijo-año
            $like = $prefijo . '-' . $year . '-%';
            $ultimo = DB::table('procesos')
                ->where('codigo', 'like', $like)
                ->orderByDesc('codigo')
                ->value('codigo');

            $siguiente = 1;
            if ($ultimo) {
                // Extraer la última parte numérica
                $partes = explode('-', $ultimo);
                $numero = (int) end($partes);
                $siguiente = $numero + 1;
            }

            $numeroFmt = str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
            return "$prefijo-$year-$numeroFmt";
        }
}
