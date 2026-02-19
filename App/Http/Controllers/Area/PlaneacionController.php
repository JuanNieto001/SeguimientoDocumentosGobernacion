<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\PlanAnualAdquisicion;

class PlaneacionController extends Controller
{
    public function index()
    {
        $areaRole = 'planeacion';

        $procesos = DB::table('procesos')
            ->where('area_actual_role', $areaRole)
            ->where('estado', 'EN_CURSO')
            ->orderByDesc('id')
            ->get();

        $selectedId = request('proceso_id') ?? ($procesos->first()->id ?? null);

        $proceso = $selectedId
            ? DB::table('procesos')->where('id', $selectedId)->first()
            : null;

        $procesoEtapa = null;
        $checks = collect();
        $enviarHabilitado = false;
        $solicitudesPendientes = collect(); // ✅ NUEVO

        if ($proceso) {
            $procesoEtapa = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            // ✅ NUEVO: Cargar solicitudes de documentos si está en Etapa 1
            $etapaActual = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
            if ($etapaActual && $etapaActual->orden == 1) {
                $solicitudesPendientes = DB::table('proceso_documentos_solicitados')
                    ->where('proceso_id', $proceso->id)
                    ->where('etapa_id', $proceso->etapa_actual_id)
                    ->orderBy('id')
                    ->get();
            }

            if ($procesoEtapa) {
                $checks = DB::table('proceso_etapa_checks as pc')
                    ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
                    ->select('pc.id as check_id', 'pc.checked', 'ei.label', 'ei.requerido')
                    ->where('pc.proceso_etapa_id', $procesoEtapa->id)
                    ->orderBy('ei.orden')
                    ->get();

                // ✅ MODIFICADO: Habilitar envío si todas las solicitudes están subidas
                if ($etapaActual && $etapaActual->orden == 1) {
                    $totalSolicitudes = $solicitudesPendientes->count();
                    $solicitudesSubidas = $solicitudesPendientes->where('estado', 'subido')->count();
                    $enviarHabilitado = (bool)$procesoEtapa->recibido && $solicitudesSubidas === $totalSolicitudes;
                } else {
                    $faltantes = $checks->where('requerido', 1)->where('checked', 0)->count();
                    $enviarHabilitado = (bool)$procesoEtapa->recibido && $faltantes === 0;
                }
            }
        }

        return view('areas.planeacion', compact(
            'areaRole', 'procesos', 'proceso', 'procesoEtapa', 'checks', 'enviarHabilitado', 'solicitudesPendientes' // ✅ NUEVO
        ));
    }

    /**
     * Ver detalle de un proceso
     */
    public function show($id)
    {
        $proceso = Proceso::with([
            'workflow',
            'etapaActual',
            'procesoEtapas.etapa',
            'procesoEtapas.checks.etapaItem',
            'archivos',
            'auditorias.usuario'
        ])->findOrFail($id);

        // Verificar que sea del área de planeación
        abort_unless($proceso->area_actual_role === 'planeacion' || auth()->user()->hasRole('admin'), 403);

        return view('areas.planeacion-detalle', compact('proceso'));
    }

    /**
     * Verificar inclusión en PAA
     */
    public function verificarPAA($id)
    {
        $proceso = Proceso::findOrFail($id);
        
        // Buscar en PAA
        $paa = PlanAnualAdquisicion::where('vigencia', now()->year)
            ->where('workflow_id', $proceso->workflow_id)
            ->where('descripcion_necesidad', 'like', '%' . $proceso->objeto . '%')
            ->orWhere('codigo_bpin', $proceso->codigo_bpin ?? '')
            ->first();

        if ($paa) {
            // Actualizar proceso con datos del PAA
            $proceso->update([
                'paa_verificado' => true,
                'paa_id' => $paa->id,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'paa_verificado',
                'planeacion',
                $proceso->etapaActual->nombre ?? 'Verificación PAA',
                null,
                "Proceso verificado en PAA. Registro PAA #{$paa->id}"
            );

            return redirect()->back()->with('success', 'Proceso verificado exitosamente en el PAA');
        }

        return redirect()->back()->with('error', 'El proceso NO está incluido en el PAA vigente');
    }

    /**
     * Aprobar proceso en Planeación
     */
    public function aprobar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        
        abort_unless($proceso->area_actual_role === 'planeacion', 403);

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            // Actualizar estado
            $proceso->update([
                'aprobado_planeacion' => true,
                'observaciones_planeacion' => $request->observaciones,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'aprobado_planeacion',
                'planeacion',
                $proceso->etapaActual->nombre ?? 'Aprobación Planeación',
                null,
                "Proceso aprobado por Planeación. Observaciones: " . ($request->observaciones ?? 'Ninguna')
            );
        });

        return redirect()->back()->with('success', 'Proceso aprobado por Planeación');
    }

    /**
     * Rechazar proceso en Planeación
     */
    public function rechazar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        
        abort_unless($proceso->area_actual_role === 'planeacion', 403);

        $request->validate([
            'observaciones' => 'required|string|min:10|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            // Devolver a etapa anterior o marcar como rechazado
            $proceso->update([
                'estado' => 'rechazado',
                'rechazado_por_area' => 'planeacion',
                'observaciones_rechazo' => $request->observaciones,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'rechazado_planeacion',
                'planeacion',
                $proceso->etapaActual->nombre ?? 'Rechazo Planeación',
                null,
                "Proceso rechazado por Planeación. Motivo: {$request->observaciones}"
            );
        });

        return redirect()->back()->with('warning', 'Proceso rechazado. Se ha notificado al área solicitante');
    }

    /**
     * Reportes de Planeación
     */
    public function reportes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth());

        $estadisticas = [
            'total_procesos' => Proceso::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
            'en_planeacion' => Proceso::where('area_actual_role', 'planeacion')->count(),
            'aprobados' => Proceso::where('aprobado_planeacion', true)->count(),
            'rechazados' => Proceso::where('rechazado_por_area', 'planeacion')->count(),
            'por_modalidad' => DB::table('procesos')
                ->join('workflows', 'procesos.workflow_id', '=', 'workflows.id')
                ->select('workflows.nombre', DB::raw('count(*) as total'))
                ->whereBetween('procesos.created_at', [$fechaInicio, $fechaFin])
                ->groupBy('workflows.nombre')
                ->get(),
        ];

        return view('areas.planeacion-reportes', compact('estadisticas', 'fechaInicio', 'fechaFin'));
    }
}
