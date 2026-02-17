<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PlaneacionController extends Controller
{
    public function index()
    {
        $areaRole = 'planeacion';

        // Procesos en esta bandeja
        $procesos = DB::table('procesos')
            ->where('area_actual_role', $areaRole)
            ->orderByDesc('id')
            ->get();

        $selectedId = request('proceso_id') ?? ($procesos->first()->id ?? null);

        $proceso = $selectedId
            ? DB::table('procesos')->where('id', $selectedId)->first()
            : null;

        $procesoEtapa = null;
        $checks = collect();
        $enviarHabilitado = false;

        if ($proceso) {

            // Instancia de la etapa actual (puede no existir todavía)
            $procesoEtapa = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            if ($procesoEtapa) {
                $checks = DB::table('proceso_etapa_checks as pc')
                    ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
                    ->select('pc.id as check_id', 'pc.checked', 'ei.label', 'ei.requerido')
                    ->where('pc.proceso_etapa_id', $procesoEtapa->id)
                    ->orderBy('ei.orden')
                    ->get();

                $faltantes = $checks->where('requerido', 1)->where('checked', 0)->count();
                $enviarHabilitado = (bool)$procesoEtapa->recibido && $faltantes === 0;
            }
        }

        return view('areas.planeacion', compact(
            'areaRole',
            'procesos',
            'proceso',
            'procesoEtapa',
            'checks',
            'enviarHabilitado'
        ));
    }
}

