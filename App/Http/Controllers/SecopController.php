<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\ProcesoEtapa;
use App\Models\ProcesoAuditoria;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecopController extends Controller
{
    /**
     * Muestra la bandeja de procesos para el área SECOP
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin') && !$user->hasRole('secop')) {
            abort(403, 'No tienes acceso a esta área');
        }

        $estado = $request->get('estado', 'EN_CURSO');
        
        $procesos = Proceso::with(['workflow', 'etapaActual', 'procesoEtapas', 'creador', 'paa'])
            ->whereHas('etapaActual', function($query) {
                $query->where('area_role', 'secop');
            })
            ->when($estado !== 'todos', function($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total'    => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'secop'))->count(),
            'pendiente' => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'secop'))
                ->where('estado', 'EN_CURSO')->count(),
            'en_curso' => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'secop'))
                ->where('estado', 'EN_REVISION')->count(),
            'publicado_secop' => Proceso::whereHas('etapaActual', fn($q) => $q->where('area_role', 'secop'))
                ->whereNotNull('numero_proceso_secop')->count(),
        ];

        return view('secop.index', compact('procesos', 'stats', 'estado'));
    }

    /**
     * Muestra el detalle de un proceso
     */
    public function show($id)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin') && !$user->hasRole('secop')) {
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

        if ($proceso->etapaActual->area_role !== 'secop') {
            abort(403, 'Este proceso no está asignado a SECOP');
        }

        $procesoEtapaActual = $proceso->procesoEtapas()
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        return view('secop.show', compact('proceso', 'procesoEtapaActual'));
    }

    /**
     * Publicar proceso en SECOP II
     */
    public function publicar(Request $request, $id)
    {
        $validated = $request->validate([
            'numero_proceso_secop' => 'required|string|max:50',
            'url_secop' => 'required|url|max:500',
            'fecha_publicacion' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::findOrFail($id);

            $proceso->update([
                'numero_proceso_secop' => $validated['numero_proceso_secop'],
                'url_secop' => $validated['url_secop'],
                'fecha_publicacion_secop' => $validated['fecha_publicacion'],
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'publicado_secop',
                'SECOP',
                $proceso->etapaActual->nombre,
                null,
                "Proceso publicado en SECOP II: {$validated['numero_proceso_secop']}" .
                ($validated['observaciones'] ? " - {$validated['observaciones']}" : "")
            );

            DB::commit();

            return back()->with('success', 'Proceso publicado en SECOP II correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al publicar en SECOP: ' . $e->getMessage());
        }
    }

    /**
     * Registrar contrato electrónico
     */
    public function registrarContrato(Request $request, $id)
    {
        $validated = $request->validate([
            'numero_contrato' => 'required|string|max:50',
            'fecha_contrato' => 'required|date',
            'valor_contrato' => 'required|numeric|min:0',
            'plazo' => 'required|integer|min:1',
            'unidad_plazo' => 'required|in:dias,meses,años',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::findOrFail($id);

            $proceso->update([
                'numero_contrato' => $validated['numero_contrato'],
                'fecha_contrato' => $validated['fecha_contrato'],
                'valor_contrato' => $validated['valor_contrato'],
                'plazo_contrato' => $validated['plazo'],
                'unidad_plazo' => $validated['unidad_plazo'],
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'contrato_registrado',
                'SECOP',
                $proceso->etapaActual->nombre,
                null,
                "Contrato registrado: {$validated['numero_contrato']} - Valor: $" . 
                number_format($validated['valor_contrato'], 2) .
                " - Plazo: {$validated['plazo']} {$validated['unidad_plazo']}" .
                ($validated['observaciones'] ? " - {$validated['observaciones']}" : "")
            );

            DB::commit();

            return back()->with('success', 'Contrato electrónico registrado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar contrato: ' . $e->getMessage());
        }
    }

    /**
     * Registrar acta de inicio
     */
    public function registrarActaInicio(Request $request, $id)
    {
        $validated = $request->validate([
            'numero_acta' => 'required|string|max:50',
            'fecha_acta' => 'required|date',
            'fecha_inicio_ejecucion' => 'required|date',
            'fecha_fin_ejecucion' => 'required|date|after:fecha_inicio_ejecucion',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::findOrFail($id);

            $proceso->update([
                'acta_inicio' => $validated['numero_acta'],
                'fecha_acta_inicio' => $validated['fecha_acta'],
                'fecha_inicio_ejecucion' => $validated['fecha_inicio_ejecucion'],
                'fecha_fin_ejecucion' => $validated['fecha_fin_ejecucion'],
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'acta_inicio_registrada',
                'SECOP',
                $proceso->etapaActual->nombre,
                null,
                "Acta de inicio registrada: {$validated['numero_acta']} - " .
                "Inicio: {$validated['fecha_inicio_ejecucion']} - " .
                "Fin: {$validated['fecha_fin_ejecucion']}" .
                ($validated['observaciones'] ? " - {$validated['observaciones']}" : "")
            );

            DB::commit();

            return back()->with('success', 'Acta de inicio registrada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar acta: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar proceso en SECOP
     */
    public function cerrar(Request $request, $id)
    {
        $validated = $request->validate([
            'fecha_cierre' => 'required|date',
            'observaciones_cierre' => 'required|string|min:10|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::findOrFail($id);

            $proceso->update([
                'fecha_cierre' => $validated['fecha_cierre'],
                'observaciones_cierre' => $validated['observaciones_cierre'],
                'estado' => 'completado',
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'proceso_cerrado',
                'SECOP',
                $proceso->etapaActual->nombre,
                null,
                "Proceso cerrado en SECOP - {$validated['observaciones_cierre']}"
            );

            DB::commit();

            return redirect()
                ->route('secop.index')
                ->with('success', 'Proceso cerrado correctamente en SECOP');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cerrar proceso: ' . $e->getMessage());
        }
    }

    /**
     * Aprobar y avanzar proceso
     */
    public function aprobar(Request $request, $id)
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proceso = Proceso::with(['etapaActual'])->findOrFail($id);

            if ($proceso->etapaActual->area_role !== 'secop') {
                return back()->with('error', 'Este proceso no está asignado a SECOP');
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
                // Es la última etapa, cerrar proceso
                $proceso->estado = 'completado';
                $proceso->fecha_cierre = now();
                $proceso->save();

                ProcesoAuditoria::registrar(
                    $proceso->id,
                    'proceso_completado',
                    'SECOP',
                    $proceso->etapaActual->nombre,
                    null,
                    "Proceso completado exitosamente"
                );

                DB::commit();

                return redirect()
                    ->route('secop.index')
                    ->with('success', 'Proceso completado exitosamente');
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
                'SECOP',
                $proceso->etapaActual->nombre,
                $siguienteEtapa->nombre,
                $validated['observaciones'] ?? "Proceso aprobado en SECOP y enviado a {$siguienteEtapa->area_role}"
            );

            DB::commit();

            return redirect()
                ->route('secop.index')
                ->with('success', "Proceso aprobado y enviado a {$siguienteEtapa->area_role}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al aprobar: ' . $e->getMessage());
        }
    }

    /**
     * Reportes de SECOP
     */
    public function reportes(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth());
        $hasta = $request->get('hasta', now()->endOfMonth());

        $stats = [
            'publicaciones_secop' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'publicado_secop')
                ->where('area', 'SECOP')
                ->count(),
            'contratos_registrados' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'contrato_registrado')
                ->where('area', 'SECOP')
                ->count(),
            'actas_inicio' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'acta_inicio_registrada')
                ->where('area', 'SECOP')
                ->count(),
            'procesos_cerrados' => ProcesoAuditoria::whereBetween('created_at', [$desde, $hasta])
                ->where('accion', 'proceso_cerrado')
                ->where('area', 'SECOP')
                ->count(),
            'valor_total_contratos' => Proceso::whereNotNull('valor_contrato')
                ->whereBetween('fecha_contrato', [$desde, $hasta])
                ->sum('valor_contrato'),
        ];

        return view('secop.reportes', compact('stats', 'desde', 'hasta'));
    }
}
