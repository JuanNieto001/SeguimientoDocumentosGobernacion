<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\ProcesoEtapa;
use App\Models\ProcesoAuditoria;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JuridicaController extends Controller
{
    /**
     * Muestra la bandeja de procesos para el área Jurídica
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin') && !$user->hasRole('juridica')) {
            abort(403, 'No tienes acceso a esta área');
        }

        $estado = $request->get('estado', 'EN_CURSO');
        
        $procesos = Proceso::with(['workflow', 'etapaActual', 'procesoEtapas', 'creador', 'paa'])
            ->whereHas('etapaActual', function($query) {
                $query->where('area_role', 'juridica');
            })
            ->when($estado !== 'todos', function($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total'    => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'juridica'))->count(),
            'pendiente' => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'juridica'))
                ->where('estado', 'EN_CURSO')->count(),
            'en_curso' => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'juridica'))
                ->where('estado', 'EN_REVISION')->count(),
            'rechazado' => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'juridica'))
                ->where('estado', 'rechazado')->count(),
        ];

        return view('juridica.index', compact('procesos', 'stats', 'estado'));
    }

    /**
     * Muestra el detalle de un proceso
     */
    public function show($id)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin') && !$user->hasRole('juridica')) {
            abort(403, 'No tienes acceso a esta área');
        }

        $proceso = Proceso::with([
            'workflow',
            'etapaActual.items',
            'procesoEtapas.etapa',
            'procesoEtapas.checks.item',
            'procesoEtapas.recibidoPor',
            'procesoEtapas.enviadoPor',
            'archivos',
            'auditorias.usuario',
            'creador',
            'paa',
            'modificaciones'
        ])->findOrFail($id);

        if ($proceso->etapaActual->area_role !== 'juridica') {
            abort(403, 'Este proceso no está asignado a Jurídica');
        }

        $procesoEtapaActual = $proceso->procesoEtapas()
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        return view('juridica.show', compact('proceso', 'procesoEtapaActual'));
    }

    /**
     * Emitir Ajustado a Derecho
     */
    public function emitirAjustado(Request $request, $id)
    {
        $validated = $request->validate([
            'numero_documento' => 'required|string|max:50',
            'fecha_emision' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::findOrFail($id);

            $proceso->update([
                'ajustado_derecho' => $validated['numero_documento'],
                'fecha_ajustado' => $validated['fecha_emision'],
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'ajustado_derecho_emitido',
                'Jurídica',
                $proceso->etapaActual->nombre,
                null,
                "Ajustado a Derecho emitido: {$validated['numero_documento']}" .
                ($validated['observaciones'] ? " - {$validated['observaciones']}" : "")
            );

            DB::commit();

            return back()->with('success', 'Ajustado a Derecho emitido correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al emitir documento: ' . $e->getMessage());
        }
    }

    /**
     * Verificar contratista (antecedentes)
     */
    public function verificarContratista(Request $request, $id)
    {
        $validated = $request->validate([
            'antecedentes_resultado' => 'required|in:sin_antecedentes,con_antecedentes',
            'numero_documento'       => 'required|string|max:30',
            'observaciones'          => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::findOrFail($id);

            $tieneAntecedentes = $validated['antecedentes_resultado'] === 'con_antecedentes';

            $proceso->update([
                'verificacion_contratista' => $validated['numero_documento'],
                'resultado_verificacion'   => $validated['antecedentes_resultado'],
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'contratista_verificado',
                'Verificación de contratista: doc ' . $validated['numero_documento'] . ' — ' . $validated['antecedentes_resultado'],
                $proceso->etapa_actual_id,
                null,
                ['resultado' => $validated['antecedentes_resultado']]
            );

            if ($tieneAntecedentes) {
                Alerta::create([
                    'proceso_id' => $proceso->id,
                    'tipo'       => 'advertencia',
                    'mensaje'    => 'El contratista tiene antecedentes o inhabilidades',
                    'prioridad'  => 'alta',
                    'user_id'    => $proceso->created_by,
                ]);
            }

            DB::commit();

            return back()->with(
                $tieneAntecedentes ? 'error' : 'success',
                $tieneAntecedentes
                    ? 'Verificación registrada — Se encontraron antecedentes. Se creó una alerta.'
                    : 'Verificación registrada — Sin antecedentes.'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error en verificación: ' . $e->getMessage());
        }
    }

    /**
     * Aprobar revisión jurídica y avanzar
     */
    public function aprobar(Request $request, $id)
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::with(['etapaActual'])->findOrFail($id);

            if ($proceso->etapaActual->area_role !== 'juridica') {
                return back()->with('error', 'Este proceso no está asignado a Jurídica');
            }

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

            $nuevaProcesoEtapa = $proceso->procesoEtapas()->create([
                'etapa_id' => $siguienteEtapa->id,
                'estado' => 'en_proceso',
                'fecha_inicio' => now(),
            ]);

            foreach ($siguienteEtapa->items as $item) {
                $nuevaProcesoEtapa->checks()->create([
                    'etapa_item_id' => $item->id,
                    'completado' => false,
                ]);
            }

            ProcesoAuditoria::registrar(
                $proceso->id,
                'etapa_aprobada',
                'Jurídica',
                $proceso->etapaActual->nombre,
                $siguienteEtapa->nombre,
                $validated['observaciones'] ?? "Revisión jurídica aprobada. Proceso enviado a {$siguienteEtapa->area_role}"
            );

            DB::commit();

            return redirect()
                ->route('juridica.index')
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

            if ($proceso->etapaActual->area_role !== 'juridica') {
                return back()->with('error', 'Este proceso no está asignado a Jurídica');
            }

            $etapaActual = $proceso->etapaActual;

            $etapaAnterior = DB::table('etapas')
                ->where('workflow_id', $proceso->workflow_id)
                ->where('next_etapa_id', $etapaActual->id)
                ->first();

            if (!$etapaAnterior) {
                return back()->with('error', 'No se puede rechazar, es la primera etapa');
            }

            DB::table('procesos')->where('id', $proceso->id)->update([
                'etapa_actual_id'  => $etapaAnterior->id,
                'area_actual_role' => $etapaAnterior->area_role,
                'estado'           => 'EN_CURSO',
                'updated_at'       => now(),
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'etapa_rechazada',
                "RECHAZADO POR JURÍDICA: {$validated['motivo']}",
                $etapaActual->id,
                null,
                ['motivo' => $validated['motivo'], 'devuelto_a' => $etapaAnterior->nombre]
            );

            Alerta::create([
                'proceso_id' => $proceso->id,
                'tipo'       => 'rechazo',
                'mensaje'    => "Proceso rechazado por Jurídica: {$validated['motivo']}",
                'prioridad'  => 'alta',
                'user_id'    => $proceso->created_by,
            ]);

            DB::commit();

            return redirect()
                ->route('juridica.index')
                ->with('success', 'Proceso rechazado y devuelto a ' . $etapaAnterior->nombre);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al rechazar: ' . $e->getMessage());
        }
    }

    /**
     * Aprobar pólizas
     */
    public function aprobarPolizas(Request $request, $id)
    {
        $validated = $request->validate([
            'polizas_aprobadas' => 'required|boolean',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::findOrFail($id);

            $proceso->update([
                'polizas_aprobadas' => $validated['polizas_aprobadas'],
                'fecha_aprobacion_polizas' => now(),
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                $validated['polizas_aprobadas'] ? 'polizas_aprobadas' : 'polizas_rechazadas',
                'Jurídica',
                $proceso->etapaActual->nombre,
                null,
                ($validated['polizas_aprobadas'] ? 'Pólizas aprobadas' : 'Pólizas rechazadas') .
                ($validated['observaciones'] ? " - {$validated['observaciones']}" : "")
            );

            DB::commit();

            return back()->with('success', 
                $validated['polizas_aprobadas'] 
                    ? 'Pólizas aprobadas correctamente' 
                    : 'Pólizas rechazadas'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar pólizas: ' . $e->getMessage());
        }
    }

    /**
     * Reportes de Jurídica
     */
    public function reportes(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth());
        $hasta = $request->get('hasta', now()->endOfMonth());

        $stats = [
            'ajustados_emitidos' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'ajustado_derecho_emitido')
                ->where('area', 'Jurídica')
                ->count(),
            'contratistas_verificados' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'contratista_verificado')
                ->where('area', 'Jurídica')
                ->count(),
            'polizas_aprobadas' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'polizas_aprobadas')
                ->where('area', 'Jurídica')
                ->count(),
            'procesos_aprobados' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'etapa_aprobada')
                ->where('area', 'Jurídica')
                ->count(),
            'procesos_rechazados' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'etapa_rechazada')
                ->where('area', 'Jurídica')
                ->count(),
        ];

        return view('juridica.reportes', compact('stats', 'desde', 'hasta'));
    }
}
