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
        // Obtener el primer proceso pendiente en Planeación
        $primerProceso = DB::table('procesos')
            ->where('area_actual_role', 'planeacion')
            ->where('estado', 'EN_CURSO')
            ->orderByDesc('id')
            ->first();

        // Si hay procesos pendientes, redirigir al detalle del primero
        if ($primerProceso) {
            return redirect()->route('planeacion.show', $primerProceso->id);
        }

        // Si no hay procesos pendientes, mostrar mensaje
        return view('areas.planeacion', [
            'areaRole' => 'planeacion',
            'procesos' => collect(),
            'proceso' => null,
            'procesoEtapa' => null,
            'checks' => collect(),
            'enviarHabilitado' => false,
            'solicitudesPendientes' => collect(),
        ]);
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

        // Verificar que sea del área de planeación o admin
        if ($proceso->area_actual_role !== 'planeacion' && !auth()->user()->hasRole('admin')) {
            return redirect()->route('planeacion.index')
                ->with('success', 'El proceso fue enviado exitosamente a la siguiente área.');
        }

        // Cargar la etapa actual del proceso con sus checks
        $procesoEtapaActual = DB::table('proceso_etapas')
            ->where('proceso_id', $proceso->id)
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        // Cargar checks si existe la etapa
        $checks = collect();
        if ($procesoEtapaActual) {
            $checks = DB::table('proceso_etapa_checks as pc')
                ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
                ->select('pc.id', 'pc.checked', 'pc.checked_by', 'pc.checked_at', 'ei.label', 'ei.requerido')
                ->where('pc.proceso_etapa_id', $procesoEtapaActual->id)
                ->orderBy('ei.orden')
                ->get();
        }

        return view('planeacion.show', compact('proceso', 'procesoEtapaActual', 'checks'));
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
        
        // Validar que el proceso esté en el área de planeación
        if ($proceso->area_actual_role !== 'planeacion') {
            return redirect()->route('planeacion.index')
                ->with('error', 'Este proceso ya no está en tu bandeja.');
        }

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        $result = DB::transaction(function () use ($proceso, $request) {
            // Marcar etapa actual como enviada
            $procesoEtapaActual = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();
            
            if ($procesoEtapaActual) {
                DB::table('proceso_etapas')
                    ->where('id', $procesoEtapaActual->id)
                    ->update([
                        'enviado' => true,
                        'enviado_por' => auth()->id(),
                        'enviado_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            
            // Buscar siguiente etapa
            $siguienteEtapa = DB::table('etapas')
                ->where('workflow_id', $proceso->workflow_id)
                ->where('orden', '>', $proceso->etapaActual->orden)
                ->where('activa', 1)
                ->orderBy('orden')
                ->first();
            
            if ($siguienteEtapa) {
                // Actualizar proceso a siguiente etapa
                $proceso->update([
                    'etapa_actual_id' => $siguienteEtapa->id,
                    'area_actual_role' => $siguienteEtapa->area_role,
                    'aprobado_planeacion' => true,
                    'observaciones_planeacion' => $request->observaciones,
                ]);
                
                // Crear instancia de siguiente etapa
                $procesoEtapaSiguiente = DB::table('proceso_etapas')->insertGetId([
                    'proceso_id' => $proceso->id,
                    'etapa_id' => $siguienteEtapa->id,
                    'recibido' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Crear checks de siguiente etapa
                $items = DB::table('etapa_items')
                    ->where('etapa_id', $siguienteEtapa->id)
                    ->orderBy('orden')
                    ->get(['id']);
                
                foreach ($items as $item) {
                    DB::table('proceso_etapa_checks')->insert([
                        'proceso_etapa_id' => $procesoEtapaSiguiente,
                        'etapa_item_id' => $item->id,
                        'checked' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                // No hay más etapas - proceso completado
                $proceso->update([
                    'estado' => 'completado',
                    'aprobado_planeacion' => true,
                    'observaciones_planeacion' => $request->observaciones,
                ]);
            }

            ProcesoAuditoria::registrar(
                $proceso->id,
                'aprobado_planeacion',
                'planeacion',
                $proceso->etapaActual->nombre ?? 'Aprobación Planeación',
                null,
                "Proceso aprobado por Planeación. Observaciones: " . ($request->observaciones ?? 'Ninguna')
            );
            
            return true;
        });

        if ($result) {
            return redirect()->route('planeacion.index')->with('success', 'Proceso aprobado y enviado a la siguiente etapa');
        }
        
        return redirect()->route('planeacion.index')->with('error', 'Error al aprobar el proceso');
    }

    /**
     * Rechazar proceso en Planeación
     */
    public function rechazar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        
        // Validar que el proceso esté en el área de planeación
        if ($proceso->area_actual_role !== 'planeacion') {
            return redirect()->route('planeacion.index')
                ->with('error', 'Este proceso ya no está en tu bandeja.');
        }

        $request->validate([
            'motivo_rechazo' => 'required|string|min:10|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            // Devolver a etapa anterior o marcar como rechazado
            $proceso->update([
                'estado' => 'rechazado',
                'rechazado_por_area' => 'planeacion',
                'observaciones_rechazo' => $request->motivo_rechazo,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'rechazado_planeacion',
                'planeacion',
                $proceso->etapaActual->nombre ?? 'Rechazo Planeación',
                null,
                "Proceso rechazado por Planeación. Motivo: {$request->motivo_rechazo}"
            );
        });

        return redirect()->route('planeacion.index')->with('warning', 'Proceso rechazado. Se ha notificado al área solicitante');
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
