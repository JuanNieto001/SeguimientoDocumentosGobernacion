<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\ProcesoEtapa;
use App\Models\ProcesoAuditoria;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HaciendaController extends Controller
{
    /**
     * Muestra la bandeja de procesos para el área de Hacienda
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin') && !$user->hasRole('hacienda')) {
            abort(403, 'No tienes acceso a esta área');
        }

        $estado = $request->get('estado', 'EN_CURSO');
        
        $procesos = Proceso::with(['workflow', 'etapaActual', 'procesoEtapas', 'creador', 'paa'])
            ->whereHas('etapaActual', function($query) {
                $query->where('area_role', 'hacienda');
            })
            ->when($estado !== 'todos', function($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total'    => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'hacienda'))->count(),
            'pendiente' => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'hacienda'))
                ->where('estado', 'EN_CURSO')->count(),
            'en_curso' => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'hacienda'))
                ->where('estado', 'EN_REVISION')->count(),
            'rechazado' => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'hacienda'))
                ->where('estado', 'rechazado')->count(),
        ];

        return view('hacienda.index', compact('procesos', 'stats', 'estado'));
    }

    /**
     * Muestra el detalle de un proceso
     */
    public function show($id)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin') && !$user->hasRole('hacienda')) {
            abort(403, 'No tienes acceso a esta área');
        }

        $proceso = Proceso::with([
            'workflow',
            'etapaActual.items',
            'procesoEtapas.etapa',
            'procesoEtapas.checks.item',
            'archivos',
            'auditorias.usuario',
            'creador',
            'paa'
        ])->findOrFail($id);

        if ($proceso->etapaActual->area_role !== 'hacienda') {
            abort(403, 'Este proceso no está asignado a Hacienda');
        }

        $procesoEtapaActual = $proceso->procesoEtapas()
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        return view('hacienda.show', compact('proceso', 'procesoEtapaActual'));
    }

    /**
     * Emitir CDP (Certificado de Disponibilidad Presupuestal)
     */
    public function emitirCDP(Request $request, $id)
    {
        $validated = $request->validate([
            'numero_cdp' => 'required|string|max:50',
            'valor_cdp' => 'required|numeric|min:0',
            'fecha_cdp' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::findOrFail($id);

            // Guardar datos del CDP en el proceso
            $proceso->update([
                'numero_cdp' => $validated['numero_cdp'],
                'valor_cdp' => $validated['valor_cdp'],
                'fecha_cdp' => $validated['fecha_cdp'],
            ]);

            // Registrar auditoría
            ProcesoAuditoria::registrar(
                $proceso->id,
                'cdp_emitido',
                'Hacienda',
                $proceso->etapaActual->nombre,
                null,
                "CDP emitido: {$validated['numero_cdp']} por valor de $" . number_format($validated['valor_cdp'], 2) .
                ($validated['observaciones'] ? " - {$validated['observaciones']}" : "")
            );

            DB::commit();

            return back()->with('success', 'CDP emitido correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al emitir CDP: ' . $e->getMessage());
        }
    }

    /**
     * Emitir RP (Registro Presupuestal)
     */
    public function emitirRP(Request $request, $id)
    {
        $validated = $request->validate([
            'numero_rp' => 'required|string|max:50',
            'valor_rp' => 'required|numeric|min:0',
            'fecha_rp' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::findOrFail($id);

            $proceso->update([
                'numero_rp' => $validated['numero_rp'],
                'valor_rp' => $validated['valor_rp'],
                'fecha_rp' => $validated['fecha_rp'],
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'rp_emitido',
                'Hacienda',
                $proceso->etapaActual->nombre,
                null,
                "RP emitido: {$validated['numero_rp']} por valor de $" . number_format($validated['valor_rp'], 2) .
                ($validated['observaciones'] ? " - {$validated['observaciones']}" : "")
            );

            DB::commit();

            return back()->with('success', 'RP emitido correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al emitir RP: ' . $e->getMessage());
        }
    }

    /**
     * Aprobar viabilidad económica y avanzar proceso
     */
    public function aprobar(Request $request, $id)
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::with(['etapaActual'])->findOrFail($id);

            if ($proceso->etapaActual->area_role !== 'hacienda') {
                return back()->with('error', 'Este proceso no está asignado a Hacienda');
            }

            // Verificar checks completados
            $procesoEtapa = $proceso->procesoEtapas()
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            $checksIncompletos = $procesoEtapa->checks()->where('completado', false)->count();

            if ($checksIncompletos > 0) {
                return back()->with('warning', "Faltan $checksIncompletos checks por completar");
            }

            $siguienteEtapa = $proceso->etapaActual->siguiente();

            if (!$siguienteEtapa) {
                return back()->with('error', 'No hay siguiente etapa configurada');
            }

            $proceso->etapa_actual_id = $siguienteEtapa->id;
            $proceso->save();

            // Crear registro para nueva etapa
            $nuevaProcesoEtapa = $proceso->procesoEtapas()->create([
                'etapa_id' => $siguienteEtapa->id,
                'estado' => 'en_proceso',
                'fecha_inicio' => now(),
            ]);

            // Crear checks
            foreach ($siguienteEtapa->items as $item) {
                $nuevaProcesoEtapa->checks()->create([
                    'etapa_item_id' => $item->id,
                    'completado' => false,
                ]);
            }

            ProcesoAuditoria::registrar(
                $proceso->id,
                'etapa_aprobada',
                'Hacienda',
                $proceso->etapaActual->nombre,
                $siguienteEtapa->nombre,
                $validated['observaciones'] ?? "Viabilidad económica aprobada. Proceso enviado a {$siguienteEtapa->area_role}"
            );

            DB::commit();

            return redirect()
                ->route('hacienda.index')
                ->with('success', "Proceso aprobado y enviado a {$siguienteEtapa->area_role}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al aprobar: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar el proceso
     */
    public function rechazar(Request $request, $id)
    {
        $validated = $request->validate([
            'motivo' => 'required|string|min:10|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::with(['etapaActual'])->findOrFail($id);

            if ($proceso->etapaActual->area_role !== 'hacienda') {
                return back()->with('error', 'Este proceso no está asignado a Hacienda');
            }

            $etapaActual = $proceso->etapaActual;

            // Buscar etapa anterior
            $etapaAnterior = DB::table('etapas')
                ->where('workflow_id', $proceso->workflow_id)
                ->where('next_etapa_id', $etapaActual->id)
                ->first();

            if (!$etapaAnterior) {
                return back()->with('error', 'No se puede rechazar, es la primera etapa');
            }

            $proceso->etapa_actual_id = $etapaAnterior->id;
            $proceso->estado = 'rechazado';
            $proceso->save();

            ProcesoAuditoria::registrar(
                $proceso->id,
                'etapa_rechazada',
                'Hacienda',
                $etapaActual->nombre,
                $etapaAnterior->nombre,
                "RECHAZADO POR HACIENDA: {$validated['motivo']}"
            );

            // Crear alerta para el responsable anterior
            Alerta::create([
                'proceso_id' => $proceso->id,
                'tipo' => 'rechazo',
                'mensaje' => "Proceso rechazado por Hacienda: {$validated['motivo']}",
                'prioridad' => 'alta',
                'user_id' => $proceso->created_by,
            ]);

            DB::commit();

            return redirect()
                ->route('hacienda.index')
                ->with('success', 'Proceso rechazado y devuelto a etapa anterior');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al rechazar: ' . $e->getMessage());
        }
    }

    /**
     * Reportes de Hacienda
     */
    public function reportes(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth());
        $hasta = $request->get('hasta', now()->endOfMonth());

        $stats = [
            'cdp_emitidos' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'cdp_emitido')
                ->where('area', 'Hacienda')
                ->count(),
            'rp_emitidos' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'rp_emitido')
                ->where('area', 'Hacienda')
                ->count(),
            'valor_total_cdp' => Proceso::whereNotNull('valor_cdp')
                ->whereBetween('fecha_cdp', [$desde, $hasta])
                ->sum('valor_cdp'),
            'valor_total_rp' => Proceso::whereNotNull('valor_rp')
                ->whereBetween('fecha_rp', [$desde, $hasta])
                ->sum('valor_rp'),
            'procesos_aprobados' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'etapa_aprobada')
                ->where('area', 'Hacienda')
                ->count(),
            'procesos_rechazados' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'etapa_rechazada')
                ->where('area', 'Hacienda')
                ->count(),
        ];

        return view('hacienda.reportes', compact('stats', 'desde', 'hasta'));
    }
}
