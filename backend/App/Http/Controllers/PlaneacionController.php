<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\ProcesoEtapa;
use App\Models\ProcesoAuditoria;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlaneacionController extends Controller
{
    /**
     * Muestra la bandeja de procesos para el área de Planeación
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Verificar acceso: admin o planeacion
        if (!$user->hasRole('admin') && !$user->hasRole('planeacion')) {
            abort(403, 'No tienes acceso a esta área');
        }

        $estado = $request->get('estado', 'pendiente');

        // Bandeja: procesos cuya etapa actual es planeacion
        $procesos = DB::table('procesos as p')
            ->leftJoin('workflows as w', 'w.id', '=', 'p.workflow_id')
            ->leftJoin('etapas as e', 'e.id', '=', 'p.etapa_actual_id')
            ->select('p.*', 'w.nombre as workflow_nombre', 'e.nombre as etapa_nombre')
            ->where('p.area_actual_role', 'planeacion')
            ->orderByDesc('p.created_at')
            ->get();

        // Estadísticas simples basadas en la bandeja actual
        $stats = [
            'pendientes' => $procesos->count(),
            'en_revision' => 0,
            'completados_mes' => 0,
        ];

        // Alertas activas
        $alertas = Alerta::with('proceso.workflow')
            ->where('user_id', $user->id)
            ->where('leida', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('planeacion.index', compact('procesos', 'stats', 'estado', 'alertas'));
    }

    /**
     * Muestra el detalle de un proceso específico para Planeación
     */
    public function show($id)
    {
        $proceso = Proceso::with([
            'workflow.etapas.items',
            'etapaActual',
            'procesoEtapas.etapa.items',
            'procesoEtapas.checks.item',
            'archivos.tipoArchivo',
            'creador',
            'auditorias.user',
            'alertas',
            'paa'
        ])->findOrFail($id);

        // Verificar acceso: admin puede ver cualquier proceso, planeacion solo los suyos
        $user = auth()->user();
        if (!$user->hasRole('admin') && optional($proceso->etapaActual)->area_role !== 'planeacion') {
            abort(403, 'Este proceso no está actualmente en el área de Planeación');
        }

        // Proceso etapa actual
        $procesoEtapaActual = $proceso->procesoEtapas()
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        return view('planeacion.show', compact('proceso', 'procesoEtapaActual'));
    }

    /**
     * Aprueba y envía el proceso a la siguiente etapa
     */
    public function aprobar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);

        // Verificar que la etapa actual es de planeacion
        if ($proceso->etapaActual->area_role !== 'planeacion') {
            return back()->with('error', 'Este proceso no está en el área de Planeación');
        }

        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $procesoEtapa = $proceso->procesoEtapas()
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            if ($procesoEtapa) {
                DB::table('proceso_etapas')
                    ->where('id', $procesoEtapa->id)
                    ->update([
                        'enviado'    => true,
                        'enviado_por' => auth()->id(),
                        'enviado_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            // Guardar observaciones en el proceso si existen
            if (!empty($validated['observaciones'])) {
                $proceso->update(['observaciones_planeacion' => $validated['observaciones']]);
            }

            // Registrar auditoría
            ProcesoAuditoria::registrar(
                $proceso->id,
                'etapa_aprobada',
                'Planeación',
                $proceso->etapa_actual_id,
                null,
                "Etapa {$proceso->etapaActual->nombre} aprobada por Planeación. " . ($validated['observaciones'] ?? '')
            );

            // Avanzar a la siguiente etapa si existe
            $etapaActual   = $proceso->etapaActual; // referencia a la etapa actual ANTES de avanzar
            $nextEtapaId   = $etapaActual->next_etapa_id;

            if ($nextEtapaId) {
                $siguienteEtapa = \App\Models\Etapa::findOrFail($nextEtapaId);

                $proceso->update([
                    'etapa_actual_id'  => $nextEtapaId,
                    'area_actual_role' => $siguienteEtapa->area_role,
                    'aprobado_planeacion' => true,
                ]);

                // Crear registro de proceso_etapa para la nueva etapa (solo columnas que existen)
                ProcesoEtapa::firstOrCreate([
                    'proceso_id' => $proceso->id,
                    'etapa_id'   => $nextEtapaId,
                ]);

                // Alertar a los usuarios de la siguiente área
                $responsables = \App\Models\User::role($siguienteEtapa->area_role)->get();
                foreach ($responsables as $responsable) {
                    Alerta::create([
                        'proceso_id' => $proceso->id,
                        'tipo'       => 'proceso_recibido',
                        'mensaje'    => "Nuevo proceso recibido en {$siguienteEtapa->nombre}",
                        'prioridad'  => 'alta',
                        'user_id'    => $responsable->id,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('planeacion.show', $proceso->id)
                ->with('success', 'Proceso aprobado y enviado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al aprobar el proceso: ' . $e->getMessage());
        }
    }

    /**
     * Rechaza el proceso y lo devuelve a la etapa anterior
     */
    public function rechazar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);

        $validated = $request->validate([
            'motivo_rechazo' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $procesoEtapa = $proceso->procesoEtapas()
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            if ($procesoEtapa) {
                DB::table('proceso_etapas')
                    ->where('id', $procesoEtapa->id)
                    ->update([
                        'enviado'    => true,
                        'enviado_por' => auth()->id(),
                        'enviado_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            // Guardar motivo de rechazo
            $proceso->update(['observaciones_planeacion' => $validated['motivo_rechazo']]);

            // Registrar auditoría
            ProcesoAuditoria::registrar(
                $proceso->id,
                'etapa_rechazada',
                'Planeación',
                $proceso->etapa_actual_id,
                null,
                "Etapa {$proceso->etapaActual->nombre} rechazada por Planeación. Motivo: {$validated['motivo_rechazo']}"
            );

            // Encontrar etapa anterior
            $etapaAnterior = \App\Models\Etapa::where('workflow_id', $proceso->workflow_id)
                ->where('next_etapa_id', $proceso->etapa_actual_id)
                ->first();

            if ($etapaAnterior) {
                $proceso->update([
                    'etapa_actual_id' => $etapaAnterior->id
                ]);

                // Crear alerta para el área anterior
                $responsables = \App\Models\User::role($etapaAnterior->area_role)->get();
                
                foreach ($responsables as $responsable) {
                    Alerta::create([
                        'proceso_id' => $proceso->id,
                        'tipo' => 'proceso_rechazado',
                        'mensaje' => "Proceso rechazado desde Planeación. Motivo: {$validated['motivo_rechazo']}",
                        'prioridad' => 'alta',
                        'user_id' => $responsable->id,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('planeacion.index')
                ->with('success', 'Proceso rechazado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al rechazar el proceso: ' . $e->getMessage());
        }
    }

    /**
     * Reportes y estadísticas de Planeación
     */
    public function reportes(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        $stats = [
            'total_procesados' => Proceso::whereHas('procesoEtapas', function($q) use ($mes, $anio) {
                    $q->whereHas('etapa', function($eq) {
                            $eq->where('area_role', 'planeacion');
                        })
                        ->whereMonth('fecha_recepcion', $mes)
                        ->whereYear('fecha_recepcion', $anio);
                })->count(),
                
            'aprobados' => Proceso::whereHas('procesoEtapas', function($q) use ($mes, $anio) {
                    $q->where('estado', 'aprobado')
                        ->whereHas('etapa', function($eq) {
                            $eq->where('area_role', 'planeacion');
                        })
                        ->whereMonth('fecha_envio', $mes)
                        ->whereYear('fecha_envio', $anio);
                })->count(),
                
            'rechazados' => Proceso::whereHas('procesoEtapas', function($q) use ($mes, $anio) {
                    $q->where('estado', 'rechazado')
                        ->whereHas('etapa', function($eq) {
                            $eq->where('area_role', 'planeacion');
                        })
                        ->whereMonth('fecha_envio', $mes)
                        ->whereYear('fecha_envio', $anio);
                })->count(),
        ];

        return view('planeacion.reportes', compact('stats', 'mes', 'anio'));
    }
}
