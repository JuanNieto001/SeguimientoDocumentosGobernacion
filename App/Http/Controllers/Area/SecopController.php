<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\ProcesoEtapaArchivo;

class SecopController extends Controller
{
    public function index()
    {
        $areaRole = 'secop';

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
                $enviarHabilitado = (bool)$procesoEtapa->recibido && $faltantes === 0;
            }
        }

        return view('areas.secop', compact(
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

        abort_unless($proceso->area_actual_role === 'secop' || auth()->user()->hasRole('admin'), 403);

        return view('areas.secop-detalle', compact('proceso'));
    }

    /**
     * Publicar en SECOP
     */
    public function publicar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'secop', 403);

        $request->validate([
            'url_secop' => 'required|url|max:500',
            'numero_proceso_secop' => 'required|string|max:100',
            'archivo_publicacion' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        return DB::transaction(function () use ($proceso, $request) {
            // Guardar archivo si existe
            if ($request->hasFile('archivo_publicacion')) {
                $archivo = $request->file('archivo_publicacion');
                $nombreGuardado = 'PUBLICACION_SECOP_' . time() . '.pdf';
                $ruta = "procesos/{$proceso->id}/secop/{$nombreGuardado}";
                
                $archivo->storeAs('public/' . dirname($ruta), basename($ruta));

                ProcesoEtapaArchivo::create([
                    'proceso_id' => $proceso->id,
                    'proceso_etapa_id' => $proceso->procesoEtapas()->where('etapa_id', $proceso->etapa_actual_id)->first()->id ?? null,
                    'etapa_id' => $proceso->etapa_actual_id,
                    'tipo_archivo' => 'publicacion_secop',
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'nombre_guardado' => $nombreGuardado,
                    'ruta' => $ruta,
                    'mime_type' => 'application/pdf',
                    'tamanio' => $archivo->getSize(),
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                    'estado' => 'aprobado',
                ]);
            }

            $proceso->update([
                'url_secop' => $request->url_secop,
                'numero_proceso_secop' => $request->numero_proceso_secop,
                'publicado_secop' => true,
                'fecha_publicacion_secop' => now(),
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'publicacion_secop',
                'secop',
                $proceso->etapaActual->nombre ?? 'Publicación SECOP',
                null,
                "Proceso publicado en SECOP: {$request->numero_proceso_secop}"
            );

            return redirect()->back()->with('success', 'Proceso publicado en SECOP exitosamente');
        });
    }

    /**
     * Registrar contrato
     */
    public function registrarContrato(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'secop', 403);

        $request->validate([
            'numero_contrato' => 'required|string|max:100',
            'fecha_contrato' => 'required|date',
            'archivo_contrato' => 'required|file|mimes:pdf|max:10240',
        ]);

        return DB::transaction(function () use ($proceso, $request) {
            // Guardar archivo
            $archivo = $request->file('archivo_contrato');
            $nombreGuardado = 'CONTRATO_' . $request->numero_contrato . '_' . time() . '.pdf';
            $ruta = "procesos/{$proceso->id}/secop/{$nombreGuardado}";
            
            $archivo->storeAs('public/' . dirname($ruta), basename($ruta));

            ProcesoEtapaArchivo::create([
                'proceso_id' => $proceso->id,
                'proceso_etapa_id' => $proceso->procesoEtapas()->where('etapa_id', $proceso->etapa_actual_id)->first()->id ?? null,
                'etapa_id' => $proceso->etapa_actual_id,
                'tipo_archivo' => 'contrato',
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
                'numero_contrato' => $request->numero_contrato,
                'fecha_contrato' => $request->fecha_contrato,
                'contrato_registrado_secop' => true,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'contrato_registrado',
                'secop',
                $proceso->etapaActual->nombre ?? 'Registro Contrato',
                null,
                "Contrato registrado: {$request->numero_contrato}"
            );

            return redirect()->back()->with('success', 'Contrato registrado exitosamente');
        });
    }

    /**
     * Registrar acta de inicio
     */
    public function registrarActaInicio(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'secop', 403);

        $request->validate([
            'numero_acta_inicio' => 'required|string|max:100',
            'fecha_acta_inicio' => 'required|date',
            'archivo_acta_inicio' => 'required|file|mimes:pdf|max:5120',
        ]);

        return DB::transaction(function () use ($proceso, $request) {
            // Guardar archivo
            $archivo = $request->file('archivo_acta_inicio');
            $nombreGuardado = 'ACTA_INICIO_' . $request->numero_acta_inicio . '_' . time() . '.pdf';
            $ruta = "procesos/{$proceso->id}/secop/{$nombreGuardado}";
            
            $archivo->storeAs('public/' . dirname($ruta), basename($ruta));

            ProcesoEtapaArchivo::create([
                'proceso_id' => $proceso->id,
                'proceso_etapa_id' => $proceso->procesoEtapas()->where('etapa_id', $proceso->etapa_actual_id)->first()->id ?? null,
                'etapa_id' => $proceso->etapa_actual_id,
                'tipo_archivo' => 'acta_inicio',
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
                'numero_acta_inicio' => $request->numero_acta_inicio,
                'fecha_acta_inicio' => $request->fecha_acta_inicio,
                'acta_inicio_registrada' => true,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'acta_inicio_registrada',
                'secop',
                $proceso->etapaActual->nombre ?? 'Acta de Inicio',
                null,
                "Acta de inicio registrada: {$request->numero_acta_inicio}"
            );

            return redirect()->back()->with('success', 'Acta de inicio registrada exitosamente');
        });
    }

    /**
     * Cerrar proceso en SECOP
     */
    public function cerrar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'secop', 403);

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            $proceso->update([
                'cerrado_secop' => true,
                'fecha_cierre_secop' => now(),
                'observaciones_cierre_secop' => $request->observaciones,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'proceso_cerrado_secop',
                'secop',
                $proceso->etapaActual->nombre ?? 'Cierre en SECOP',
                null,
                "Proceso cerrado en SECOP. Observaciones: " . ($request->observaciones ?? 'Ninguna')
            );
        });

        return redirect()->back()->with('success', 'Proceso cerrado en SECOP');
    }

    /**
     * Aprobar registro en SECOP
     */
    public function aprobar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        abort_unless($proceso->area_actual_role === 'secop', 403);

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($proceso, $request) {
            $proceso->update([
                'aprobado_secop' => true,
                'observaciones_secop' => $request->observaciones,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'aprobado_secop',
                'secop',
                $proceso->etapaActual->nombre ?? 'Aprobación SECOP',
                null,
                "Proceso aprobado en SECOP. Observaciones: " . ($request->observaciones ?? 'Ninguna')
            );
        });

        return redirect()->back()->with('success', 'Proceso aprobado en SECOP');
    }

    /**
     * Reportes de SECOP
     */
    public function reportes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth());

        $estadisticas = [
            'total_procesos' => Proceso::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
            'en_secop' => Proceso::where('area_actual_role', 'secop')->count(),
            'publicados' => Proceso::where('publicado_secop', true)->count(),
            'contratos_registrados' => Proceso::where('contrato_registrado_secop', true)->count(),
            'actas_inicio' => Proceso::where('acta_inicio_registrada', true)->count(),
            'cerrados' => Proceso::where('cerrado_secop', true)->count(),
            'aprobados' => Proceso::where('aprobado_secop', true)->count(),
        ];

        return view('areas.secop-reportes', compact('estadisticas', 'fechaInicio', 'fechaFin'));
    }
}
