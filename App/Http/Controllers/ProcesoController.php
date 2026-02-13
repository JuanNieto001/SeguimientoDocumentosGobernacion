<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcesoController extends Controller
{
    // Admin ve todos; áreas ven solo los que están en su rol
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $procesos = DB::table('procesos')
                ->orderByDesc('id')
                ->get();
        } else {
            // toma el primer rol "de área"
            $role = $user->getRoleNames()->first();

            $procesos = DB::table('procesos')
                ->where('area_actual_role', $role)
                ->orderByDesc('id')
                ->get();
        }

        return view('procesos.index', compact('procesos'));
    }

    public function create()
    {
        return view('procesos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'string', 'max:50', 'unique:procesos,codigo'],
            'objeto' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $userId = Auth::id();

        return DB::transaction(function () use ($request, $userId) {

            // 1) primera etapa (orden 1)
            $firstEtapa = DB::table('etapas')
                ->where('activa', 1)
                ->orderBy('orden')
                ->first();

            if (!$firstEtapa) {
                abort(500, 'No hay etapas configuradas. Ejecuta el WorkflowSeeder.');
            }

            // 2) crear proceso
            $procesoId = DB::table('procesos')->insertGetId([
                'codigo' => $request->codigo,
                'objeto' => $request->objeto,
                'descripcion' => $request->descripcion,
                'estado' => 'EN_CURSO',
                'etapa_actual_id' => $firstEtapa->id,
                'area_actual_role' => $firstEtapa->area_role,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3) crear proceso_etapas por cada etapa
            $etapas = DB::table('etapas')
                ->where('activa', 1)
                ->orderBy('orden')
                ->get();

            foreach ($etapas as $etapa) {
                $procesoEtapaId = DB::table('proceso_etapas')->insertGetId([
                    'proceso_id' => $procesoId,
                    'etapa_id' => $etapa->id,
                    'recibido' => false,
                    'enviado' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4) crear checks por cada item de la etapa
                $items = DB::table('etapa_items')
                    ->where('etapa_id', $etapa->id)
                    ->orderBy('orden')
                    ->get();

                foreach ($items as $item) {
                    DB::table('proceso_etapa_checks')->insert([
                        'proceso_etapa_id' => $procesoEtapaId,
                        'etapa_item_id' => $item->id,
                        'checked' => false,
                        'checked_by' => null,
                        'checked_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return redirect()->route('procesos.index')
                ->with('success', 'Proceso creado y flujo instanciado correctamente.');
        });
    }
}
