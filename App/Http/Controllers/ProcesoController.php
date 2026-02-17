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

        if (!$user->hasRole('admin')) {

            if ($user->hasRole('unidad_solicitante')) {
                $query->where('procesos.created_by', $user->id);
            } else {
                $rolesArea = ['planeacion', 'hacienda', 'juridica', 'secop'];
                $miRolArea = collect($rolesArea)->first(fn ($r) => $user->hasRole($r));

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
            'codigo'      => ['required', 'string', 'max:60', 'unique:procesos,codigo'],
            'objeto'      => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($data, $user) {

            // Siempre iniciar en la primera etapa de Unidad; si no existe, fallback a la primera por orden
            $primeraEtapa = DB::table('etapas')
                ->where('workflow_id', $data['workflow_id'])
                ->where('activa', 1)
                ->where('area_role', 'unidad_solicitante')
                ->orderBy('orden')
                ->first();

            if (!$primeraEtapa) {
                $primeraEtapa = DB::table('etapas')
                    ->where('workflow_id', $data['workflow_id'])
                    ->where('activa', 1)
                    ->orderBy('orden')
                    ->first();
            }

            abort_unless($primeraEtapa, 422, 'El workflow seleccionado no tiene etapas activas.');

            // Crear proceso
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

            // Crear instancia de etapa inicial
            DB::table('proceso_etapas')->insert([
                'proceso_id' => $procesoId,
                'etapa_id'   => $primeraEtapa->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Redirigir a la bandeja
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
}
