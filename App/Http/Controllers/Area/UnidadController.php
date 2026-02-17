<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UnidadController extends Controller
{
    public function index()
    {
        $areaRole = 'unidad_solicitante';
        $user = auth()->user();

        // Solo procesos EN_CURSO en esta bandeja
        $procesosQuery = DB::table('procesos')
            ->where('area_actual_role', $areaRole)
            ->where('estado', 'EN_CURSO')
            ->orderByDesc('id');

        // Admin ve todos; unidad ve solo los creados por él/ella
        if (!$user->hasRole('admin')) {
            $procesosQuery->where('created_by', $user->id);
        }

        $procesos = $procesosQuery->get();

        // Selección segura
        $requestedId = request('proceso_id');
        $selectedId = null;

        if ($requestedId) {
            $existsInList = $procesos->firstWhere('id', (int)$requestedId);
            $selectedId = $existsInList ? (int)$requestedId : null;
        }

        if (!$selectedId) {
            $selectedId = $procesos->first()->id ?? null;
        }

        $proceso = $selectedId
            ? DB::table('procesos')->where('id', $selectedId)->first()
            : null;

        // Para unidad: solo necesitamos procesoEtapa (para archivos) y permitir enviar
        $procesoEtapa = null;

        if ($proceso) {
            $procesoEtapa = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            // crear si no existe
            if (!$procesoEtapa) {
                $id = DB::table('proceso_etapas')->insertGetId([
                    'proceso_id' => $proceso->id,
                    'etapa_id'   => $proceso->etapa_actual_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $procesoEtapa = DB::table('proceso_etapas')->where('id', $id)->first();
            }
        }

        // En unidad, el botón enviar se controla por backend con validateArchivosUnidad()
        $enviarHabilitado = (bool)$proceso;

        // checks vacío (para no romper vista si aún lo usa)
        $checks = collect();

        return view('areas.unidad', compact(
            'areaRole', 'procesos', 'proceso', 'procesoEtapa', 'checks', 'enviarHabilitado'
        ));
    }
}
