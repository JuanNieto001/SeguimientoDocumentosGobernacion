<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
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

        $proceso = Proceso::with(['etapaActual', 'workflow', 'procesoEtapas'])
            ->where('codigo', $codigo)
            ->first();

        // Historial de tracking para este código
        $historialTracking = TrackingEvento::with('user')
            ->where('codigo_proceso', $codigo)
            ->orderByDesc('created_at')
            ->get();

        if (!$proceso) {
            return back()
                ->withInput()
                ->with('error_busqueda', "No se encontró ningún proceso con el código «{$codigo}».")
                ->with('codigo_buscado', $codigo);
        }

        // Registrar evento de consulta automático
        TrackingEvento::create([
            'codigo_proceso'   => $codigo,
            'proceso_id'       => $proceso->id,
            'user_id'          => auth()->id(),
            'tipo'             => 'consulta',
            'responsable_nombre' => auth()->user()->name,
            'ip_address'       => request()->ip(),
        ]);

        return view('tracking.resultado', compact('proceso', 'codigo', 'historialTracking'));
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
