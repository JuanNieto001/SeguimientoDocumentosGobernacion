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
        
        // Obtener el alcance del rol del usuario
        $role = $user->roles()->first();
        $dashboardScope = $role->dashboard_scope ?? 'propios';
        
        // Si el alcance es global, secretaria o unidad, usar el dashboard ejecutivo
        if (in_array($dashboardScope, ['global', 'secretaria', 'unidad'])) {
            return $this->dashboardEjecutivo($user, $dashboardScope);
        }

        // ── Roles de área específica (solicitudes paralelas Etapa 1) ──────────
        $rolesDocumentos = ['compras', 'talento_humano', 'rentas', 'contabilidad', 'inversiones_publicas', 'presupuesto', 'radicacion'];
        $userRolesDoc = collect($rolesDocumentos)->filter(fn($r) => $user->hasRole($r));

        // Cargar solicitudes de documentos pendientes/subidos para estos roles
        $solicitudesPendientes = collect();
        if ($userRolesDoc->isNotEmpty()) {
            $solicitudesPendientes = DB::table('proceso_documentos_solicitados as pds')
                ->join('procesos', 'procesos.id', '=', 'pds.proceso_id')
                ->leftJoin('etapas', 'etapas.id', '=', 'procesos.etapa_actual_id')
                ->whereIn('pds.area_responsable_rol', $userRolesDoc->values()->toArray())
                ->select(
                    'procesos.id as proceso_id',
                    'procesos.codigo as proceso_codigo',
                    'procesos.objeto as proceso_objeto',
                    'etapas.nombre as etapa_nombre',
                    DB::raw('COUNT(*) as total_docs'),
                    DB::raw('SUM(CASE WHEN pds.estado = "subido" THEN 1 ELSE 0 END) as docs_subidos'),
                    DB::raw('SUM(CASE WHEN pds.estado = "pendiente" AND pds.puede_subir = 1 THEN 1 ELSE 0 END) as docs_pendientes')
                )
                ->groupBy('procesos.id', 'procesos.codigo', 'procesos.objeto', 'etapas.nombre')
                ->orderByDesc('procesos.id')
                ->get();
        }

        $base = DB::table('procesos')
            ->leftJoin('workflows', 'workflows.id', '=', 'procesos.workflow_id')
            ->select([
                'procesos.*',
                'workflows.nombre as workflow_nombre',
            ])
            ->orderByDesc('procesos.id');

        // Unidad ve lo suyo. Planeación ve todo. Áreas ven lo que esté en su bandeja.
        if ($user->hasRole('unidad_solicitante')) {
            $base->where('procesos.created_by', $user->id);
        } elseif ($user->hasRole('planeacion') && $userRolesDoc->isEmpty()) {
            // Planeación pura supervisa todos los procesos — sin filtro adicional
        } else {
            $rolesArea = ['hacienda', 'juridica', 'secop'];
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

        $metricas = $this->computeMetricas($user, $userRolesDoc);

        return view('dashboard', compact('enCurso', 'finalizados', 'solicitudesPendientes', 'metricas'));
    }

    /**
     * Dashboard ejecutivo con scope según rol:
     * - 'global'     → Admin / Gobernador: todos los procesos
     * - 'secretaria' → Secretario: solo procesos de su secretaría
     * - 'unidad'     → Jefe de unidad: solo procesos de su unidad
     */
    private function dashboardEjecutivo($user, $scope)
    {
        $inicioMes = now()->startOfMonth();
        $finMes    = now()->endOfMonth();

        $secId = ($scope === 'secretaria' && $user->secretaria_id) ? (int) $user->secretaria_id : null;
        $uniId = ($scope === 'unidad'     && $user->unidad_id)     ? (int) $user->unidad_id     : null;

        // Helper: query base con scope aplicado
        $scoped = fn() => DB::table('procesos')
            ->when($secId, fn($q) => $q->where('secretaria_origen_id', $secId))
            ->when($uniId, fn($q) => $q->where('unidad_origen_id', $uniId));

        // ── KPIs principales ────────────────────────────────────────────────
        $totalProcesos    = $scoped()->count();
        $enCurso          = $scoped()->where('estado', 'EN_CURSO')->count();
        $finalizados      = $scoped()->whereIn('estado', ['FINALIZADO', 'completado', 'cerrado'])->count();
        $rechazados       = $scoped()->where('estado', 'RECHAZADO')->count();
        $creadosMes       = $scoped()->whereBetween('created_at', [$inicioMes, $finMes])->count();
        $finalizadosMes   = $scoped()
            ->whereIn('estado', ['FINALIZADO', 'completado', 'cerrado'])
            ->whereBetween('updated_at', [$inicioMes, $finMes])
            ->count();

        // ── Alertas ─────────────────────────────────────────────────────────
        $alertasAltas = Alerta::where('leida', false)->where('prioridad', 'alta')->count();
        $alertasTotal = Alerta::where('leida', false)->count();

        // ── Tendencia últimos 6 meses ────────────────────────────────────────
        $tendencia = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $ini = $mes->copy()->startOfMonth();
            $fin = $mes->copy()->endOfMonth();
            $tendencia[] = [
                'mes_corto'   => $mes->translatedFormat('M'),
                'mes'         => $mes->format('M Y'),
                'creados'     => $scoped()->whereBetween('created_at', [$ini, $fin])->count(),
                'finalizados' => $scoped()
                    ->whereIn('estado', ['FINALIZADO', 'completado', 'cerrado'])
                    ->whereBetween('updated_at', [$ini, $fin])
                    ->count(),
                'rechazados'  => $scoped()
                    ->where('estado', 'RECHAZADO')
                    ->whereBetween('updated_at', [$ini, $fin])
                    ->count(),
            ];
        }

        // ── Distribución por área (procesos EN_CURSO por área actual) ────────
        $areaLabels = [
            'unidad_solicitante' => 'Unidad Solicitante',
            'planeacion'         => 'Planeación',
            'hacienda'           => 'Hacienda',
            'juridica'           => 'Jurídica',
            'secop'              => 'SECOP',
        ];
        $areaColores = [
            'unidad_solicitante' => '#3b82f6',
            'planeacion'         => '#16a34a',
            'hacienda'           => '#ca8a04',
            'juridica'           => '#ea580c',
            'secop'              => '#9333ea',
        ];
        $porArea = [];
        foreach ($areaLabels as $role => $label) {
            $porArea[] = [
                'area'   => $role,
                'label'  => $label,
                'color'  => $areaColores[$role],
                'total'  => $scoped()->where('area_actual_role', $role)->where('estado', 'EN_CURSO')->count(),
                'alertas'=> Alerta::where('area_responsable', $role)->where('leida', false)->count(),
            ];
        }

        // ── Distribución por estado (para donut) ────────────────────────────
        $porEstadoRaw = $scoped()
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        // ── Por modalidad/workflow ──────────────────────────────────────────
        $porModalidad = $scoped()
            ->join('workflows', 'workflows.id', '=', 'procesos.workflow_id')
            ->select('workflows.nombre', DB::raw('count(*) as total'))
            ->groupBy('workflows.id', 'workflows.nombre')
            ->orderByDesc('total')
            ->get();

        // ── Alertas y riesgos ───────────────────────────────────────────────
        $alertasRiesgos = [
            'procesos_con_retraso'    => Alerta::where('tipo', 'tiempo_excedido')
                ->where('leida', false)->distinct('proceso_id')->count('proceso_id'),
            'documentos_rechazados'   => Alerta::where('tipo', 'documento_rechazado')
                ->where('leida', false)->count(),
            'procesos_sin_actividad'  => Alerta::where('tipo', 'sin_actividad')
                ->where('leida', false)->distinct('proceso_id')->count('proceso_id'),
            'certificados_por_vencer' => Alerta::where('tipo', 'certificado_por_vencer')
                ->where('leida', false)->count(),
        ];

        // ── Procesos recientes ──────────────────────────────────────────────
        $procesosRecientes = $scoped()
            ->leftJoin('workflows as w', 'w.id', '=', 'procesos.workflow_id')
            ->leftJoin('etapas as e', 'e.id', '=', 'procesos.etapa_actual_id')
            ->select(
                'procesos.id',
                'procesos.codigo',
                'procesos.objeto',
                'procesos.estado',
                'procesos.area_actual_role',
                'procesos.updated_at',
                'procesos.created_at',
                'w.nombre as workflow',
                'e.nombre as etapa'
            )
            ->orderByDesc('procesos.id')
            ->limit(15)
            ->get();

        // ── Lista lateral: secretarías (admin/gobernador) o unidades (secretario) ──
        $listaLateral = collect();
        $listaLateralTipo = null;

        if ($scope === 'global') {
            $listaLateralTipo = 'secretarias';
            $listaLateral = DB::table('secretarias')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get()
                ->map(function ($sec) use ($scoped) {
                    $sec->total   = $scoped()->where('secretaria_origen_id', $sec->id)->count();
                    $sec->en_curso = $scoped()->where('secretaria_origen_id', $sec->id)->where('estado', 'EN_CURSO')->count();
                    return $sec;
                });
        } elseif ($scope === 'secretaria' && $secId) {
            $listaLateralTipo = 'unidades';
            $listaLateral = DB::table('unidades')
                ->where('secretaria_id', $secId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get()
                ->map(function ($uni) use ($scoped) {
                    $uni->total    = $scoped()->where('unidad_origen_id', $uni->id)->count();
                    $uni->en_curso = $scoped()->where('unidad_origen_id', $uni->id)->where('estado', 'EN_CURSO')->count();
                    return $uni;
                });
        }

        // ── Nombre del scope ────────────────────────────────────────────────
        $scopeNombre = match (true) {
            $scope === 'global'     => 'Gobernación de Caldas',
            $scope === 'secretaria' => DB::table('secretarias')->where('id', $secId)->value('nombre') ?? 'Secretaría',
            $scope === 'unidad'     => DB::table('unidades')->where('id', $uniId)->value('nombre') ?? 'Unidad',
            default => 'Sistema',
        };

        return view('dashboard.admin', compact(
            'scope', 'scopeNombre', 'user',
            'totalProcesos', 'enCurso', 'finalizados', 'rechazados',
            'creadosMes', 'finalizadosMes',
            'alertasAltas', 'alertasTotal',
            'tendencia', 'porArea', 'porEstadoRaw', 'porModalidad',
            'alertasRiesgos', 'procesosRecientes',
            'listaLateral', 'listaLateralTipo'
        ));
    }

    /**
     * Métricas mensuales personalizadas según el rol del usuario (roles de área)
     */
    private function computeMetricas($user, $userRolesDoc)
    {
        $inicioMes = now()->startOfMonth();
        $finMes    = now()->endOfMonth();

        // ── Áreas de documentos (compras, talento_humano, rentas, etc.) ───────
        if ($userRolesDoc->isNotEmpty()) {
            $roles = $userRolesDoc->values()->toArray();

            $asignadosMes = DB::table('proceso_documentos_solicitados as pds')
                ->join('procesos', 'procesos.id', '=', 'pds.proceso_id')
                ->whereIn('pds.area_responsable_rol', $roles)
                ->whereBetween('pds.created_at', [$inicioMes, $finMes])
                ->distinct('pds.proceso_id')->count('pds.proceso_id');

            $subidosMes = DB::table('proceso_documentos_solicitados')
                ->whereIn('area_responsable_rol', $roles)
                ->where('estado', 'subido')
                ->whereBetween('subido_at', [$inicioMes, $finMes])
                ->count();

            $pendientesActual = DB::table('proceso_documentos_solicitados')
                ->whereIn('area_responsable_rol', $roles)
                ->where('estado', 'pendiente')
                ->count();

            $totalHistorico = DB::table('proceso_documentos_solicitados')
                ->whereIn('area_responsable_rol', $roles)
                ->count();

            return [
                'tipo' => 'area_doc',
                'mes'  => now()->translatedFormat('F Y'),
                'tarjetas' => [
                    ['icono' => '📂', 'valor' => $asignadosMes,    'label' => 'Procesos asignados este mes', 'color' => 'blue'],
                    ['icono' => '✅', 'valor' => $subidosMes,       'label' => 'Documentos subidos este mes',  'color' => 'green'],
                    ['icono' => '⏳', 'valor' => $pendientesActual,  'label' => 'Documentos pendientes',        'color' => 'yellow'],
                    ['icono' => '📊', 'valor' => $totalHistorico,    'label' => 'Total histórico asignados',    'color' => 'gray'],
                ],
            ];
        }

        // ── Unidad Solicitante ─────────────────────────────────────────────────
        if ($user->hasRole('unidad_solicitante')) {
            $creadosMes = DB::table('procesos')
                ->where('created_by', $user->id)
                ->whereBetween('created_at', [$inicioMes, $finMes])
                ->count();

            $finalizadosMes = DB::table('procesos')
                ->where('created_by', $user->id)
                ->whereIn('estado', ['FINALIZADO', 'completado', 'cerrado'])
                ->whereBetween('updated_at', [$inicioMes, $finMes])
                ->count();

            $rechazadosMes = DB::table('procesos')
                ->where('created_by', $user->id)
                ->where('estado', 'RECHAZADO')
                ->whereBetween('updated_at', [$inicioMes, $finMes])
                ->count();

            $enCursoActual = DB::table('procesos')
                ->where('created_by', $user->id)
                ->where('estado', 'EN_CURSO')
                ->count();

            return [
                'tipo' => 'unidad_solicitante',
                'mes'  => now()->translatedFormat('F Y'),
                'tarjetas' => [
                    ['icono' => '📋', 'valor' => $creadosMes,      'label' => 'Solicitudes creadas este mes',  'color' => 'blue'],
                    ['icono' => '🔄', 'valor' => $enCursoActual,    'label' => 'Procesos activos actualmente',  'color' => 'yellow'],
                    ['icono' => '✅', 'valor' => $finalizadosMes,    'label' => 'Finalizados este mes',          'color' => 'green'],
                    ['icono' => '❌', 'valor' => $rechazadosMes,     'label' => 'Rechazados este mes',           'color' => 'red'],
                ],
            ];
        }

        // ── Planeación ────────────────────────────────────────────────────────
        if ($user->hasRole('planeacion')) {
            $recibidosMes = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->where('e.area_role', 'planeacion')
                ->where('pe.recibido', true)
                ->whereBetween('pe.recibido_at', [$inicioMes, $finMes])
                ->count();

            $enviadosMes = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->where('e.area_role', 'planeacion')
                ->where('pe.enviado', true)
                ->whereBetween('pe.enviado_at', [$inicioMes, $finMes])
                ->count();

            $rechazadosMes = DB::table('procesos')
                ->where('estado', 'RECHAZADO')
                ->whereBetween('updated_at', [$inicioMes, $finMes])
                ->count();

            $enPlaneacion = DB::table('procesos')
                ->where('area_actual_role', 'planeacion')
                ->where('estado', 'EN_CURSO')
                ->count();

            return [
                'tipo' => 'planeacion',
                'mes'  => now()->translatedFormat('F Y'),
                'tarjetas' => [
                    ['icono' => '📥', 'valor' => $recibidosMes,  'label' => 'Recibidos este mes',           'color' => 'blue'],
                    ['icono' => '📤', 'valor' => $enviadosMes,   'label' => 'Enviados a áreas este mes',    'color' => 'green'],
                    ['icono' => '🔄', 'valor' => $enPlaneacion,  'label' => 'En tu bandeja ahora',          'color' => 'yellow'],
                    ['icono' => '❌', 'valor' => $rechazadosMes, 'label' => 'Rechazados este mes (sistema)', 'color' => 'red'],
                ],
            ];
        }

        // ── Hacienda / Jurídica / SECOP ───────────────────────────────────────
        $rolesArea = ['hacienda', 'juridica', 'secop'];
        $miRolArea = collect($rolesArea)->first(fn($r) => $user->hasRole($r));
        if ($miRolArea) {
            $recibidosMes = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->where('e.area_role', $miRolArea)
                ->where('pe.recibido', true)
                ->whereBetween('pe.recibido_at', [$inicioMes, $finMes])
                ->count();

            $enviadosMes = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->where('e.area_role', $miRolArea)
                ->where('pe.enviado', true)
                ->whereBetween('pe.enviado_at', [$inicioMes, $finMes])
                ->count();

            $enBandeja = DB::table('procesos')
                ->where('area_actual_role', $miRolArea)
                ->where('estado', 'EN_CURSO')
                ->count();

            $rechazadosMes = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->join('procesos as p', 'p.id', '=', 'pe.proceso_id')
                ->where('e.area_role', $miRolArea)
                ->where('p.estado', 'RECHAZADO')
                ->whereBetween('p.updated_at', [$inicioMes, $finMes])
                ->count();

            return [
                'tipo' => $miRolArea,
                'mes'  => now()->translatedFormat('F Y'),
                'tarjetas' => [
                    ['icono' => '📥', 'valor' => $recibidosMes, 'label' => 'Recibidos este mes',   'color' => 'blue'],
                    ['icono' => '📤', 'valor' => $enviadosMes,  'label' => 'Enviados este mes',    'color' => 'green'],
                    ['icono' => '🔄', 'valor' => $enBandeja,    'label' => 'En tu bandeja ahora',  'color' => 'yellow'],
                    ['icono' => '❌', 'valor' => $rechazadosMes,'label' => 'Rechazados este mes',  'color' => 'red'],
                ],
            ];
        }

        return ['tipo' => 'generic', 'mes' => now()->translatedFormat('F Y'), 'tarjetas' => []];
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Métodos públicos utilizados por reporte y endpoints de estadísticas
    // ═══════════════════════════════════════════════════════════════════════════

    public function indicadoresGenerales()
    {
        $hoy = now();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        return [
            'total_procesos'         => Proceso::count(),
            'procesos_activos'       => Proceso::whereNotIn('estado', ['completado', 'cerrado', 'rechazado'])->count(),
            'procesos_finalizados'   => Proceso::whereIn('estado', ['completado', 'cerrado'])->count(),
            'procesos_rechazados'    => Proceso::where('estado', 'rechazado')->count(),
            'procesos_mes'           => Proceso::whereBetween('created_at', [$inicioMes, $finMes])->count(),
            'finalizados_mes'        => Proceso::whereIn('estado', ['completado', 'cerrado'])
                ->whereBetween('updated_at', [$inicioMes, $finMes])->count(),
            'alertas_activas'        => Alerta::where('leida', false)->count(),
            'alertas_alta_prioridad' => Alerta::where('leida', false)->where('prioridad', 'alta')->count(),
            'documentos_totales'     => ProcesoEtapaArchivo::count(),
            'documentos_pendientes'  => ProcesoEtapaArchivo::where('estado', 'pendiente')->count(),
            'documentos_rechazados'  => ProcesoEtapaArchivo::where('estado', 'rechazado')->count(),
            'por_modalidad'          => DB::table('procesos')
                ->join('workflows', 'procesos.workflow_id', '=', 'workflows.id')
                ->select('workflows.nombre', 'workflows.codigo', DB::raw('count(*) as total'))
                ->groupBy('workflows.id', 'workflows.nombre', 'workflows.codigo')
                ->get(),
            'tendencia'              => $this->obtenerTendenciaUltimosSeisMeses(),
        ];
    }

    public function estadisticasPorArea()
    {
        $areas = ['unidad_solicitante', 'planeacion', 'hacienda', 'juridica', 'secop'];
        $estadisticas = [];
        foreach ($areas as $area) {
            $estadisticas[$area] = [
                'total'                => Proceso::where('area_actual_role', $area)->count(),
                'alertas'              => Alerta::where('area_responsable', $area)->where('leida', false)->count(),
                'documentos_pendientes'=> ProcesoEtapaArchivo::whereHas('proceso', fn($q) => $q->where('area_actual_role', $area))
                    ->where('estado', 'pendiente')->count(),
            ];
        }
        return $estadisticas;
    }

    public function indicadoresPorEtapa()
    {
        $distribucion = DB::table('procesos')
            ->join('etapas', 'procesos.etapa_actual_id', '=', 'etapas.id')
            ->join('workflows', 'procesos.workflow_id', '=', 'workflows.id')
            ->select('etapas.nombre as etapa', 'workflows.nombre as workflow', 'etapas.area_role', DB::raw('count(*) as total'))
            ->whereNotIn('procesos.estado', ['completado', 'cerrado', 'rechazado'])
            ->groupBy('etapas.id', 'etapas.nombre', 'workflows.nombre', 'etapas.area_role')
            ->orderBy('total', 'desc')
            ->get();

        return ['distribucion' => $distribucion, 'por_fase' => collect()];
    }

    public function indicadoresAlertasRiesgos()
    {
        return [
            'procesos_con_retraso'    => Alerta::where('tipo', 'tiempo_excedido')->where('leida', false)->distinct('proceso_id')->count('proceso_id'),
            'certificados_por_vencer' => Alerta::where('tipo', 'certificado_por_vencer')->where('leida', false)->count(),
            'documentos_rechazados'   => Alerta::where('tipo', 'documento_rechazado')->where('leida', false)->count(),
            'procesos_sin_actividad'  => Alerta::where('tipo', 'sin_actividad')->where('leida', false)->distinct('proceso_id')->count('proceso_id'),
            'alertas_por_prioridad'   => Alerta::where('leida', false)->select('prioridad', DB::raw('count(*) as total'))
                ->groupBy('prioridad')->pluck('total', 'prioridad')->toArray(),
        ];
    }

    public function indicadoresEficiencia()
    {
        $tresMonthsAgo = now()->subMonths(3);
        $procesosFinalizados = Proceso::whereIn('estado', ['completado', 'cerrado'])->where('updated_at', '>', $tresMonthsAgo)->get();
        $tiempoTotal = 0;
        $count = 0;
        foreach ($procesosFinalizados as $p) {
            $tiempoTotal += $p->created_at->diffInDays($p->updated_at);
            $count++;
        }

        $driver = DB::connection()->getDriverName();
        $avgExpr = $driver === 'sqlite'
            ? 'AVG(julianday(procesos.updated_at) - julianday(procesos.created_at))'
            : 'AVG(TIMESTAMPDIFF(DAY, procesos.created_at, procesos.updated_at))';

        return [
            'promedio_general_dias'      => $count > 0 ? round($tiempoTotal / $count, 2) : 0,
            'procesos_finalizados_3meses'=> $count,
            'por_modalidad'              => DB::table('procesos')
                ->join('workflows', 'procesos.workflow_id', '=', 'workflows.id')
                ->select('workflows.nombre', DB::raw($avgExpr . ' as promedio_dias'))
                ->whereIn('procesos.estado', ['completado', 'cerrado'])
                ->where('procesos.updated_at', '>', $tresMonthsAgo)
                ->groupBy('workflows.id', 'workflows.nombre')
                ->get(),
        ];
    }

    public function buscar(Request $request)
    {
        $termino = $request->input('q');
        $resultados = Proceso::with(['workflow', 'etapaActual'])
            ->where(fn($q) => $q->where('codigo', 'like', "%{$termino}%")
                ->orWhere('objeto', 'like', "%{$termino}%")
                ->orWhere('descripcion', 'like', "%{$termino}%"))
            ->limit(20)
            ->get();
        return response()->json($resultados);
    }

    public function reporte(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth());
        $fechaFin    = $request->input('fecha_fin',    now()->endOfMonth());

        $datos = [
            'periodo'               => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
            'indicadores'           => $this->indicadoresGenerales(),
            'por_area'              => $this->estadisticasPorArea(),
            'por_etapa'             => $this->indicadoresPorEtapa(),
            'alertas_riesgos'       => $this->indicadoresAlertasRiesgos(),
            'eficiencia'            => $this->indicadoresEficiencia(),
        ];

        if ($request->wantsJson()) {
            return response()->json($datos);
        }

        return view('dashboard.reporte', compact('datos'));
    }

    private function obtenerTendenciaUltimosSeisMeses()
    {
        $tendencia = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $ini = $mes->copy()->startOfMonth();
            $fin = $mes->copy()->endOfMonth();
            $tendencia[] = [
                'mes'         => $mes->format('M Y'),
                'creados'     => Proceso::whereBetween('created_at', [$ini, $fin])->count(),
                'finalizados' => Proceso::whereIn('estado', ['completado', 'cerrado'])->whereBetween('updated_at', [$ini, $fin])->count(),
            ];
        }
        return $tendencia;
    }
}
