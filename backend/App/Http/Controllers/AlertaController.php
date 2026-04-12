<?php
/**
 * Archivo: backend/App/Http/Controllers/AlertaController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alerta;
use App\Services\AlertaService;

class AlertaController extends Controller
{
    /**
     * Abrir centro de alertas desde campanita: limpia punto rojo al primer clic.
     */
    public function abrirCentro()
    {
        $user = auth()->user();
        $area = $this->obtenerAreaUsuario($user);

        $query = Alerta::query()->where('leida', false);

        if (!$user->hasRole('admin')) {
            $query->where(function ($q) use ($user, $area) {
                $q->where('user_id', $user->id);
                if ($area) {
                    $q->orWhere('area_responsable', $area);
                }
            });
        }

        $query->update([
            'leida' => true,
            'leida_at' => now(),
        ]);

        return redirect()->route('alertas.index');
    }

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

        $query = Alerta::with(['proceso', 'proceso.workflow', 'procesoCd']);

        // Filtrar por área si no es admin
        if (!$user->hasRole('admin')) {
            $query->where(function ($q) use ($user, $area) {
                $q->where('user_id', $user->id);
                if ($area) {
                    $q->orWhere('area_responsable', $area);
                }
            });
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

        // Estadísticas (mismas reglas de visibilidad del usuario)
        $statsQuery = Alerta::query();
        if (!$user->hasRole('admin')) {
            $statsQuery->where(function ($q) use ($user, $area) {
                $q->where('user_id', $user->id);
                if ($area) {
                    $q->orWhere('area_responsable', $area);
                }
            });
        }
        $estadisticas = [
            'total' => (clone $statsQuery)->where('leida', false)->count(),
            'alta' => (clone $statsQuery)->where('leida', false)->where('prioridad', 'alta')->count(),
            'media' => (clone $statsQuery)->where('leida', false)->where('prioridad', 'media')->count(),
            'baja' => (clone $statsQuery)->where('leida', false)->where('prioridad', 'baja')->count(),
        ];

        return view('backend.alertas.index', compact('alertas', 'estadisticas', 'area'));
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
            $query->where(function ($q) use ($user, $area) {
                $q->where('user_id', $user->id);
                if ($area) {
                    $q->orWhere('area_responsable', $area);
                }
            });
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
        if ($user->hasRole('rentas')) return 'rentas';
        if ($user->hasRole('contabilidad')) return 'contabilidad';
        if ($user->hasRole('presupuesto')) return 'presupuesto';
        if ($user->hasRole('inversiones_publicas')) return 'inversiones_publicas';
        if ($user->hasRole('radicacion')) return 'radicacion';
        if ($user->hasRole('compras')) return 'compras';
        if ($user->hasRole('talento_humano')) return 'talento_humano';
        // Compatibilidad histórica: el rol descentralizacion opera sobre área planeacion.
        if ($user->hasRole('descentralizacion')) return 'planeacion';
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

        $query = Alerta::with(['proceso', 'procesoCd'])
            ->where('leida', false);

        if (!$user->hasRole('admin')) {
            $query->where(function ($q) use ($user, $area) {
                $q->where('user_id', $user->id);
                if ($area) {
                    $q->orWhere('area_responsable', $area);
                }
            });
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

