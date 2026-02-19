<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcesoAuditoria;
use App\Models\Proceso;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            abort(403);
        }

        $query = ProcesoAuditoria::with(['user', 'proceso', 'etapa'])
            ->orderByDesc('created_at');

        // Filtro por proceso
        if ($request->filled('proceso_id')) {
            $query->where('proceso_id', $request->proceso_id);
        }

        // Filtro por acción
        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        // Filtro por usuario
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por fecha
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        $logs = $query->paginate(50)->appends($request->query());

        // Datos para filtros
        $procesos = Proceso::select('id', 'codigo')->orderBy('codigo')->get();
        $acciones = ProcesoAuditoria::select('accion')->distinct()->orderBy('accion')->pluck('accion');
        $usuarios = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        // Stats
        $stats = [
            'total'     => ProcesoAuditoria::count(),
            'hoy'       => ProcesoAuditoria::whereDate('created_at', today())->count(),
            'usuarios'  => ProcesoAuditoria::distinct('user_id')->count('user_id'),
            'procesos'  => ProcesoAuditoria::distinct('proceso_id')->count('proceso_id'),
        ];

        return view('admin.logs', compact('logs', 'procesos', 'acciones', 'usuarios', 'stats'));
    }

    /**
     * Detalle de logs de un proceso específico
     */
    public function show(Request $request, $procesoId)
    {
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            abort(403);
        }

        $proceso = Proceso::with([
            'workflow',
            'etapaActual',
            'procesoEtapas.etapa',
            'procesoEtapas.checks.item',
            'procesoEtapas.recibidoPor',
            'procesoEtapas.enviadoPor',
            'creador',
        ])->findOrFail($procesoId);

        $logs = ProcesoAuditoria::with(['user', 'etapa'])
            ->where('proceso_id', $procesoId)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.logs-proceso', compact('proceso', 'logs'));
    }
}
