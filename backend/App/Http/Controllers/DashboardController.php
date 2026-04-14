<?php
/**
 * Archivo: backend/App/Http/Controllers/DashboardController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

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
    public function index(Request $request)
    {
        $user = auth()->user();
        $user->loadMissing('roles');
        $roleNames = $user->roles->pluck('name')->all();

        $filtroPeriodo = $request->input('periodo', 'mes');
        $filtroPeriodo = $filtroPeriodo === 'anio' ? 'anio' : 'mes';
        $filtroAnio = (int) $request->input('anio', now()->year);
        $filtroMes = (int) $request->input('mes', now()->month);

        $filtroAnio = $filtroAnio > 0 ? $filtroAnio : (int) now()->year;
        $filtroMes = ($filtroMes >= 1 && $filtroMes <= 12) ? $filtroMes : (int) now()->month;

        if ($filtroPeriodo === 'anio') {
            $inicioPeriodo = Carbon::create($filtroAnio, 1, 1)->startOfYear();
            $finPeriodo = Carbon::create($filtroAnio, 12, 31)->endOfYear();
            $periodoLabel = (string) $filtroAnio;
        } else {
            $inicioPeriodo = Carbon::create($filtroAnio, $filtroMes, 1)->startOfMonth();
            $finPeriodo = Carbon::create($filtroAnio, $filtroMes, 1)->endOfMonth();
            $periodoLabel = $inicioPeriodo->translatedFormat('F Y');
        }
        
        // Obtener el alcance del dashboard segun todos los roles del usuario
        $dashboardScope = $this->resolveDashboardScope($user, $user->roles, $roleNames);
        
        // Si el alcance es global, secretaria o unidad, usar el dashboard ejecutivo
        if (in_array($dashboardScope, ['global', 'secretaria', 'unidad'], true)) {
            return $this->dashboardEjecutivo($user, $dashboardScope);
        }

        // ── Roles de área específica (solicitudes paralelas Etapa 1) ──────────
        $rolesDocumentos = ['compras', 'talento_humano', 'rentas', 'contabilidad', 'inversiones_publicas', 'presupuesto', 'radicacion'];
        $userRolesDoc = collect($rolesDocumentos)->filter(fn($r) => $user->hasRole($r));

        // Cargar solicitudes de documentos pendientes/subidos para estos roles
        $solicitudesPendientes = collect();
        $solicitudesPendientesArea = collect();
        $kpisDocumentos = null;
        if ($userRolesDoc->isNotEmpty()) {
            $rolesDoc = $userRolesDoc->values()->toArray();

            $solicitudesPendientes = DB::table('proceso_documentos_solicitados as pds')
                ->join('procesos', 'procesos.id', '=', 'pds.proceso_id')
                ->leftJoin('etapas', 'etapas.id', '=', 'procesos.etapa_actual_id')
                ->whereIn('pds.area_responsable_rol', $rolesDoc)
                ->where('pds.subido_por', $user->id)
                ->whereBetween('pds.subido_at', [$inicioPeriodo, $finPeriodo])
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

            $solicitudesPendientesArea = DB::table('proceso_documentos_solicitados as pds')
                ->join('procesos', 'procesos.id', '=', 'pds.proceso_id')
                ->leftJoin('etapas', 'etapas.id', '=', 'procesos.etapa_actual_id')
                ->whereIn('pds.area_responsable_rol', $rolesDoc)
                ->whereBetween('pds.created_at', [$inicioPeriodo, $finPeriodo])
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
                ->havingRaw('SUM(CASE WHEN pds.estado = "pendiente" AND pds.puede_subir = 1 THEN 1 ELSE 0 END) > 0')
                ->orderByDesc('procesos.id')
                ->get();

            $kpisDocumentos = DB::table('proceso_documentos_solicitados as pds')
                ->leftJoin('proceso_etapa_archivos as pea', 'pea.id', '=', 'pds.archivo_id')
                ->whereIn('pds.area_responsable_rol', $rolesDoc)
                ->where('pds.subido_por', $user->id)
                ->whereBetween('pds.subido_at', [$inicioPeriodo, $finPeriodo])
                ->select(
                    DB::raw('COALESCE(SUM(CASE WHEN pea.estado = "aprobado" THEN 1 ELSE 0 END), 0) as aprobados'),
                    DB::raw('COALESCE(SUM(CASE WHEN pea.estado = "rechazado" THEN 1 ELSE 0 END), 0) as rechazados')
                )
                ->first();
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

        $metricas = $this->computeMetricas($user, $userRolesDoc, $inicioPeriodo, $finPeriodo, $filtroPeriodo, $periodoLabel);

        return view('dashboard', compact(
            'enCurso',
            'finalizados',
            'solicitudesPendientes',
            'solicitudesPendientesArea',
            'metricas',
            'kpisDocumentos',
            'filtroPeriodo',
            'filtroMes',
            'filtroAnio'
        ));
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
        $kpiMesLabel = 'Creados este mes';
        $kpiMesSub = ucfirst(now()->translatedFormat('F'));
        $kpiFinalizadosSub = 'Completados';

        $areaOperativa = $this->obtenerAreaOperativaDashboard($user);
        $esOperativo = !in_array($scope, ['global'], true)
            && !$user->hasRole('secretario')
            && !$user->hasRole('admin_secretaria')
            && !$user->hasRole('jefe_unidad')
            && !empty($areaOperativa);

        if ($esOperativo) {
            $gestionadosQuery = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->join('procesos as p', 'p.id', '=', 'pe.proceso_id')
                ->where('e.area_role', $areaOperativa)
                ->when($secId, fn($q) => $q->where('p.secretaria_origen_id', $secId))
                ->when($uniId, fn($q) => $q->where('p.unidad_origen_id', $uniId));

            $totalProcesos = (clone $gestionadosQuery)
                ->distinct('p.id')
                ->count('p.id');

            $enCurso = DB::table('procesos as p')
                ->where('p.area_actual_role', $areaOperativa)
                ->where('p.estado', 'EN_CURSO')
                ->when($secId, fn($q) => $q->where('p.secretaria_origen_id', $secId))
                ->when($uniId, fn($q) => $q->where('p.unidad_origen_id', $uniId))
                ->count();

            $finalizados = (clone $gestionadosQuery)
                ->where('pe.enviado', true)
                ->distinct('p.id')
                ->count('p.id');

            $rechazados = DB::table('procesos as p')
                ->join('proceso_etapas as pe', 'pe.proceso_id', '=', 'p.id')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->where('e.area_role', $areaOperativa)
                ->where('p.estado', 'RECHAZADO')
                ->when($secId, fn($q) => $q->where('p.secretaria_origen_id', $secId))
                ->when($uniId, fn($q) => $q->where('p.unidad_origen_id', $uniId))
                ->distinct('p.id')
                ->count('p.id');

            $creadosMes = (clone $gestionadosQuery)
                ->where('pe.enviado', true)
                ->whereBetween('pe.enviado_at', [$inicioMes, $finMes])
                ->distinct('p.id')
                ->count('p.id');

            $finalizadosMes = $creadosMes;
            $kpiMesLabel = 'Enviados este mes';
            $kpiFinalizadosSub = 'Atendidos y enviados';
        }

        // ── Alertas ─────────────────────────────────────────────────────────
        $alertasBaseQuery = $this->queryAlertasDashboard($user, $scope, $scoped);
        $alertasAltas = (clone $alertasBaseQuery)->where('prioridad', 'alta')->count();
        $alertasTotal = (clone $alertasBaseQuery)->count();

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
                'alertas'=> (clone $alertasBaseQuery)->where('area_responsable', $role)->count(),
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
            'procesos_con_retraso'    => (clone $alertasBaseQuery)
                ->where('tipo', 'tiempo_excedido')->distinct('proceso_id')->count('proceso_id'),
            'documentos_rechazados'   => (clone $alertasBaseQuery)
                ->where('tipo', 'documento_rechazado')->count(),
            'procesos_sin_actividad'  => (clone $alertasBaseQuery)
                ->where('tipo', 'sin_actividad')->distinct('proceso_id')->count('proceso_id'),
            'certificados_por_vencer' => (clone $alertasBaseQuery)
                ->where('tipo', 'certificado_por_vencer')->count(),
        ];

        // ── Señales adicionales para Jefes de Unidad ───────────────────────
        $documentosEstado = collect();
        $etapasActivas = collect();

        if ($scope === 'unidad' && $uniId) {
            $documentosEstado = DB::table('proceso_etapa_archivos as pea')
                ->join('procesos as p', 'p.id', '=', 'pea.proceso_id')
                ->where('p.unidad_origen_id', $uniId)
                ->select('pea.estado', DB::raw('count(*) as total'))
                ->groupBy('pea.estado')
                ->orderBy('pea.estado')
                ->get();

            $etapasActivas = $scoped()
                ->join('etapas as e', 'e.id', '=', 'procesos.etapa_actual_id')
                ->select('e.nombre', 'e.area_role', DB::raw('count(*) as total'))
                ->groupBy('e.id', 'e.nombre', 'e.area_role')
                ->orderByDesc('total')
                ->limit(6)
                ->get();
        }

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
            'kpiMesLabel', 'kpiMesSub', 'kpiFinalizadosSub',
            'alertasAltas', 'alertasTotal',
            'tendencia', 'porArea', 'porEstadoRaw', 'porModalidad',
            'alertasRiesgos', 'procesosRecientes',
            'listaLateral', 'listaLateralTipo',
            'documentosEstado', 'etapasActivas'
        ));
    }

    private function obtenerAreaOperativaDashboard($user): ?string
    {
        if ($user->hasRole('descentralizacion') || $user->hasRole('planeacion')) {
            return 'planeacion';
        }

        foreach (['hacienda', 'juridica', 'secop', 'unidad_solicitante'] as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }

    private function resolveDashboardScope($user, $roles, array $roleNames): string
    {
        $roleNames = array_values(array_unique($roleNames));

        // Jerarquía fija para evitar depender de seeders/dashboard_scope en otros equipos.
        if (!empty(array_intersect($roleNames, ['admin', 'admin_general', 'gobernador']))) {
            return 'global';
        }

        if (!empty(array_intersect($roleNames, ['secretario', 'admin_secretaria']))) {
            return !empty($user->secretaria_id) ? 'secretaria' : 'propios';
        }

        if (in_array('jefe_unidad', $roleNames, true)) {
            if (!empty($user->unidad_id)) {
                return 'unidad';
            }

            if (!empty($user->secretaria_id)) {
                return 'secretaria';
            }

            return 'propios';
        }

        // Requerimiento operativo: el resto de usuarios usa la vista tipo Descentralización.
        $rolesResto = [
            'unidad_solicitante',
            'abogado_unidad',
            'planeacion',
            'descentralizacion',
            'hacienda',
            'juridica',
            'secop',
            'compras',
            'talento_humano',
            'rentas',
            'contabilidad',
            'inversiones_publicas',
            'presupuesto',
            'radicacion',
            'profesional_contratacion',
            'revisor_juridico',
            'consulta',
        ];

        if (!empty(array_intersect($roleNames, $rolesResto))) {
            if (!empty($user->secretaria_id)) {
                return 'secretaria';
            }

            if (!empty($user->unidad_id)) {
                return 'unidad';
            }

            return 'propios';
        }

        // Fallback para roles no contemplados explícitamente.
        $priority = ['propios' => 0, 'unidad' => 1, 'secretaria' => 2, 'global' => 3];
        $scope = 'propios';

        foreach ($roles as $role) {
            $roleScope = $role->dashboard_scope ?? null;
            if (!is_string($roleScope) || !isset($priority[$roleScope])) {
                continue;
            }

            if ($priority[$roleScope] > $priority[$scope]) {
                $scope = $roleScope;
            }
        }

        if ($scope === 'secretaria' && empty($user->secretaria_id)) {
            return 'propios';
        }

        if ($scope === 'unidad' && empty($user->unidad_id)) {
            return 'propios';
        }

        return $scope;
    }

    private function queryAlertasDashboard($user, string $scope, callable $scoped)
    {
        $query = Alerta::query()->where('leida', false);

        if ($scope === 'global') {
            return $query;
        }

        $areaUsuario = $this->obtenerAreaUsuarioParaAlertas($user);

        return $query->where(function ($q) use ($user, $areaUsuario, $scoped) {
            $q->whereIn('proceso_id', $scoped()->select('id'))
                ->orWhere('user_id', $user->id);

            if ($areaUsuario) {
                $q->orWhere(function ($sub) use ($areaUsuario) {
                    $sub->whereNull('user_id')
                        ->where('area_responsable', $areaUsuario);
                });
            }
        });
    }

    private function obtenerAreaUsuarioParaAlertas($user): ?string
    {
        if ($user->hasRole('rentas')) return 'rentas';
        if ($user->hasRole('contabilidad')) return 'contabilidad';
        if ($user->hasRole('presupuesto')) return 'presupuesto';
        if ($user->hasRole('inversiones_publicas')) return 'inversiones_publicas';
        if ($user->hasRole('radicacion')) return 'radicacion';
        if ($user->hasRole('compras')) return 'compras';
        if ($user->hasRole('talento_humano')) return 'talento_humano';
        if ($user->hasRole('descentralizacion')) return 'planeacion';
        if ($user->hasRole('planeacion')) return 'planeacion';
        if ($user->hasRole('hacienda')) return 'hacienda';
        if ($user->hasRole('juridica')) return 'juridica';
        if ($user->hasRole('secop')) return 'secop';
        if ($user->hasRole('unidad_solicitante')) return 'unidad_solicitante';

        return null;
    }

    /**
     * Métricas mensuales personalizadas según el rol del usuario (roles de área)
     */
    private function computeMetricas($user, $userRolesDoc, Carbon $inicioPeriodo, Carbon $finPeriodo, string $filtroPeriodo, string $periodoLabel)
    {
        $periodoTexto = $filtroPeriodo === 'anio' ? 'este año' : 'este mes';

        // ── Áreas de documentos (compras, talento_humano, rentas, etc.) ───────
        if ($userRolesDoc->isNotEmpty()) {
            $roles = $userRolesDoc->values()->toArray();

            $asignadosPeriodo = DB::table('proceso_documentos_solicitados as pds')
                ->join('procesos', 'procesos.id', '=', 'pds.proceso_id')
                ->whereIn('pds.area_responsable_rol', $roles)
                ->whereBetween('pds.created_at', [$inicioPeriodo, $finPeriodo])
                ->distinct('pds.proceso_id')->count('pds.proceso_id');

            $subidosPeriodo = DB::table('proceso_documentos_solicitados')
                ->whereIn('area_responsable_rol', $roles)
                ->where('estado', 'subido')
                ->whereBetween('subido_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $pendientesPeriodo = DB::table('proceso_documentos_solicitados')
                ->whereIn('area_responsable_rol', $roles)
                ->where('estado', 'pendiente')
                ->whereBetween('created_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $totalPeriodo = DB::table('proceso_documentos_solicitados')
                ->whereIn('area_responsable_rol', $roles)
                ->whereBetween('created_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            return [
                'tipo' => 'area_doc',
                'mes'  => $periodoLabel,
                'tarjetas' => [
                    ['icono' => '📂', 'valor' => $asignadosPeriodo,  'label' => "Procesos asignados {$periodoTexto}", 'color' => 'blue'],
                    ['icono' => '✅', 'valor' => $subidosPeriodo,    'label' => "Documentos subidos {$periodoTexto}",  'color' => 'green'],
                    ['icono' => '⏳', 'valor' => $pendientesPeriodo, 'label' => "Documentos pendientes {$periodoTexto}", 'color' => 'yellow'],
                    ['icono' => '📊', 'valor' => $totalPeriodo,      'label' => "Total documentos {$periodoTexto}",    'color' => 'gray'],
                ],
            ];
        }

        // ── Unidad Solicitante ─────────────────────────────────────────────────
        if ($user->hasRole('unidad_solicitante')) {
            $creadosPeriodo = DB::table('procesos')
                ->where('created_by', $user->id)
                ->whereBetween('created_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $finalizadosPeriodo = DB::table('procesos')
                ->where('created_by', $user->id)
                ->whereIn('estado', ['FINALIZADO', 'completado', 'cerrado'])
                ->whereBetween('updated_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $rechazadosPeriodo = DB::table('procesos')
                ->where('created_by', $user->id)
                ->where('estado', 'RECHAZADO')
                ->whereBetween('updated_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $enCursoPeriodo = DB::table('procesos')
                ->where('created_by', $user->id)
                ->where('estado', 'EN_CURSO')
                ->whereBetween('updated_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            return [
                'tipo' => 'unidad_solicitante',
                'mes'  => $periodoLabel,
                'tarjetas' => [
                    ['icono' => '📋', 'valor' => $creadosPeriodo,   'label' => "Solicitudes creadas {$periodoTexto}",  'color' => 'blue'],
                    ['icono' => '🔄', 'valor' => $enCursoPeriodo,   'label' => "Procesos activos {$periodoTexto}",     'color' => 'yellow'],
                    ['icono' => '✅', 'valor' => $finalizadosPeriodo, 'label' => "Finalizados {$periodoTexto}",        'color' => 'green'],
                    ['icono' => '❌', 'valor' => $rechazadosPeriodo,  'label' => "Rechazados {$periodoTexto}",         'color' => 'red'],
                ],
            ];
        }

        // ── Planeación ────────────────────────────────────────────────────────
        if ($user->hasRole('planeacion')) {
            $recibidosPeriodo = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->where('e.area_role', 'planeacion')
                ->where('pe.recibido', true)
                ->whereBetween('pe.recibido_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $enviadosPeriodo = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->where('e.area_role', 'planeacion')
                ->where('pe.enviado', true)
                ->whereBetween('pe.enviado_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $rechazadosPeriodo = DB::table('procesos')
                ->where('estado', 'RECHAZADO')
                ->whereBetween('updated_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $enPlaneacionPeriodo = DB::table('procesos')
                ->where('area_actual_role', 'planeacion')
                ->where('estado', 'EN_CURSO')
                ->whereBetween('updated_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            return [
                'tipo' => 'planeacion',
                'mes'  => $periodoLabel,
                'tarjetas' => [
                    ['icono' => '📥', 'valor' => $recibidosPeriodo,  'label' => "Recibidos {$periodoTexto}",           'color' => 'blue'],
                    ['icono' => '📤', 'valor' => $enviadosPeriodo,   'label' => "Enviados a áreas {$periodoTexto}",    'color' => 'green'],
                    ['icono' => '🔄', 'valor' => $enPlaneacionPeriodo, 'label' => "En tu bandeja {$periodoTexto}",      'color' => 'yellow'],
                    ['icono' => '❌', 'valor' => $rechazadosPeriodo, 'label' => "Rechazados {$periodoTexto}",          'color' => 'red'],
                ],
            ];
        }

        // ── Hacienda / Jurídica / SECOP ───────────────────────────────────────
        $rolesArea = ['hacienda', 'juridica', 'secop'];
        $miRolArea = collect($rolesArea)->first(fn($r) => $user->hasRole($r));
        if ($miRolArea) {
            $recibidosPeriodo = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->where('e.area_role', $miRolArea)
                ->where('pe.recibido', true)
                ->whereBetween('pe.recibido_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $enviadosPeriodo = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->where('e.area_role', $miRolArea)
                ->where('pe.enviado', true)
                ->whereBetween('pe.enviado_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $enBandejaPeriodo = DB::table('procesos')
                ->where('area_actual_role', $miRolArea)
                ->where('estado', 'EN_CURSO')
                ->whereBetween('updated_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            $rechazadosPeriodo = DB::table('proceso_etapas as pe')
                ->join('etapas as e', 'e.id', '=', 'pe.etapa_id')
                ->join('procesos as p', 'p.id', '=', 'pe.proceso_id')
                ->where('e.area_role', $miRolArea)
                ->where('p.estado', 'RECHAZADO')
                ->whereBetween('p.updated_at', [$inicioPeriodo, $finPeriodo])
                ->count();

            return [
                'tipo' => $miRolArea,
                'mes'  => $periodoLabel,
                'tarjetas' => [
                    ['icono' => '📥', 'valor' => $recibidosPeriodo, 'label' => "Recibidos {$periodoTexto}",   'color' => 'blue'],
                    ['icono' => '📤', 'valor' => $enviadosPeriodo,  'label' => "Enviados {$periodoTexto}",    'color' => 'green'],
                    ['icono' => '🔄', 'valor' => $enBandejaPeriodo, 'label' => "En tu bandeja {$periodoTexto}",  'color' => 'yellow'],
                    ['icono' => '❌', 'valor' => $rechazadosPeriodo,'label' => "Rechazados {$periodoTexto}",  'color' => 'red'],
                ],
            ];
        }

        return ['tipo' => 'generic', 'mes' => $periodoLabel, 'tarjetas' => []];
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

