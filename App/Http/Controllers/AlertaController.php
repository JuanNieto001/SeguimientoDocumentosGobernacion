<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alerta;
use App\Services\AlertaService;

class AlertaController extends Controller
{
    /**
     * Lista de alertas (filtradas por área del usuario)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Obtener el área según el rol del usuario
        $area = $this->obtenerAreaUsuario($user);
        
        // Filtros
        $tipo = $request->input('tipo');
        $prioridad = $request->input('prioridad');
        $leida = $request->input('leida', 'no_leidas');

        $query = Alerta::with(['proceso', 'proceso.workflow']);

        // Filtrar por área si no es admin
        if (!$user->hasRole('admin')) {
            if ($area) {
                $query->where('area_responsable', $area);
            } else {
                $query->where('user_id', $user->id);
            }
        }

        // Filtro de tipo
        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        // Filtro de prioridad
        if ($prioridad) {
            $query->where('prioridad', $prioridad);
        }

        // Filtro de leídas/no leídas
        if ($leida === 'no_leidas') {
            $query->where('leida', false);
        } elseif ($leida === 'leidas') {
            $query->where('leida', true);
        }

        $alertas = $query->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Estadísticas
        $estadisticas = [
            'total' => Alerta::where('leida', false)->count(),
            'alta' => Alerta::where('leida', false)->where('prioridad', 'alta')->count(),
            'media' => Alerta::where('leida', false)->where('prioridad', 'media')->count(),
            'baja' => Alerta::where('leida', false)->where('prioridad', 'baja')->count(),
        ];

        return view('alertas.index', compact('alertas', 'estadisticas', 'area'));
    }

    /**
     * Marcar alerta como leída
     */
    public function marcarLeida($id)
    {
        $alerta = Alerta::findOrFail($id);
        
        // Verificar permisos
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            $area = $this->obtenerAreaUsuario($user);
            if ($alerta->area_responsable !== $area && $alerta->user_id !== $user->id) {
                abort(403, 'No tienes permiso para marcar esta alerta');
            }
        }

        AlertaService::marcarLeida($id);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alerta marcada como leída');
    }

    /**
     * Marcar todas las alertas como leídas
     */
    public function marcarTodasLeidas()
    {
        $user = auth()->user();
        $area = $this->obtenerAreaUsuario($user);

        $query = Alerta::query();

        if (!$user->hasRole('admin')) {
            if ($area) {
                $query->where('area_responsable', $area);
            } else {
                $query->where('user_id', $user->id);
            }
        }

        $count = $query->update([
            'leida' => true,
            'leida_at' => now()
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'count' => $count]);
        }

        return redirect()->back()->with('success', "Se marcaron {$count} alertas como leídas");
    }

    /**
     * Eliminar alerta
     */
    public function destroy($id)
    {
        $alerta = Alerta::findOrFail($id);
        
        // Verificar permisos
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            $area = $this->obtenerAreaUsuario($user);
            if ($alerta->area_responsable !== $area && $alerta->user_id !== $user->id) {
                abort(403, 'No tienes permiso para eliminar esta alerta');
            }
        }

        $alerta->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alerta eliminada');
    }

    /**
     * Obtener área del usuario según sus roles
     */
    private function obtenerAreaUsuario($user): ?string
    {
        if ($user->hasRole('planeacion')) return 'planeacion';
        if ($user->hasRole('hacienda')) return 'hacienda';
        if ($user->hasRole('juridica')) return 'juridica';
        if ($user->hasRole('secop')) return 'secop';
        if ($user->hasRole('unidad_solicitante')) return 'unidad_solicitante';
        
        return null;
    }

    /**
     * Widget de alertas para dashboard
     */
    public function widget()
    {
        $user = auth()->user();
        $area = $this->obtenerAreaUsuario($user);

        $query = Alerta::with(['proceso'])
            ->where('leida', false);

        if (!$user->hasRole('admin')) {
            if ($area) {
                $query->where('area_responsable', $area);
            } else {
                $query->where('user_id', $user->id);
            }
        }

        $alertas = $query->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $total = $query->count();

        return response()->json([
            'alertas' => $alertas,
            'total' => $total
        ]);
    }
}
