<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\ProcesoEtapaArchivo;

class HaciendaController extends Controller
{
    public function index()
    {
        $areaRole = 'hacienda';

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

        if ($proceso) {
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

        return view('areas.hacienda', compact(
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

        abort_unless($proceso->area_actual_role === 'hacienda' || auth()->user()->hasRole('admin'), 403);

        return view('areas.hacienda-detalle', compact('proceso'));
    }

    /**
     * Emitir CDP (Certificado de Disponibilidad Presupuestal)
     */
    public function emitirCDP(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'hacienda', 403);

        $request->validate([
            'numero_cdp' => 'required|string|max:100',
            'valor_cdp' => 'required|numeric|min:0',
            'rubro_presupuestal' => 'required|string|max:200',
            'archivo_cdp' => 'required|file|mimes:pdf|max:5120', // 5MB
        ]);

        return DB::transaction(function () use ($proceso, $request) {
            // Guardar archivo CDP
            $archivo = $request->file('archivo_cdp');
            $nombreGuardado = 'CDP_' . $request->numero_cdp . '_' . time() . '.pdf';
            $ruta = "procesos/{$proceso->id}/hacienda/{$nombreGuardado}";
            
            $archivo->storeAs('public/' . dirname($ruta), basename($ruta));

            // Registrar archivo
            ProcesoEtapaArchivo::create([
                'proceso_id' => $proceso->id,
                'proceso_etapa_id' => $proceso->procesoEtapas()->where('etapa_id', $proceso->etapa_actual_id)->first()->id ?? null,
                'etapa_id' => $proceso->etapa_actual_id,
                'tipo_archivo' => 'cdp',
                'nombre_original' => $archivo->getClientOriginalName(),
                'nombre_guardado' => $nombreGuardado,
                'ruta' => $ruta,
                'mime_type' => 'application/pdf',
                'tamanio' => $archivo->getSize(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'estado' => 'aprobado', // CDP ya viene aprobado por Hacienda
            ]);

            // Actualizar proceso
            $proceso->update([
                'numero_cdp' => $request->numero_cdp,
                'valor_cdp' => $request->valor_cdp,
                'rubro_presupuestal' => $request->rubro_presupuestal,
                'cdp_emitido' => true,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'cdp_emitido',
                'hacienda',
                $proceso->etapaActual->nombre ?? 'Emisión CDP',
                null,
                "CDP emitido: #{$request->numero_cdp} por valor de $" . number_format($request->valor_cdp, 2)
            );

            return redirect()->back()->with('success', 'CDP emitido exitosamente');
        });
    }

    /**
     * Emitir RP (Registro Presupuestal)
     */
    public function emitirRP(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'hacienda', 403);

        // Validar que exista CDP
        abort_unless($proceso->cdp_emitido, 422, 'Debe emitir primero el CDP');

        $request->validate([
            'numero_rp' => 'required|string|max:100',
            'valor_rp' => 'required|numeric|min:0',
            'archivo_rp' => 'required|file|mimes:pdf|max:5120',
        ]);

        return DB::transaction(function () use ($proceso, $request) {
            // Guardar archivo RP
            $archivo = $request->file('archivo_rp');
            $nombreGuardado = 'RP_' . $request->numero_rp . '_' . time() . '.pdf';
            $ruta = "procesos/{$proceso->id}/hacienda/{$nombreGuardado}";
            
            $archivo->storeAs('public/' . dirname($ruta), basename($ruta));

            ProcesoEtapaArchivo::create([
                'proceso_id' => $proceso->id,
                'proceso_etapa_id' => $proceso->procesoEtapas()->where('etapa_id', $proceso->etapa_actual_id)->first()->id ?? null,
                'etapa_id' => $proceso->etapa_actual_id,
                'tipo_archivo' => 'rp',
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
                'numero_rp' => $request->numero_rp,
                'valor_rp' => $request->valor_rp,
                'rp_emitido' => true,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'rp_emitido',
                'hacienda',
                $proceso->etapaActual->nombre ?? 'Emisión RP',
                null,
                "RP emitido: #{$request->numero_rp} por valor de $" . number_format($request->valor_rp, 2)
            );

            return redirect()->back()->with('success', 'RP emitido exitosamente');
        });
    }

    /**
     * Aprobar viabilidad económica
     */
    public function aprobar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'hacienda', 403);

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            $proceso->update([
                'aprobado_hacienda' => true,
                'observaciones_hacienda' => $request->observaciones,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'aprobado_hacienda',
                'hacienda',
                $proceso->etapaActual->nombre ?? 'Aprobación Hacienda',
                null,
                "Viabilidad económica aprobada. Observaciones: " . ($request->observaciones ?? 'Ninguna')
            );
        });

        return redirect()->back()->with('success', 'Viabilidad económica aprobada');
    }

    /**
     * Rechazar viabilidad económica
     */
    public function rechazar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'hacienda', 403);

        $request->validate([
            'observaciones' => 'required|string|min:10|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            $proceso->update([
                'estado' => 'rechazado',
                'rechazado_por_area' => 'hacienda',
                'observaciones_rechazo' => $request->observaciones,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'rechazado_hacienda',
                'hacienda',
                $proceso->etapaActual->nombre ?? 'Rechazo Hacienda',
                null,
                "Viabilidad económica rechazada. Motivo: {$request->observaciones}"
            );
        });

        return redirect()->back()->with('warning', 'Viabilidad económica rechazada');
    }

    /**
     * Reportes de Hacienda
     */
    public function reportes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth());

        $estadisticas = [
            'total_procesos' => Proceso::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
            'en_hacienda' => Proceso::where('area_actual_role', 'hacienda')->count(),
            'cdp_emitidos' => Proceso::where('cdp_emitido', true)->count(),
            'rp_emitidos' => Proceso::where('rp_emitido', true)->count(),
            'aprobados' => Proceso::where('aprobado_hacienda', true)->count(),
            'rechazados' => Proceso::where('rechazado_por_area', 'hacienda')->count(),
            'valor_total_cdp' => Proceso::where('cdp_emitido', true)->sum('valor_cdp'),
            'valor_total_rp' => Proceso::where('rp_emitido', true)->sum('valor_rp'),
        ];

        return view('areas.hacienda-reportes', compact('estadisticas', 'fechaInicio', 'fechaFin'));
    }
}
