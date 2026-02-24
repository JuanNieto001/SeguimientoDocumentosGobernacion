<?php

namespace App\Http\Controllers;

use App\Models\InformeSupervision;
use App\Models\PagoContrato;
use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupervisionController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // BANDEJA
    // ──────────────────────────────────────────────────────────────

    /**
     * Lista de informes del proceso
     */
    public function index(int $procesoId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $this->checkAcceso($proceso);

        $informes = InformeSupervision::with(['supervisor', 'pagos'])
            ->where('proceso_id', $procesoId)
            ->orderBy('numero_informe')
            ->get();

        $pagos = PagoContrato::where('proceso_id', $procesoId)
            ->orderBy('numero_pago')
            ->get();

        $stats = [
            'total_informes'    => $informes->count(),
            'aprobados'         => $informes->where('estado_informe', 'aprobado')->count(),
            'pendientes'        => $informes->where('estado_informe', 'enviado')->count(),
            'total_pagos'       => $pagos->count(),
            'pagos_realizados'  => $pagos->where('estado', 'pagado')->count(),
            'valor_pagado'      => $pagos->where('estado', 'pagado')->sum('valor'),
            'prox_pago'         => $pagos->filter(fn($p) => $p->proximo)->first(),
            'porcentaje_avance' => $informes->isNotEmpty()
                ? $informes->sortByDesc('numero_informe')->first()->porcentaje_avance
                : 0,
        ];

        return view('supervision.index', compact('proceso', 'informes', 'pagos', 'stats'));
    }

    // ──────────────────────────────────────────────────────────────
    // INFORMES
    // ──────────────────────────────────────────────────────────────

    public function crearInforme(int $procesoId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $this->checkAcceso($proceso);

        $ultimoNumero = InformeSupervision::where('proceso_id', $procesoId)->max('numero_informe') ?? 0;

        return view('supervision.crear-informe', compact('proceso', 'ultimoNumero'));
    }

    public function guardarInforme(Request $request, int $procesoId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $this->checkAcceso($proceso);

        $validated = $request->validate([
            'periodo_inicio'          => 'required|string|max:100',
            'periodo_fin'             => 'required|string|max:100',
            'fecha_informe'           => 'required|date',
            'estado_avance'           => 'required|in:en_ejecucion,con_retraso,completado,suspendido',
            'porcentaje_avance'       => 'required|integer|min:0|max:100',
            'descripcion_actividades' => 'required|string|min:20',
            'observaciones'           => 'nullable|string|max:2000',
            'archivo_soporte'         => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'descripcion_actividades.min' => 'La descripción debe tener al menos 20 caracteres.',
        ]);

        $rutaArchivo = null;
        if ($request->hasFile('archivo_soporte')) {
            $archivo = $request->file('archivo_soporte');
            $nombre  = 'INFORME_' . $procesoId . '_' . time() . '.' . $archivo->extension();
            $rutaArchivo = $archivo->storeAs("public/procesos/{$procesoId}/supervision", $nombre);
            $rutaArchivo = str_replace('public/', '', $rutaArchivo);
        }

        $numero = InformeSupervision::where('proceso_id', $procesoId)->max('numero_informe') + 1;

        DB::transaction(function () use ($validated, $proceso, $numero, $rutaArchivo) {
            $informe = InformeSupervision::create([
                'proceso_id'              => $proceso->id,
                'supervisor_id'           => auth()->id(),
                'numero_informe'          => $numero,
                'periodo_inicio'          => $validated['periodo_inicio'],
                'periodo_fin'             => $validated['periodo_fin'],
                'fecha_informe'           => $validated['fecha_informe'],
                'estado_avance'           => $validated['estado_avance'],
                'porcentaje_avance'       => $validated['porcentaje_avance'],
                'descripcion_actividades' => $validated['descripcion_actividades'],
                'observaciones'           => $validated['observaciones'] ?? null,
                'archivo_soporte'         => $rutaArchivo,
                'estado_informe'          => 'enviado',
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'informe_supervision_registrado',
                "Informe de supervisión #{$numero} registrado para el período {$validated['periodo_inicio']} — {$validated['periodo_fin']}"
            );
        });

        return redirect()
            ->route('supervision.index', $procesoId)
            ->with('success', "Informe #{$numero} registrado correctamente.");
    }

    // ──────────────────────────────────────────────────────────────
    // PAGOS
    // ──────────────────────────────────────────────────────────────

    public function crearPago(int $procesoId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $this->checkAcceso($proceso);

        $ultimoNumero = PagoContrato::where('proceso_id', $procesoId)->max('numero_pago') ?? 0;
        $informes     = InformeSupervision::where('proceso_id', $procesoId)
            ->where('estado_informe', 'aprobado')
            ->orderBy('numero_informe')
            ->get();

        return view('supervision.crear-pago', compact('proceso', 'ultimoNumero', 'informes'));
    }

    public function guardarPago(Request $request, int $procesoId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $this->checkAcceso($proceso);

        $validated = $request->validate([
            'valor'               => 'required|numeric|min:1',
            'fecha_solicitud'     => 'required|date',
            'fecha_estimada_pago' => 'nullable|date|after_or_equal:fecha_solicitud',
            'informe_id'          => 'nullable|exists:informes_supervision,id',
            'observaciones'       => 'nullable|string|max:1000',
            'archivo_soporte'     => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $rutaArchivo = null;
        if ($request->hasFile('archivo_soporte')) {
            $archivo = $request->file('archivo_soporte');
            $nombre  = 'PAGO_' . $procesoId . '_' . time() . '.pdf';
            $rutaArchivo = $archivo->storeAs("public/procesos/{$procesoId}/pagos", $nombre);
            $rutaArchivo = str_replace('public/', '', $rutaArchivo);
        }

        $numero = PagoContrato::where('proceso_id', $procesoId)->max('numero_pago') + 1;

        DB::transaction(function () use ($validated, $proceso, $numero, $rutaArchivo) {
            PagoContrato::create([
                'proceso_id'          => $proceso->id,
                'informe_id'          => $validated['informe_id'] ?? null,
                'numero_pago'         => $numero,
                'valor'               => $validated['valor'],
                'fecha_solicitud'     => $validated['fecha_solicitud'],
                'fecha_estimada_pago' => $validated['fecha_estimada_pago'] ?? null,
                'estado'              => 'pendiente',
                'observaciones'       => $validated['observaciones'] ?? null,
                'archivo_soporte'     => $rutaArchivo,
                'registrado_por'      => auth()->id(),
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'pago_registrado',
                "Pago #{$numero} registrado por $ " . number_format($validated['valor'], 2, ',', '.')
            );
        });

        return redirect()
            ->route('supervision.index', $procesoId)
            ->with('success', "Pago #{$numero} registrado correctamente.");
    }

    /**
     * Actualizar estado de un pago
     */
    public function actualizarPago(Request $request, int $procesoId, PagoContrato $pago)
    {
        abort_unless($pago->proceso_id === $procesoId, 404);
        abort_unless(auth()->user()->hasAnyRole(['admin', 'planeacion', 'hacienda', 'unidad_solicitante']), 403);

        $validated = $request->validate([
            'estado'              => 'required|in:pendiente,en_tramite,aprobado,pagado,rechazado',
            'numero_referencia'   => 'nullable|string|max:100',
            'fecha_pago_efectivo' => 'nullable|date',
            'observaciones'       => 'nullable|string|max:500',
        ]);

        $estadoAnterior = $pago->estado;
        $pago->update($validated);

        ProcesoAuditoria::registrar(
            $procesoId,
            'pago_actualizado',
            "Pago #{$pago->numero_pago} actualizado: {$estadoAnterior} → {$validated['estado']}"
        );

        return back()->with('success', 'Estado del pago actualizado.');
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────

    private function checkAcceso(Proceso $proceso): void
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('admin') ||
            $user->id === $proceso->supervisor_id ||
            $user->hasAnyRole(['planeacion', 'hacienda', 'juridica', 'secop', 'unidad_solicitante']),
            403
        );
    }
}
