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

            // Si es unidad_solicitante: ve los procesos que creó
            if ($user->hasRole('unidad_solicitante')) {
                $query->where('procesos.created_by', $user->id);
            } else {
                // Si es un área (planeacion, hacienda, juridica, secop):
                // ve lo que esté en su bandeja (area_actual_role)
                $rolesArea = ['planeacion', 'hacienda', 'juridica', 'secop'];

                $miRolArea = collect($rolesArea)->first(fn ($r) => $user->hasRole($r));

                // Si no tiene ninguno de esos roles, no ve nada
                if (!$miRolArea) {
                    $query->whereRaw('1=0');
                } else {
                    $query->where('procesos.area_actual_role', $miRolArea);
                }
            }
        }

        $procesos = $query->get();

        return view('procesos.index', compact('procesos'));
    }

    public function create()
    {
        $user = auth()->user();
        abort_unless($user->hasRole('admin') || $user->hasRole('unidad_solicitante'), 403);

        $workflows = DB::table('workflows')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view('procesos.create', compact('workflows'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('admin') || $user->hasRole('unidad_solicitante'), 403);

        $data = $request->validate([
            'workflow_id' => ['required', 'exists:workflows,id'],
            'objeto'      => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
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
                'workflow_id'      => $data['workflow_id'],
                'codigo'           => $data['codigo'],
                'objeto'           => $data['objeto'],
                'descripcion'      => $data['descripcion'] ?? null,
                'estado'           => 'EN_CURSO',
                'etapa_actual_id'  => $primeraEtapa->id,
                'area_actual_role' => $primeraEtapa->area_role,
                'created_by'       => $user->id,
                'created_at'       => now(),
                'updated_at'       => now(),
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
