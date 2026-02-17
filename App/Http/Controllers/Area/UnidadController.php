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

        // 1) Listado de procesos que están actualmente en Unidad
        // Admin ve todos los que están en esta área.
        // Unidad solicitante ve solo los creados por él/ella.
        $procesosQuery = DB::table('procesos')
            ->where('area_actual_role', $areaRole)
            ->orderByDesc('id');

        if (!$user->hasRole('admin')) {
            $procesosQuery->where('created_by', $user->id);
        }

        $procesos = $procesosQuery->get();

        // 2) Selección segura: si viene proceso_id por querystring, validar que exista en la lista
        $requestedId = request('proceso_id');
        $selectedId = null;

        if ($requestedId) {
            $existsInList = $procesos->firstWhere('id', (int) $requestedId);
            $selectedId = $existsInList ? (int) $requestedId : null;
        }

        if (!$selectedId) {
            $selectedId = $procesos->first()->id ?? null;
        }

        // 3) Cargar proceso seleccionado (si hay)
        $proceso = $selectedId
            ? DB::table('procesos')->where('id', $selectedId)->first()
            : null;

        $procesoEtapa = null;
        $checks = collect();
        $enviarHabilitado = false;

        if ($proceso) {

            // 4) Traer/crear la instancia de la etapa actual del proceso (proceso_etapas)
            // IMPORTANTE: aquí lo creamos si no existe para evitar null->id
            $procesoEtapa = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            if (!$procesoEtapa) {
                $procesoEtapaId = DB::table('proceso_etapas')->insertGetId([
                    'proceso_id' => $proceso->id,
                    'etapa_id'   => $proceso->etapa_actual_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $procesoEtapa = DB::table('proceso_etapas')->where('id', $procesoEtapaId)->first();
            }

            // 5) Seed checks si faltan (para que la vista siempre tenga checklist)
            $checksCount = DB::table('proceso_etapa_checks')
                ->where('proceso_etapa_id', $procesoEtapa->id)
                ->count();

            if ($checksCount === 0) {
                $items = DB::table('etapa_items')
                    ->where('etapa_id', $proceso->etapa_actual_id)
                    ->orderBy('orden')
                    ->get(['id']);

                foreach ($items as $item) {
                    DB::table('proceso_etapa_checks')->insert([
                        'proceso_etapa_id' => $procesoEtapa->id,
                        'etapa_item_id'    => $item->id,
                        'checked'          => false,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            // 6) Cargar checklist
            $checks = DB::table('proceso_etapa_checks as pc')
                ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
                ->select('pc.id as check_id', 'pc.checked', 'ei.label', 'ei.requerido')
                ->where('pc.proceso_etapa_id', $procesoEtapa->id)
                ->orderBy('ei.orden')
                ->get();

            // 7) Habilitar envío si: recibido y no hay faltantes requeridos
            $faltantes = $checks->where('requerido', 1)->where('checked', 0)->count();
            $enviarHabilitado = (bool) $procesoEtapa->recibido && $faltantes === 0;
        }

        return view('areas.unidad', compact(
            'areaRole',
            'procesos',
            'proceso',
            'procesoEtapa',
            'checks',
            'enviarHabilitado'
        ));
    }
}
