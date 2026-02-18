<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Proceso;
use App\Models\ProcesoEtapaArchivo;
use App\Models\Alerta;
use App\Models\Workflow;
use App\Models\Etapa;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Admin: vista principal del sistema con indicadores
        if ($user->hasRole('admin')) {
            return $this->dashboardAdmin();
        }

        $base = DB::table('procesos')
            ->leftJoin('workflows', 'workflows.id', '=', 'procesos.workflow_id')
            ->select([
                'procesos.*',
                'workflows.nombre as workflow_nombre',
            ])
            ->orderByDesc('procesos.id');

        // Unidad ve lo suyo. Áreas ven lo que esté en su bandeja.
        if ($user->hasRole('unidad_solicitante')) {
            $base->where('procesos.created_by', $user->id);
        } else {
            $rolesArea = ['planeacion', 'hacienda', 'juridica', 'secop'];
            $miRolArea = collect($rolesArea)->first(fn ($r) => $user->hasRole($r));
            if ($miRolArea) {
                $base->where('procesos.area_actual_role', $miRolArea);
            } else {
                $base->whereRaw('1=0');
            }
        }

        $all = $base->get();

        $enCurso = $all->where('estado', 'EN_CURSO')->values();
        $finalizados = $all->where('estado', 'FINALIZADO')->values();

        return view('dashboard', compact('enCurso', 'finalizados'));
    }

    /**
     * Dashboard para administradores con todos los indicadores
     */
    private function dashboardAdmin()
    {
        $indicadores = $this->indicadoresGenerales();
        $estadisticasArea = $this->estadisticasPorArea();
        $estadisticasEtapa = $this->indicadoresPorEtapa();
        $alertasRiesgos = $this->indicadoresAlertasRiesgos();
        $eficiencia = $this->indicadoresEficiencia();
        $seguimientoProcesos = $this->seguimientoProcesos();

        return view('dashboard.admin', compact(
            'indicadores',
            'estadisticasArea',
            'estadisticasEtapa',
            'alertasRiesgos',
            'eficiencia',
            'seguimientoProcesos'
        ));
    }

    /**
     * Listado resumido de procesos con su ubicación actual y estado de recibo/envío
     */
    private function seguimientoProcesos()
    {
        return DB::table('procesos as p')
            ->leftJoin('workflows as w', 'w.id', '=', 'p.workflow_id')
            ->leftJoin('etapas as e', 'e.id', '=', 'p.etapa_actual_id')
            ->leftJoin('proceso_etapas as pe', function ($join) {
                $join->on('pe.proceso_id', '=', 'p.id')
                    ->on('pe.etapa_id', '=', 'p.etapa_actual_id');
            })
            ->select(
                'p.id',
                'p.codigo',
                'p.objeto',
                'p.estado',
                'p.area_actual_role',
                'p.updated_at',
                'w.nombre as workflow',
                'e.nombre as etapa',
                'pe.recibido',
                'pe.enviado',
                'pe.recibido_at',
                'pe.enviado_at'
            )
            ->selectSub(function ($q) {
                $q->from('proceso_etapa_archivos as a')
                    ->whereColumn('a.proceso_id', 'p.id')
                    ->whereColumn('a.etapa_id', 'p.etapa_actual_id')
                    ->where('a.estado', 'rechazado')
                    ->selectRaw('count(*)');
            }, 'rechazados')
            ->selectSub(function ($q) {
                $q->from('proceso_etapa_archivos as a')
                    ->whereColumn('a.proceso_id', 'p.id')
                    ->whereColumn('a.etapa_id', 'p.etapa_actual_id')
                    ->whereIn('a.estado', ['pendiente', 'en_revision'])
                    ->selectRaw('count(*)');
            }, 'pendientes')
            ->orderByDesc('p.id')
            ->limit(20)
            ->get();
    }

    /**
     * Indicadores generales del sistema
     */
    public function indicadoresGenerales()
    {
        $hoy = now();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        $indicadores = [
            // Totales generales
            'total_procesos' => Proceso::count(),
            'procesos_activos' => Proceso::whereNotIn('estado', ['completado', 'cerrado', 'rechazado'])->count(),
            'procesos_finalizados' => Proceso::whereIn('estado', ['completado', 'cerrado'])->count(),
            'procesos_rechazados' => Proceso::where('estado', 'rechazado')->count(),
            
            // Del mes actual
            'procesos_mes' => Proceso::whereBetween('created_at', [$inicioMes, $finMes])->count(),
            'finalizados_mes' => Proceso::whereIn('estado', ['completado', 'cerrado'])
                ->whereBetween('updated_at', [$inicioMes, $finMes])
                ->count(),
            
            // Alertas
            'alertas_activas' => Alerta::where('leida', false)->count(),
            'alertas_alta_prioridad' => Alerta::where('leida', false)
                ->where('prioridad', 'alta')
                ->count(),
            
            // Documentos
            'documentos_totales' => ProcesoEtapaArchivo::count(),
            'documentos_pendientes' => ProcesoEtapaArchivo::where('estado', 'pendiente')->count(),
            'documentos_rechazados' => ProcesoEtapaArchivo::where('estado', 'rechazado')->count(),
            
            // Por modalidad
            'por_modalidad' => DB::table('procesos')
                ->join('workflows', 'procesos.workflow_id', '=', 'workflows.id')
                ->select('workflows.nombre', 'workflows.codigo', DB::raw('count(*) as total'))
                ->groupBy('workflows.id', 'workflows.nombre', 'workflows.codigo')
                ->get(),
            
            // Tendencia (últimos 6 meses)
            'tendencia' => $this->obtenerTendenciaUltimosSeisMeses(),
        ];

        return $indicadores;
    }

    /**
     * Estadísticas por área responsable
     */
    public function estadisticasPorArea()
    {
        $areas = ['unidad_solicitante', 'planeacion', 'hacienda', 'juridica', 'secop'];
        
        $estadisticas = [];
        
        foreach ($areas as $area) {
            $estadisticas[$area] = [
                'total' => Proceso::where('area_actual_role', $area)->count(),
                'alertas' => Alerta::where('area_responsable', $area)
                    ->where('leida', false)
                    ->count(),
                'documentos_pendientes' => ProcesoEtapaArchivo::whereHas('proceso', function($q) use ($area) {
                    $q->where('area_actual_role', $area);
                })->where('estado', 'pendiente')->count(),
            ];
        }

        return $estadisticas;
    }

    /**
     * Indicadores por etapa (distribución de procesos)
     */
    public function indicadoresPorEtapa()
    {
        $distribucion = DB::table('procesos')
            ->join('etapas', 'procesos.etapa_actual_id', '=', 'etapas.id')
            ->join('workflows', 'procesos.workflow_id', '=', 'workflows.id')
            ->select(
                'etapas.nombre as etapa',
                'workflows.nombre as workflow',
                // Usamos area_role porque el schema no tiene area_responsable
                'etapas.area_role',
                DB::raw('count(*) as total')
            )
            ->whereNotIn('procesos.estado', ['completado', 'cerrado', 'rechazado'])
            ->groupBy('etapas.id', 'etapas.nombre', 'workflows.nombre', 'etapas.area_role')
            ->orderBy('total', 'desc')
            ->get();

        // Agrupar por fase (Preparatoria, Precontractual, Contractual, Poscontractual)
        // Nota: el schema actual no tiene columna etapas.fase; devolver vacío para evitar error
        $porFase = collect();

        return [
            'distribucion' => $distribucion,
            'por_fase' => $porFase,
        ];
    }

    /**
     * Indicadores de cumplimiento documental
     */
    public function indicadoresCumplimientoDocumental()
    {
        $procesos = Proceso::whereNotIn('estado', ['completado', 'cerrado', 'rechazado'])->get();
        
        $estadisticas = [
            'procesos_con_documentos_completos' => 0,
            'procesos_con_documentos_pendientes' => 0,
            'procesos_con_documentos_rechazados' => 0,
            'tasa_aprobacion' => 0,
        ];

        foreach ($procesos as $proceso) {
            $documentos = ProcesoEtapaArchivo::where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->get();

            if ($documentos->isEmpty()) {
                continue;
            }

            $pendientes = $documentos->where('estado', 'pendiente')->count();
            $rechazados = $documentos->where('estado', 'rechazado')->count();

            if ($rechazados > 0) {
                $estadisticas['procesos_con_documentos_rechazados']++;
            } elseif ($pendientes > 0) {
                $estadisticas['procesos_con_documentos_pendientes']++;
            } else {
                $estadisticas['procesos_con_documentos_completos']++;
            }
        }

        $totalDocumentos = ProcesoEtapaArchivo::count();
        $documentosAprobados = ProcesoEtapaArchivo::where('estado', 'aprobado')->count();
        
        $estadisticas['tasa_aprobacion'] = $totalDocumentos > 0 
            ? round(($documentosAprobados / $totalDocumentos) * 100, 2) 
            : 0;

        return $estadisticas;
    }

    /**
     * Indicadores de alertas y riesgos
     */
    public function indicadoresAlertasRiesgos()
    {
        $indicadores = [
            'procesos_con_retraso' => Alerta::where('tipo', 'tiempo_excedido')
                ->where('leida', false)
                ->distinct('proceso_id')
                ->count('proceso_id'),
            
            'certificados_por_vencer' => Alerta::where('tipo', 'certificado_por_vencer')
                ->where('leida', false)
                ->count(),
            
            'documentos_rechazados' => Alerta::where('tipo', 'documento_rechazado')
                ->where('leida', false)
                ->count(),
            
            'procesos_sin_actividad' => Alerta::where('tipo', 'sin_actividad')
                ->where('leida', false)
                ->distinct('proceso_id')
                ->count('proceso_id'),
            
            'alertas_por_prioridad' => Alerta::where('leida', false)
                ->select('prioridad', DB::raw('count(*) as total'))
                ->groupBy('prioridad')
                ->pluck('total', 'prioridad')
                ->toArray(),
        ];

        return $indicadores;
    }

    /**
     * Indicadores de eficiencia (tiempos promedio)
     */
    public function indicadoresEficiencia()
    {
        // Procesos finalizados en los últimos 3 meses
        $tresMonthsAgo = now()->subMonths(3);
        
        $procesosFinalizados = Proceso::whereIn('estado', ['completado', 'cerrado'])
            ->where('updated_at', '>', $tresMonthsAgo)
            ->get();

        $tiempoPromedioTotal = 0;
        $count = 0;

        foreach ($procesosFinalizados as $proceso) {
            $diasTotal = $proceso->created_at->diffInDays($proceso->updated_at);
            $tiempoPromedioTotal += $diasTotal;
            $count++;
        }

        $promedioGeneral = $count > 0 ? round($tiempoPromedioTotal / $count, 2) : 0;

        // Tiempo promedio por modalidad
        $tiempoPorModalidad = DB::table('procesos')
            ->join('workflows', 'procesos.workflow_id', '=', 'workflows.id')
            ->select(
                'workflows.nombre',
                DB::raw('AVG(JULIANDAY(procesos.updated_at) - JULIANDAY(procesos.created_at)) as promedio_dias')
            )
            ->whereIn('procesos.estado', ['completado', 'cerrado'])
            ->where('procesos.updated_at', '>', $tresMonthsAgo)
            ->groupBy('workflows.id', 'workflows.nombre')
            ->get();

        return [
            'promedio_general_dias' => $promedioGeneral,
            'procesos_finalizados_3meses' => $count,
            'por_modalidad' => $tiempoPorModalidad,
        ];
    }

    /**
     * Indicadores por responsable (carga de trabajo)
     */
    public function indicadoresPorResponsable()
    {
        $usuarios = DB::table('users')
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('model_has_roles')
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->whereIn('model_has_roles.role_id', function($q) {
                        $q->select('id')
                            ->from('roles')
                            ->whereIn('name', ['planeacion', 'hacienda', 'juridica', 'secop']);
                    });
            })
            ->get();

        $cargaTrabajo = [];

        foreach ($usuarios as $usuario) {
            $cargaTrabajo[$usuario->id] = [
                'nombre' => $usuario->name,
                'email' => $usuario->email,
                'procesos_activos' => Proceso::whereNotIn('estado', ['completado', 'cerrado', 'rechazado'])
                    ->whereHas('procesoEtapas', function($q) use ($usuario) {
                        $q->where('recibido_por', $usuario->id)
                            ->whereNull('enviado_at');
                    })
                    ->count(),
                'alertas_pendientes' => Alerta::where('user_id', $usuario->id)
                    ->where('leida', false)
                    ->count(),
                'documentos_por_aprobar' => ProcesoEtapaArchivo::where('uploaded_by', '!=', $usuario->id)
                    ->where('estado', 'pendiente')
                    ->whereHas('proceso', function($q) use ($usuario) {
                        $q->whereHas('procesoEtapas', function($pe) use ($usuario) {
                            $pe->where('recibido_por', $usuario->id)
                                ->whereNull('enviado_at');
                        });
                    })
                    ->count(),
            ];
        }

        return $cargaTrabajo;
    }

    /**
     * Búsqueda rápida de procesos
     */
    public function buscar(Request $request)
    {
        $termino = $request->input('q');
        
        $resultados = Proceso::with(['workflow', 'etapaActual'])
            ->where(function($query) use ($termino) {
                $query->where('codigo', 'like', "%{$termino}%")
                    ->orWhere('objeto', 'like', "%{$termino}%")
                    ->orWhere('descripcion', 'like', "%{$termino}%");
            })
            ->limit(20)
            ->get();

        return response()->json($resultados);
    }

    /**
     * Reporte general del dashboard
     */
    public function reporte(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth());

        $datos = [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'indicadores' => $this->indicadoresGenerales(),
            'por_area' => $this->estadisticasPorArea(),
            'por_etapa' => $this->indicadoresPorEtapa(),
            'cumplimiento_documental' => $this->indicadoresCumplimientoDocumental(),
            'alertas_riesgos' => $this->indicadoresAlertasRiesgos(),
            'eficiencia' => $this->indicadoresEficiencia(),
        ];

        if ($request->wantsJson()) {
            return response()->json($datos);
        }

        return view('dashboard.reporte', compact('datos'));
    }

    /**
     * Obtener tendencia de los últimos 6 meses
     */
    private function obtenerTendenciaUltimosSeisMeses()
    {
        $tendencia = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $inicioMes = $mes->copy()->startOfMonth();
            $finMes = $mes->copy()->endOfMonth();
            
            $tendencia[] = [
                'mes' => $mes->format('M Y'),
                'creados' => Proceso::whereBetween('created_at', [$inicioMes, $finMes])->count(),
                'finalizados' => Proceso::whereIn('estado', ['completado', 'cerrado'])
                    ->whereBetween('updated_at', [$inicioMes, $finMes])
                    ->count(),
            ];
        }
        
        return $tendencia;
    }
}
