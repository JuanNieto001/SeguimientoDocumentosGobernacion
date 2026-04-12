<?php
/**
 * Archivo: backend/App/Http/Controllers/TrackingController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\TrackingEvento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    /**
     * Pantalla principal de tracking por código
     */
    public function index()
    {
        $ultimosEventos = TrackingEvento::with(['proceso', 'user'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('tracking.index', compact('ultimosEventos'));
    }

    /**
     * Buscar un proceso por código
     */
    public function buscar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50',
        ], [
            'codigo.required' => 'Ingresa el código del proceso.',
        ]);

        $codigo = strtoupper(trim($request->codigo));

        $proceso = Proceso::with(['etapaActual', 'workflow.etapas', 'procesoEtapas', 'creador', 'secretariaOrigen', 'unidadOrigen'])
            ->where('codigo', $codigo)
            ->first();

        if (!$proceso) {
            return back()
                ->withInput()
                ->with('error_busqueda', "No se encontró ningún proceso con el código «{$codigo}».")
                ->with('codigo_buscado', $codigo);
        }

        // Calcular porcentaje de avance
        $totalEtapas   = optional($proceso->workflow)->etapas->count() ?: 1;
        $ordenActual   = optional($proceso->etapaActual)->orden ?? 0;
        $porcentaje    = $proceso->estado === 'FINALIZADO'
            ? 100
            : (int) round(($ordenActual / $totalEtapas) * 100);

        // Registrar evento de consulta automático
        TrackingEvento::create([
            'codigo_proceso'     => $codigo,
            'proceso_id'         => $proceso->id,
            'user_id'            => auth()->id(),
            'tipo'               => 'consulta',
            'responsable_nombre' => auth()->user()->name,
            'ip_address'         => request()->ip(),
        ]);

        // ── Auditoría del proceso ────────────────────────────────────────
        $auditoria = ProcesoAuditoria::with(['user', 'etapa'])
            ->where('proceso_id', $proceso->id)
            ->orderByDesc('created_at')
            ->get();

        // ── Movimientos de tracking (sin consultas) ──────────────────────
        $trackingEventos = TrackingEvento::with('user')
            ->where('codigo_proceso', $codigo)
            ->whereNotIn('tipo', ['consulta'])
            ->orderByDesc('created_at')
            ->get();

        // ── Mapa de etiquetas para acciones de auditoría ─────────────────
        $accionLabels = [
            'proceso_creado'       => 'Proceso creado',
            'etapa_avanzada'       => 'Etapa avanzada',
            'documento_subido'     => 'Documento subido',
            'proceso_aprobado'     => 'Proceso aprobado',
            'revision_solicitada'  => 'Revisión solicitada',
            'proceso_rechazado'    => 'Proceso rechazado',
            'proceso_actualizado'  => 'Proceso actualizado',
            'proceso_devuelto'     => 'Proceso devuelto',
            'proceso_finalizado'   => 'Proceso finalizado',
            'etapa_iniciada'       => 'Etapa iniciada',
            'observacion_agregada' => 'Observación agregada',
        ];
        $accionColors = [
            'proceso_creado'       => ['bg' => '#dcfce7', 'c' => '#15803d', 'dot' => '#22c55e'],
            'etapa_avanzada'       => ['bg' => '#ccfbf1', 'c' => '#0f766e', 'dot' => '#0d9488'],
            'documento_subido'     => ['bg' => '#f3e8ff', 'c' => '#7e22ce', 'dot' => '#a855f7'],
            'proceso_aprobado'     => ['bg' => '#dcfce7', 'c' => '#15803d', 'dot' => '#22c55e'],
            'revision_solicitada'  => ['bg' => '#fef9c3', 'c' => '#92400e', 'dot' => '#f59e0b'],
            'proceso_rechazado'    => ['bg' => '#fee2e2', 'c' => '#991b1b', 'dot' => '#ef4444'],
            'proceso_devuelto'     => ['bg' => '#ffedd5', 'c' => '#9a3412', 'dot' => '#f97316'],
            'proceso_finalizado'   => ['bg' => '#dcfce7', 'c' => '#15803d', 'dot' => '#16a34a'],
            'default'              => ['bg' => '#f0fdf4', 'c' => '#166534', 'dot' => '#16a34a'],
        ];
        $trackingColors = [
            'entrega'   => ['bg' => '#dbeafe', 'c' => '#1d4ed8', 'dot' => '#3b82f6'],
            'recepcion' => ['bg' => '#dcfce7', 'c' => '#15803d', 'dot' => '#16a34a'],
        ];

        // ── Construir timeline unificado ─────────────────────────────────
        $timeline = collect();

        foreach ($auditoria as $a) {
            $colores = $accionColors[$a->accion] ?? $accionColors['default'];
            $timeline->push([
                'source'   => 'auditoria',
                'tipo'     => $a->accion,
                'label'    => $accionLabels[$a->accion] ?? ucwords(str_replace('_', ' ', $a->accion)),
                'detalle'  => $a->descripcion,
                'usuario'  => optional($a->user)->name ?? 'Sistema',
                'etapa'    => optional($a->etapa)->nombre,
                'fecha'    => $a->created_at,
                'ip'       => $a->ip_address,
                'bg'       => $colores['bg'],
                'color'    => $colores['c'],
                'dot'      => $colores['dot'],
                'extra'    => $a->datos_nuevos,
            ]);
        }

        foreach ($trackingEventos as $t) {
            $colores = $trackingColors[$t->tipo] ?? ['bg' => '#f1f5f9', 'c' => '#475569', 'dot' => '#94a3b8'];
            $labels  = ['entrega' => 'Entrega física', 'recepcion' => 'Recepción física'];
            $timeline->push([
                'source'      => 'tracking',
                'tipo'        => $t->tipo,
                'label'       => $labels[$t->tipo] ?? ucfirst($t->tipo),
                'detalle'     => $t->observaciones,
                'usuario'     => $t->responsable_nombre ?? optional($t->user)->name ?? '—',
                'etapa'       => null,
                'fecha'       => $t->created_at,
                'ip'          => $t->ip_address,
                'bg'          => $colores['bg'],
                'color'       => $colores['c'],
                'dot'         => $colores['dot'],
                'area_origen' => $t->area_origen,
                'area_destino'=> $t->area_destino,
                'extra'       => null,
            ]);
        }

        $timeline = $timeline->sortByDesc('fecha')->values();

        // También pasamos historialTracking para compatibilidad
        $historialTracking = $trackingEventos;

        return view('tracking.resultado', compact('proceso', 'codigo', 'historialTracking', 'timeline', 'porcentaje', 'totalEtapas', 'ordenActual'));
    }

    /**
     * Registrar una entrega o recepción física
     */
    public function registrar(Request $request)
    {
        $validated = $request->validate([
            'codigo_proceso'     => 'required|string|max:50',
            'tipo'               => 'required|in:entrega,recepcion',
            'area_origen'        => 'nullable|string|max:150',
            'area_destino'       => 'nullable|string|max:150',
            'responsable_nombre' => 'nullable|string|max:255',
            'observaciones'      => 'nullable|string|max:1000',
        ], [
            'tipo.required'           => 'Selecciona el tipo de evento.',
            'codigo_proceso.required' => 'El código del proceso es obligatorio.',
        ]);

        $codigo = strtoupper(trim($validated['codigo_proceso']));

        $proceso = Proceso::where('codigo', $codigo)->first();

        TrackingEvento::create([
            'codigo_proceso'     => $codigo,
            'proceso_id'         => $proceso?->id,
            'user_id'            => auth()->id(),
            'tipo'               => $validated['tipo'],
            'area_origen'        => $validated['area_origen'] ?? null,
            'area_destino'       => $validated['area_destino'] ?? null,
            'responsable_nombre' => $validated['responsable_nombre'] ?? auth()->user()->name,
            'observaciones'      => $validated['observaciones'] ?? null,
            'ip_address'         => request()->ip(),
        ]);

        $tipoLabel = $validated['tipo'] === 'entrega' ? 'Entrega' : 'Recepción';

        return back()->with('success_tracking', "{$tipoLabel} registrada correctamente para el proceso {$codigo}.");
    }
}

