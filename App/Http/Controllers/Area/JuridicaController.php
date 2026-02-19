<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\ProcesoEtapaArchivo;

class JuridicaController extends Controller
{
    public function index()
    {
        $areaRole = 'juridica';

        $procesos = \DB::table('procesos')
            ->where('area_actual_role', $areaRole)
            ->where('estado', 'EN_CURSO')
            ->orderByDesc('id')
            ->get();

        $selectedId = request('proceso_id') ?? ($procesos->first()->id ?? null);

        $proceso = $selectedId
            ? \DB::table('procesos')->where('id', $selectedId)->first()
            : null;

        $procesoEtapa = null;
        $checks = collect();
        $enviarHabilitado = false;

        if ($proceso) {
            $procesoEtapa = \DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            if ($procesoEtapa) {
                $checks = \DB::table('proceso_etapa_checks as pc')
                    ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
                    ->select('pc.id as check_id', 'pc.checked', 'ei.label', 'ei.requerido')
                    ->where('pc.proceso_etapa_id', $procesoEtapa->id)
                    ->orderBy('ei.orden')
                    ->get();

                $faltantes = $checks->where('requerido', 1)->where('checked', 0)->count();
                $enviarHabilitado = $procesoEtapa->recibido && $faltantes === 0;
            }
        }

        return view('areas.juridica', compact(
            'areaRole', 'procesos', 'proceso', 'procesoEtapa', 'checks', 'enviarHabilitado'
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
            'archivos',
            'auditorias.usuario'
        ])->findOrFail($id);

        abort_unless($proceso->area_actual_role === 'juridica' || auth()->user()->hasRole('admin'), 403);

        return view('areas.juridica-detalle', compact('proceso'));
    }

    /**
     * Emitir Ajustado a Derecho
     */
    public function emitirAjustado(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'juridica', 403);

        $request->validate([
            'numero_ajustado' => 'required|string|max:100',
            'archivo_ajustado' => 'required|file|mimes:pdf|max:5120',
        ]);

        return DB::transaction(function () use ($proceso, $request) {
            // Guardar archivo
            $archivo = $request->file('archivo_ajustado');
            $nombreGuardado = 'AJUSTADO_' . $request->numero_ajustado . '_' . time() . '.pdf';
            $ruta = "procesos/{$proceso->id}/juridica/{$nombreGuardado}";
            
            $archivo->storeAs('public/' . dirname($ruta), basename($ruta));

            ProcesoEtapaArchivo::create([
                'proceso_id' => $proceso->id,
                'proceso_etapa_id' => $proceso->procesoEtapas()->where('etapa_id', $proceso->etapa_actual_id)->first()->id ?? null,
                'etapa_id' => $proceso->etapa_actual_id,
                'tipo_archivo' => 'ajustado_derecho',
                'nombre_original' => $archivo->getClientOriginalName(),
                'nombre_guardado' => $nombreGuardado,
                'ruta' => $ruta,
                'mime_type' => 'application/pdf',
                'tamanio' => $archivo->getSize(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'estado' => 'aprobado',
            ]);

            $proceso->update([
                'numero_ajustado' => $request->numero_ajustado,
                'ajustado_emitido' => true,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'ajustado_emitido',
                'juridica',
                $proceso->etapaActual->nombre ?? 'Emisión Ajustado',
                null,
                "Ajustado a Derecho emitido: #{$request->numero_ajustado}"
            );

            return redirect()->back()->with('success', 'Ajustado a Derecho emitido exitosamente');
        });
    }

    /**
     * Verificar antecedentes del contratista
     */
    public function verificarContratista(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'juridica', 403);

        $request->validate([
            'verificacion_completa' => 'required|boolean',
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            $proceso->update([
                'contratista_verificado' => $request->verificacion_completa,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'contratista_verificado',
                'juridica',
                $proceso->etapaActual->nombre ?? 'Verificación Contratista',
                null,
                "Verificación de contratista: " . ($request->verificacion_completa ? 'Aprobado' : 'Rechazado') . ". Observaciones: " . ($request->observaciones ?? 'Ninguna')
            );
        });

        return redirect()->back()->with('success', 'Verificación de contratista registrada');
    }

    /**
     * Aprobar pólizas
     */
    public function aprobarPolizas(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'juridica', 403);

        $request->validate([
            'polizas_aprobadas' => 'required|boolean',
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            $proceso->update([
                'polizas_aprobadas' => $request->polizas_aprobadas,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'polizas_aprobadas',
                'juridica',
                $proceso->etapaActual->nombre ?? 'Aprobación Pólizas',
                null,
                "Pólizas: " . ($request->polizas_aprobadas ? 'Aprobadas' : 'Rechazadas') . ". Observaciones: " . ($request->observaciones ?? 'Ninguna')
            );
        });

        return redirect()->back()->with('success', 'Estado de pólizas actualizado');
    }

    /**
     * Aprobar proceso en Jurídica
     */
    public function aprobar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'juridica', 403);

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            $proceso->update([
                'aprobado_juridica' => true,
                'observaciones_juridica' => $request->observaciones,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'aprobado_juridica',
                'juridica',
                $proceso->etapaActual->nombre ?? 'Aprobación Jurídica',
                null,
                "Proceso aprobado jurídicamente. Observaciones: " . ($request->observaciones ?? 'Ninguna')
            );
        });

        return redirect()->back()->with('success', 'Proceso aprobado jurídicamente');
    }

    /**
     * Rechazar proceso en Jurídica
     */
    public function rechazar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'juridica', 403);

        $request->validate([
            'observaciones' => 'required|string|min:10|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            $proceso->update([
                'estado' => 'rechazado',
                'rechazado_por_area' => 'juridica',
                'observaciones_rechazo' => $request->observaciones,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'rechazado_juridica',
                'juridica',
                $proceso->etapaActual->nombre ?? 'Rechazo Jurídica',
                null,
                "Proceso rechazado por Jurídica. Motivo: {$request->observaciones}"
            );
        });

        return redirect()->back()->with('warning', 'Proceso rechazado por Jurídica');
    }

    /**
     * Reportes de Jurídica
     */
    public function reportes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth());

        $estadisticas = [
            'total_procesos' => Proceso::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
            'en_juridica' => Proceso::where('area_actual_role', 'juridica')->count(),
            'ajustados_emitidos' => Proceso::where('ajustado_emitido', true)->count(),
            'contratistas_verificados' => Proceso::where('contratista_verificado', true)->count(),
            'polizas_aprobadas' => Proceso::where('polizas_aprobadas', true)->count(),
            'aprobados' => Proceso::where('aprobado_juridica', true)->count(),
            'rechazados' => Proceso::where('rechazado_por_area', 'juridica')->count(),
        ];

        return view('areas.juridica-reportes', compact('estadisticas', 'fechaInicio', 'fechaFin'));
    }
}
