{{-- Archivo: backend/resources/views/backend/dashboard/admin.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div>
                <h1 class="text-base font-black text-gray-900 leading-none">Panel de Control</h1>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $scopeNombre }} &mdash; {{ now()->translatedFormat('F Y') }}</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
                @can('procesos.crear')
                <a href="{{ route('procesos.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold shadow-sm transition-all hover:shadow-md hover:opacity-95"
                   style="background:linear-gradient(135deg,#15803d,#14532d)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Nueva solicitud
                </a>
                @endcan
                <a href="{{ route('procesos.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-gray-600 text-sm font-medium bg-white border hover:bg-gray-50"
                   style="border-color:#e2e8f0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Ver todos
                </a>
                @if(($dashboardPreview['can_preview'] ?? false) === true)
                <form method="GET" action="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-2 py-1.5 rounded-xl border bg-white" style="border-color:#e2e8f0">
                    <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Ver como</span>
                    <select name="as_user" class="rounded-lg border text-xs px-2 py-1 max-w-[16rem]" style="border-color:#cbd5e1">
                        @foreach(($dashboardPreview['users'] ?? collect()) as $previewUser)
                        <option value="{{ $previewUser->id }}" @selected((int)($dashboardPreview['selected_user_id'] ?? 0) === (int)$previewUser->id)>
                            {{ $previewUser->name }} ({{ $previewUser->email }})
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold text-white" style="background:#1d4ed8">Cargar</button>
                    @if(($dashboardPreview['is_preview'] ?? false) === true)
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold text-gray-600 border" style="border-color:#d1d5db">Salir</a>
                    @endif
                </form>
                @endif
            </div>
        </div>
    </x-slot>

    @php
    $estadoConfig = [
        'EN_CURSO'   => ['label'=>'En curso',    'color'=>'#2563eb','bg'=>'#eff6ff'],
        'FINALIZADO' => ['label'=>'Finalizado',  'color'=>'#15803d','bg'=>'#f0fdf4'],
        'completado' => ['label'=>'Completado',  'color'=>'#15803d','bg'=>'#f0fdf4'],
        'cerrado'    => ['label'=>'Cerrado',     'color'=>'#0891b2','bg'=>'#ecfeff'],
        'RECHAZADO'  => ['label'=>'Rechazado',   'color'=>'#dc2626','bg'=>'#fef2f2'],
    ];
    $areaRoutes = [
        'unidad_solicitante' => '/unidad',
        'planeacion'         => '/planeacion',
        'hacienda'           => '/hacienda',
        'juridica'           => '/juridica',
        'secop'              => '/secop',
    ];

    $docsEstadoMap = [
        'pendiente' => ['label' => 'Pendientes', 'color' => '#f59e0b'],
        'aprobado'  => ['label' => 'Aprobados',  'color' => '#16a34a'],
        'rechazado' => ['label' => 'Rechazados', 'color' => '#dc2626'],
        'vencido'   => ['label' => 'Vencidos',   'color' => '#7c3aed'],
    ];
    $docsEstadoCounts = collect($documentosEstado ?? collect())
        ->mapWithKeys(fn($row) => [(string) $row->estado => (int) $row->total]);
    $docsEstadoLabels = [];
    $docsEstadoValues = [];
    $docsEstadoColors = [];
    foreach ($docsEstadoMap as $key => $info) {
        $docsEstadoLabels[] = $info['label'];
        $docsEstadoValues[] = $docsEstadoCounts[$key] ?? 0;
        $docsEstadoColors[] = $info['color'];
    }
    @endphp

    <link rel="stylesheet" href="{{ asset('vendor/gridstack/gridstack.min.css') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800;900&family=DM+Mono:wght@400;500&display=swap');

        .dash-root * { font-family: 'DM Sans'; }
        .dash-root code, .dash-root .mono { font-family: 'DM Mono'; }

        .kpi-tile {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
            padding: 6px 8px 6px 10px;
            display: flex;
            align-items: center;
            gap: 7px;
            position: relative;
            overflow: hidden;
            transition: box-shadow .18s, transform .18s;
        }
        .kpi-tile:hover { box-shadow: 0 3px 12px 0 rgba(0,0,0,.07); transform: translateY(-1px); }
        .kpi-tile .kpi-accent {
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 3px; border-radius: 3px 0 0 3px;
        }
        .kpi-tile .kpi-icon {
            width: 24px; height: 24px; border-radius: 6px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .kpi-tile .kpi-val  { font-size: 16px; font-weight: 900; line-height: 1; letter-spacing: -0.5px; }
        .kpi-tile .kpi-label{ font-size: 9px; font-weight: 700; color: #374151; margin-top: 1px; white-space: nowrap; }
        .kpi-tile .kpi-sub  { font-size: 8px; color: #9ca3af; margin-top: 1px; }

        .chart-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .chart-card-header {
            padding: 5px 8px 4px;
            border-bottom: 1px solid #f8fafc;
            display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
        }
        .chart-card-header h3 { font-size: 10px; font-weight: 800; color: #1e293b; letter-spacing: -0.2px; margin:0; }
        .chart-card-header p  { font-size: 8.5px; color: #94a3b8; margin-top: 1px; }
        .badge-pill { font-size: 9px; font-weight: 700; padding: 2px 7px; border-radius: 999px; }

        .pipeline-step {
            display: flex; flex-direction: column; align-items: center;
            flex: 1; position: relative;
        }
        .pipeline-step:not(:last-child)::after {
            content: ''; position: absolute;
            top: 12px; left: calc(50% + 12px); right: calc(-50% + 12px);
            height: 2px; background: #e2e8f0;
        }
        .pipeline-dot {
            width: 24px; height: 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 9px; font-weight: 800; color: #fff; position: relative; z-index: 1;
        }

        .alert-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 5px 8px; border-radius: 8px; margin-bottom: 3px;
        }
        .alert-row:last-child { margin-bottom: 0; }

        .area-bar-row { margin-bottom: 5px; }
        .area-bar-row:last-child { margin-bottom: 0; }

        .proc-row {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 12px; border-bottom: 1px solid #f8fafc;
            transition: background .12s;
        }
        .proc-row:hover { background: #f8fafc; }
        .proc-row:last-child { border-bottom: none; }

        .canvas-toolbar-btn {
            border: 1px solid #dbe3ec;
            background: #ffffff;
            color: #475569;
            border-radius: 9px;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 9px;
            transition: all .14s ease;
        }
        .canvas-toolbar-btn:hover { background: #f8fafc; color: #0f172a; }
        .canvas-toolbar-btn.active {
            background: linear-gradient(135deg,#166534,#14532d);
            border-color: #14532d;
            color: #ffffff;
        }

        .dash-top-canvas { min-height: 0; }
        .dash-graph-canvas { min-height: 0; }
        .dash-bottom-canvas { min-height: 0; }

        .dash-top-canvas .grid-stack-item-content,
        .dash-graph-canvas .grid-stack-item-content,
        .dash-bottom-canvas .grid-stack-item-content {
            inset: 0 !important;
            background: transparent;
            border: none;
            overflow: visible;
        }
        .dash-grid-widget,
        .dash-grid-widget .chart-card,
        .dash-graph-widget,
        .dash-graph-widget .chart-card {
            height: 100%;
        }
        .dash-grid-widget {
            padding: 1px;
        }
        .dash-kpi-widget {
            height: 100%;
        }
        .dash-kpi-widget .kpi-tile {
            height: 100%;
        }
        .dash-graph-widget .chart-card {
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
        }
        .graph-editing .grid-stack-item-content {
            cursor: move;
        }
        .graph-editing .chart-card-header {
            cursor: move;
            user-select: none;
            background: linear-gradient(180deg,#fcfdff,#f8fbff);
        }
    </style>

    <div class="dash-root p-1 space-y-0.5" style="background:#f4f6f9;min-height:calc(100vh - 72px)">

        @if(($dashboardPreview['is_preview'] ?? false) === true)
        <div class="mx-0.5 mb-1 px-3 py-2 rounded-xl border" style="background:#eff6ff;border-color:#bfdbfe;color:#1e3a8a">
            <p class="text-[11px] font-semibold">
                Vista simulada: {{ $dashboardPreview['target_name'] ?? 'Usuario' }} ({{ $dashboardPreview['target_email'] ?? '' }})
            </p>
            <p class="text-[10px]" style="color:#475569">
                Administrador activo: {{ $dashboardPreview['actor_name'] ?? auth()->user()->name }}
            </p>
        </div>
        @endif

        {{-- ═══ CANVAS PERSONALIZABLE DEL DASHBOARD ═══ --}}
        <div class="flex items-center justify-between px-0.5 pt-0.5">
            <div>
                <p style="font-size:10px;font-weight:800;color:#64748b;letter-spacing:.03em;text-transform:uppercase">Canvas del dashboard</p>
                <p style="font-size:9px;color:#94a3b8">Arrastra y cambia tamaño de KPIs, gráficas y tablas</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span id="graphEditState" style="font-size:9px;font-weight:700;color:#94a3b8">Bloqueado</span>
                <button id="graphEditToggle" type="button" class="canvas-toolbar-btn">Organizar</button>
                <button id="graphLayoutReset" type="button" class="canvas-toolbar-btn">Restablecer</button>
            </div>
        </div>

        {{-- ═══ FILA 1: KPI COMPACTOS + SECRETARÍAS ═══ --}}
        @php
        $kpis = [
            ['id'=>'widget-kpi-total-procesos', 'v'=>$totalProcesos,   'l'=>'Total procesos',   's'=>'Registrados',      'c'=>'#2563eb','bg'=>'#eff6ff',
             'ic'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['id'=>'widget-kpi-en-curso',      'v'=>$enCurso,         'l'=>'En curso',         's'=>'Procesos activos', 'c'=>'#0891b2','bg'=>'#ecfeff',
             'ic'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['id'=>'widget-kpi-finalizados',   'v'=>$finalizados,     'l'=>'Finalizados',      's'=>($kpiFinalizadosSub ?? 'Completados'),      'c'=>'#15803d','bg'=>'#f0fdf4',
             'ic'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['id'=>'widget-kpi-rechazados',    'v'=>$rechazados,      'l'=>'Rechazados',       's'=>'Total histórico',  'c'=>'#dc2626','bg'=>'#fef2f2',
             'ic'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['id'=>'widget-kpi-creados-mes',   'v'=>$creadosMes,      'l'=>($kpiMesLabel ?? 'Creados este mes'), 's'=>($kpiMesSub ?? ucfirst(now()->translatedFormat('F'))), 'c'=>'#7c3aed','bg'=>'#faf5ff',
             'ic'=>'M12 4v16m8-8H4'],
              // Conservamos el ID histórico para no romper layouts/drag&drop guardados en localStorage.
            ['id'=>'widget-kpi-alertas-criticas','v'=>$alertasAltas,  'l'=>'Alertas altas', 's'=>'Alta prioridad',   'c'=>'#d97706','bg'=>'#fffbeb',
             'ic'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ];
        @endphp

        <div id="dashboardTopCanvas" class="grid-stack dash-top-canvas">

            {{-- KPI tiles individuales --}}
            @foreach($kpis as $index => $k)
            <div class="grid-stack-item" id="{{ $k['id'] }}" gs-x="{{ $index * 2 }}" gs-y="0" gs-w="2" gs-h="1">
                <div class="grid-stack-item-content">
                    <div class="dash-grid-widget dash-kpi-widget">
                        <div class="kpi-tile">
                            <div class="kpi-accent" style="background:{{ $k['c'] }}"></div>
                            <div class="kpi-icon" style="background:{{ $k['bg'] }}">
                                <svg class="w-3 h-3" fill="none" stroke="{{ $k['c'] }}" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['ic'] }}"/>
                                </svg>
                            </div>
                            <div>
                                <div class="kpi-val" style="color:{{ $k['c'] }}">{{ number_format($k['v']) }}</div>
                                <div class="kpi-label">{{ $k['l'] }}</div>
                                <div class="kpi-sub">{{ $k['s'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Secretarías --}}
            <div class="grid-stack-item" id="widget-secretarias" gs-x="0" gs-y="1" gs-w="7" gs-h="3">
                <div class="grid-stack-item-content">
                    <div class="dash-grid-widget">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <div>
                                    <h3>{{ $listaLateralTipo === 'secretarias' ? 'Secretarías' : ($listaLateralTipo === 'unidades' ? 'Unidades' : 'Alcance') }}</h3>
                                    <p>Procesos activos por dependencia</p>
                                </div>
                                @if($listaLateralTipo === 'secretarias')
                                <span class="badge-pill" style="background:#f0fdf4;color:#15803d;padding:1px 6px;font-size:8px">{{ $listaLateral->count() }}</span>
                                @endif
                            </div>
                            <div style="overflow-y:auto;flex:1;max-height:148px">
                                @forelse($listaLateral as $item)
                                @php $barW = $listaLateral->max('total') > 0 ? round($item->total / $listaLateral->max('total') * 100) : 0; @endphp
                                <div style="padding:3px 10px;border-bottom:1px solid #f8fafc;transition:background .12s"
                                     onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                    <div class="flex items-center justify-between" style="margin-bottom:1px">
                                        <p style="font-size:9.5px;font-weight:500;color:#475569;flex:1;padding-right:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:-0.01em">{{ $item->nombre }}</p>
                                        <span style="font-size:11px;font-weight:900;color:#1e293b;flex-shrink:0">{{ $item->total }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div style="flex:1;height:2px;border-radius:999px;background:#f1f5f9;overflow:hidden">
                                            <div style="height:2px;border-radius:999px;width:{{ $barW }}%;background:linear-gradient(90deg,#86efac,#15803d)"></div>
                                        </div>
                                        <span style="font-size:8px;color:#16a34a;font-weight:700;flex-shrink:0">{{ $item->en_curso }} act.</span>
                                    </div>
                                </div>
                                @empty
                                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:16px 12px;text-align:center">
                                    <p style="font-size:10px;color:#9ca3af">Sin dependencias con procesos</p>
                                </div>
                                @endforelse
                            </div>
                            <div style="padding:3px 10px;border-top:1px solid #f8fafc;background:#fafafa;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
                                <span style="font-size:9px;color:#94a3b8;font-weight:600">Total procesos</span>
                                <span style="font-size:11px;font-weight:900;color:#1e293b">{{ number_format($totalProcesos) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Centro de alertas --}}
            <div class="grid-stack-item" id="widget-alertas" gs-x="7" gs-y="1" gs-w="5" gs-h="3">
                <div class="grid-stack-item-content">
                    <div class="dash-grid-widget">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <div>
                                    <h3>Centro de alertas</h3>
                                    <p>Riesgos y situaciones activas</p>
                                </div>
                                @if($alertasAltas > 0)
                                <span class="badge-pill" style="background:#fef2f2;color:#dc2626;padding:1px 6px;font-size:8px">{{ $alertasAltas }} alta prioridad</span>
                                @endif
                            </div>
                            <div class="p-2">
                                @php
                                $alertasList = [
                                    ['l'=>'Con retraso',       'v'=>$alertasRiesgos['procesos_con_retraso'],   'c'=>'#d97706','bg'=>'#fffbeb','ic'=>'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    ['l'=>'Docs. rechazados',  'v'=>$alertasRiesgos['documentos_rechazados'],  'c'=>'#dc2626','bg'=>'#fef2f2','ic'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    ['l'=>'Sin actividad',     'v'=>$alertasRiesgos['procesos_sin_actividad'], 'c'=>'#7c3aed','bg'=>'#faf5ff','ic'=>'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                                    ['l'=>'Cert. por vencer',  'v'=>$alertasRiesgos['certificados_por_vencer'],'c'=>'#ea580c','bg'=>'#fff7ed','ic'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                                ];
                                @endphp
                                @foreach($alertasList as $al)
                                <div class="alert-row" style="background:{{ $al['bg'] }}">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="{{ $al['c'] }}" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $al['ic'] }}"/>
                                        </svg>
                                        <span style="font-size:10px;font-weight:600;color:#374151">{{ $al['l'] }}</span>
                                    </div>
                                    <span style="font-size:12px;font-weight:900;color:{{ $al['c'] }}">{{ $al['v'] }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="px-2 pb-2 pt-0.5" style="border-top:1px solid #f8fafc">
                                <p style="font-size:9px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Modalidades</p>
                                @forelse($porModalidad as $m)
                                @php $maxMod = max(1, $porModalidad->max('total')); @endphp
                                <div class="area-bar-row">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span style="font-size:9px;font-weight:600;color:#475569" class="truncate flex-1 pr-2">{{ $m->nombre }}</span>
                                        <span style="font-size:10px;font-weight:800;color:#4f46e5">{{ $m->total }}</span>
                                    </div>
                                    <div style="height:3px;border-radius:999px;background:#f1f5f9;overflow:hidden">
                                        <div style="height:3px;border-radius:999px;width:{{ round($m->total/$maxMod*100) }}%;background:linear-gradient(90deg,#818cf8,#4f46e5)"></div>
                                    </div>
                                </div>
                                @empty
                                <p style="font-size:9px;color:#94a3b8;text-align:center;padding:6px 0">Sin modalidades registradas</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="grid-stack-item" id="widget-estado" gs-x="0" gs-y="4" gs-w="6" gs-h="4">
                <div class="grid-stack-item-content">
                    <div class="dash-graph-widget">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <div>
                                    <h3>Por estado</h3>
                                    <p>Distribución actual</p>
                                </div>
                            </div>
                            <div class="p-2 flex flex-col items-center justify-center" style="flex:1;min-height:120px">
                                <div style="width:100px;height:100px;position:relative;flex-shrink:0">
                                    <canvas id="donutEstadoChart"></canvas>
                                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none">
                                        <span style="font-size:17px;font-weight:900;color:#1e293b;line-height:1">{{ $totalProcesos }}</span>
                                        <span style="font-size:9px;color:#94a3b8">total</span>
                                    </div>
                                </div>
                                <div class="w-full mt-2 space-y-1">
                                    @php
                                    $estadoStats = [
                                        ['l'=>'En curso',   'v'=>$enCurso,    'c'=>'#2563eb'],
                                        ['l'=>'Finalizados','v'=>$finalizados,'c'=>'#15803d'],
                                        ['l'=>'Rechazados', 'v'=>$rechazados, 'c'=>'#dc2626'],
                                    ];
                                    @endphp
                                    @foreach($estadoStats as $es)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:{{ $es['c'] }}"></span>
                                            <span style="font-size:10px;color:#475569;font-weight:600">{{ $es['l'] }}</span>
                                        </div>
                                        <span style="font-size:11px;font-weight:800;color:{{ $es['c'] }}">{{ $es['v'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-stack-item" id="widget-tendencia" gs-x="6" gs-y="4" gs-w="6" gs-h="4">
                <div class="grid-stack-item-content">
                    <div class="dash-graph-widget">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <div>
                                    <h3>Tendencia de procesos</h3>
                                    <p>Evolución mensual — creados vs. finalizados vs. rechazados</p>
                                </div>
                            </div>
                            <div class="p-2" style="flex:1;min-height:120px">
                                <canvas id="lineaTendenciaChart" style="height:100% !important"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-stack-item" id="widget-area" gs-x="0" gs-y="8" gs-w="12" gs-h="2">
                <div class="grid-stack-item-content">
                    <div class="dash-graph-widget">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <div>
                                    <h3>Por área</h3>
                                    <p>Procesos EN CURSO por área</p>
                                </div>
                            </div>
                            <div class="p-2" style="flex:1;min-height:60px">
                                <canvas id="areaHorizChart" style="height:100% !important"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($scope === 'unidad')
            <div class="grid-stack-item" id="widget-docs-estado" gs-x="0" gs-y="10" gs-w="12" gs-h="3">
                <div class="grid-stack-item-content">
                    <div class="dash-graph-widget">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <div>
                                    <h3>Documentos por estado</h3>
                                    <p>Unidad actual</p>
                                </div>
                            </div>
                            <div class="p-2 flex flex-col items-center justify-center" style="flex:1;min-height:120px">
                                <div style="width:110px;height:110px;position:relative;flex-shrink:0">
                                    <canvas id="docsEstadoUnidadChart"></canvas>
                                </div>
                                <div class="w-full mt-2 space-y-1">
                                    @foreach($docsEstadoMap as $key => $info)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:{{ $info['color'] }}"></span>
                                            <span style="font-size:10px;color:#475569;font-weight:600">{{ $info['label'] }}</span>
                                        </div>
                                        <span style="font-size:11px;font-weight:800;color:{{ $info['color'] }}">{{ $docsEstadoCounts[$key] ?? 0 }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @endif
        </div>

        <div id="dashboardBottomCanvas" class="grid-stack dash-bottom-canvas">

        {{-- ═══ FILA 4: PROCESOS RECIENTES ═══ --}}
        <div class="grid-stack-item" id="widget-recientes" gs-x="0" gs-y="0" gs-w="12" gs-h="5">
            <div class="grid-stack-item-content">
                <div class="dash-grid-widget">
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <h3>Procesos recientes</h3>
                    <p>Últimos registros — estado y ubicación en el flujo</p>
                </div>
                <a href="{{ route('procesos.index') }}" style="font-size:11px;font-weight:700;color:#15803d;text-decoration:none"
                   onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Ver todos →</a>
            </div>
            <div style="overflow-y:auto;max-height:160px">
                @forelse($procesosRecientes as $proc)
                @php
                    $eCfg = $estadoConfig[$proc->estado] ?? ['label'=>$proc->estado,'color'=>'#6b7280','bg'=>'#f8fafc'];
                    $areaLabel = ['unidad_solicitante'=>'Unidad','planeacion'=>'Planeación','hacienda'=>'Hacienda','juridica'=>'Jurídica','secop'=>'SECOP'][$proc->area_actual_role] ?? ucfirst(str_replace('_',' ',$proc->area_actual_role));
                    $areaColor = ['unidad_solicitante'=>'#3b82f6','planeacion'=>'#16a34a','hacienda'=>'#ca8a04','juridica'=>'#ea580c','secop'=>'#9333ea'][$proc->area_actual_role] ?? '#6b7280';
                    $areaStep  = ['unidad_solicitante'=>1,'planeacion'=>2,'hacienda'=>3,'juridica'=>4,'secop'=>5][$proc->area_actual_role] ?? 0;
                    $rutaProc = match($proc->area_actual_role) {
                        'planeacion' => url('/planeacion/procesos/'.$proc->id),
                        'hacienda'   => url('/hacienda/procesos/'.$proc->id),
                        'juridica'   => url('/juridica/procesos/'.$proc->id),
                        'secop'      => url('/secop/procesos/'.$proc->id),
                        default      => route('procesos.show', $proc->id),
                    };
                @endphp
                <a href="{{ $rutaProc }}" class="proc-row" style="text-decoration:none;display:flex">
                    <div style="width:28px;height:28px;border-radius:8px;background:{{ $eCfg['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        @if($proc->estado === 'EN_CURSO')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="{{ $eCfg['color'] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif(in_array($proc->estado, ['FINALIZADO','completado','cerrado']))
                        <svg class="w-3.5 h-3.5" fill="none" stroke="{{ $eCfg['color'] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="{{ $eCfg['color'] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>
                    <div style="flex:2.5;min-width:0">
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <span class="mono" style="font-size:10px;font-weight:700;color:#64748b;background:#f1f5f9;padding:1px 5px;border-radius:4px">{{ $proc->codigo }}</span>
                            <span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:999px;background:{{ $eCfg['bg'] }};color:{{ $eCfg['color'] }}">{{ $eCfg['label'] }}</span>
                        </div>
                        <p style="font-size:11px;font-weight:600;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $proc->objeto }}</p>
                    </div>
                    <div style="flex-shrink:0;display:flex;align-items:center;gap:6px">
                        <div style="display:inline-flex;align-items:center;gap:5px;padding:3px 8px;border-radius:8px;background:{{ $areaColor }}15">
                            <span style="width:15px;height:15px;border-radius:50%;background:{{ $areaColor }};color:#fff;font-size:8px;font-weight:900;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $areaStep ?: '?' }}</span>
                            <span style="font-size:10px;font-weight:700;color:{{ $areaColor }}">{{ $areaLabel }}</span>
                        </div>
                        <span style="font-size:10px;color:#94a3b8">{{ \Carbon\Carbon::parse($proc->updated_at)->diffForHumans() }}</span>
                        <span style="font-size:11px;font-weight:600;color:#15803d;padding:3px 8px;border-radius:7px;background:#f0fdf4;border:1px solid #bbf7d0">Abrir →</span>
                    </div>
                </a>
                @empty
                <div style="display:flex;flex-direction:column;align-items:center;padding:24px 16px;text-align:center">
                    <svg class="w-10 h-10 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p style="font-size:12px;font-weight:600;color:#9ca3af">Sin procesos registrados</p>
                    @can('procesos.crear')
                    <a href="{{ route('procesos.create') }}" style="font-size:11px;font-weight:700;color:#15803d;margin-top:4px">Crear primera solicitud →</a>
                    @endcan
                </div>
                @endforelse
            </div>
        </div>
                </div>
            </div>
        </div>

        {{-- ═══ FILA 5: TABLA SEGUIMIENTO ═══ --}}
        <div class="grid-stack-item" id="widget-seguimiento" gs-x="0" gs-y="6" gs-w="12" gs-h="7">
            <div class="grid-stack-item-content">
                <div class="dash-grid-widget">
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <h3>Seguimiento en tiempo real</h3>
                    <p>Ubicación y estado actual de cada proceso en el flujo contractual</p>
                </div>
                <div class="flex items-center gap-2">
                    <span style="font-size:10px;color:#94a3b8">{{ $procesosRecientes->count() }} procesos</span>
                    <a href="{{ route('procesos.index') }}" class="inline-flex items-center gap-1" style="font-size:11px;font-weight:600;color:#15803d;text-decoration:none"
                       onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                        Ver todos
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:2px solid #f1f5f9">
                            <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Código</th>
                            <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Objeto</th>
                            <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Modalidad</th>
                            <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Etapa</th>
                            <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Área</th>
                            <th style="padding:6px 12px;text-align:center;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Estado</th>
                            <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Actualizado</th>
                            <th style="padding:6px 12px"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($procesosRecientes as $proc)
                    @php
                        $eCfg2 = $estadoConfig[$proc->estado] ?? ['label'=>$proc->estado,'color'=>'#6b7280','bg'=>'#f8fafc'];
                        $areaLabel2 = ['unidad_solicitante'=>'Unidad','planeacion'=>'Planeación','hacienda'=>'Hacienda','juridica'=>'Jurídica','secop'=>'SECOP'][$proc->area_actual_role] ?? ucfirst(str_replace('_',' ',$proc->area_actual_role));
                        $areaColor2 = ['unidad_solicitante'=>'#3b82f6','planeacion'=>'#16a34a','hacienda'=>'#ca8a04','juridica'=>'#ea580c','secop'=>'#9333ea'][$proc->area_actual_role] ?? '#6b7280';
                        $areaStep2  = ['unidad_solicitante'=>1,'planeacion'=>2,'hacienda'=>3,'juridica'=>4,'secop'=>5][$proc->area_actual_role] ?? 0;
                        $rutaProc2 = match($proc->area_actual_role) {
                            'planeacion' => url('/planeacion/procesos/'.$proc->id),
                            'hacienda'   => url('/hacienda/procesos/'.$proc->id),
                            'juridica'   => url('/juridica/procesos/'.$proc->id),
                            'secop'      => url('/secop/procesos/'.$proc->id),
                            default      => route('procesos.show', $proc->id),
                        };
                    @endphp
                    <tr style="border-bottom:1px solid #f8fafc" onmouseover="this.style.background='#f8fafc80'" onmouseout="this.style.background=''">
                        <td style="padding:6px 12px">
                            <span class="mono" style="font-size:11px;font-weight:700;color:#374151;background:#f1f5f9;padding:2px 6px;border-radius:5px">{{ $proc->codigo }}</span>
                        </td>
                        <td style="padding:6px 12px;max-width:200px">
                            <span style="font-size:11px;font-weight:500;color:#374151;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $proc->objeto }}</span>
                        </td>
                        <td style="padding:6px 12px">
                            <span style="font-size:11px;color:#64748b">{{ $proc->workflow ?? 'N/D' }}</span>
                        </td>
                        <td style="padding:6px 12px">
                            <span style="font-size:11px;color:#64748b;display:block;max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $proc->etapa ?? 'N/D' }}</span>
                        </td>
                        <td style="padding:6px 12px">
                            <div style="display:inline-flex;align-items:center;gap:5px;padding:3px 8px;border-radius:8px;background:{{ $areaColor2 }}15">
                                <span style="width:15px;height:15px;border-radius:50%;background:{{ $areaColor2 }};color:#fff;font-size:8px;font-weight:900;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $areaStep2 ?: '?' }}</span>
                                <span style="font-size:10px;font-weight:700;color:{{ $areaColor2 }}">{{ $areaLabel2 }}</span>
                            </div>
                        </td>
                        <td style="padding:6px 12px;text-align:center">
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700;background:{{ $eCfg2['bg'] }};color:{{ $eCfg2['color'] }}">
                                <span style="width:5px;height:5px;border-radius:50%;background:{{ $eCfg2['color'] }};flex-shrink:0"></span>
                                {{ $eCfg2['label'] }}
                            </span>
                        </td>
                        <td style="padding:6px 12px">
                            <span style="font-size:11px;color:#94a3b8">{{ \Carbon\Carbon::parse($proc->updated_at)->diffForHumans() }}</span>
                        </td>
                        <td style="padding:6px 12px">
                            <a href="{{ $rutaProc2 }}"
                               style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:8px;font-size:11px;font-weight:600;color:#15803d;border:1px solid #bbf7d0;background:#f0fdf4;text-decoration:none;transition:box-shadow .15s"
                               onmouseover="this.style.boxShadow='0 2px 6px rgba(21,128,61,.15)'" onmouseout="this.style.boxShadow=''">
                                Abrir →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding:36px 16px;text-align:center">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:8px">
                                <div style="width:44px;height:44px;border-radius:12px;background:#f1f5f9;display:flex;align-items:center;justify-content:center">
                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p style="font-size:13px;font-weight:600;color:#9ca3af">No hay procesos registrados</p>
                                @can('procesos.crear')
                                <a href="{{ route('procesos.create') }}" style="font-size:11px;font-weight:700;color:#15803d">Crear primera solicitud →</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
                </div>
            </div>
        </div>
        </div>

    </div>

    {{-- ═══ CHART.JS ═══ --}}
    <script src="{{ asset('vendor/gridstack/gridstack-all.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    window.dashboardCharts = window.dashboardCharts || {};
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'DM Sans'";
        Chart.defaults.color = '#94a3b8';
    }

    // ─── 1. DONUT: Distribución por estado ────────────────────────────────────
    (function () {
        if (typeof Chart === 'undefined') return;
        const ctx = document.getElementById('donutEstadoChart');
        if (!ctx) return;
        const total = {{ $totalProcesos }};
        const donutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['En curso', 'Finalizados', 'Rechazados'],
                datasets: [{
                    data: total > 0 ? [{{ $enCurso }}, {{ $finalizados }}, {{ $rechazados }}] : [1, 1, 1],
                    backgroundColor: total > 0 ? ['#2563eb', '#15803d', '#dc2626'] : ['#e2e8f0','#e2e8f0','#e2e8f0'],
                    borderWidth: 3, borderColor: '#fff', hoverOffset: 4,
                }]
            },
            options: {
                responsive:true, maintainAspectRatio:true, cutout:'72%',
                plugins:{
                    legend:{ display:false },
                    tooltip:{ enabled: total > 0, cornerRadius:8, callbacks:{ label: c => ` ${c.label}: ${c.raw}` }}
                }
            }
        });
        window.dashboardCharts.donutEstadoChart = donutChart;
    })();

    // ─── 2. HORIZONTAL BAR: Por área ──────────────────────────────────────────
    (function () {
        if (typeof Chart === 'undefined') return;
        const labels = @json(collect($porArea)->pluck('label'));
        const data   = @json(collect($porArea)->pluck('total'));
        const colors = @json(collect($porArea)->pluck('color'));
        const ctx = document.getElementById('areaHorizChart');
        if (!ctx) return;
        const areaChart = new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets:[{ data, backgroundColor: colors.map(c => c + 'cc'), borderColor: colors, borderWidth:2, borderRadius:5, borderSkipped:false }] },
            options: {
                indexAxis: 'y', responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false}, tooltip:{ cornerRadius:8, callbacks:{ label: c => ` ${c.raw} procesos` }} },
                scales:{
                    x:{ grid:{color:'#f1f5f9'}, border:{dash:[3,3],color:'transparent'}, ticks:{font:{size:10}, precision:0}, beginAtZero:true },
                    y:{ grid:{display:false}, ticks:{font:{size:10}} }
                }
            }
        });
        window.dashboardCharts.areaHorizChart = areaChart;
    })();

    // ─── 3. LINE: Tendencia ────────────────────────────────────────────────────
    (function () {
        if (typeof Chart === 'undefined') return;
        const meses       = @json(collect($tendencia)->pluck('mes_corto'));
        const creados     = @json(collect($tendencia)->pluck('creados'));
        const finalizados = @json(collect($tendencia)->pluck('finalizados'));
        const rechazados  = @json(collect($tendencia)->pluck('rechazados'));
        const ctx = document.getElementById('lineaTendenciaChart');
        if (!ctx) return;
        const trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [
                    { label:'Creados',    data:creados,     borderColor:'#3b82f6', backgroundColor:'#3b82f614', borderWidth:2.5, tension:0.4, fill:true,  pointBackgroundColor:'#3b82f6', pointRadius:4, pointHoverRadius:6 },
                    { label:'Finalizados',data:finalizados, borderColor:'#16a34a', backgroundColor:'#16a34a08', borderWidth:2.5, tension:0.4, fill:false, pointBackgroundColor:'#16a34a', pointRadius:4, pointHoverRadius:6 },
                    { label:'Rechazados', data:rechazados,  borderColor:'#dc2626', backgroundColor:'#dc262608', borderWidth:2,   tension:0.4, fill:false, borderDash:[5,3], pointBackgroundColor:'#dc2626', pointRadius:3, pointHoverRadius:5 },
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                interaction:{ mode:'index', intersect:false },
                plugins:{
                    legend:{ position:'top', align:'end', labels:{ boxWidth:8, boxHeight:8, borderRadius:3, useBorderRadius:true, font:{size:10} }},
                    tooltip:{ cornerRadius:8 }
                },
                scales:{
                    x:{ grid:{display:false}, ticks:{font:{size:10}} },
                    y:{ grid:{color:'#f1f5f9'}, border:{dash:[3,3],color:'transparent'}, ticks:{font:{size:10}, precision:0, stepSize:1}, beginAtZero:true }
                }
            }
        });
        window.dashboardCharts.lineaTendenciaChart = trendChart;
    })();

    @if($scope === 'unidad')
    // ─── 4. DONUT: Documentos por estado ─────────────────────────────────────
    (function () {
        if (typeof Chart === 'undefined') return;
        const ctx = document.getElementById('docsEstadoUnidadChart');
        if (!ctx) return;
        const labels = @json($docsEstadoLabels);
        const data = @json($docsEstadoValues);
        const colors = @json($docsEstadoColors);
        const total = data.reduce((acc, val) => acc + val, 0);
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: total > 0 ? data : [1, 1, 1, 1],
                    backgroundColor: total > 0 ? colors : ['#e2e8f0','#e2e8f0','#e2e8f0','#e2e8f0'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: total > 0, cornerRadius: 8 }
                }
            }
        });
        window.dashboardCharts.docsEstadoUnidadChart = chart;
    })();
    @endif

    // ─── 5. CANVAS: GridStack para TODO el dashboard ─────────────────────────
    (function () {
        const editBtn = document.getElementById('graphEditToggle');
        const resetBtn = document.getElementById('graphLayoutReset');
        const editState = document.getElementById('graphEditState');

        const GridStackClass =
            (window.GridStack && typeof window.GridStack.init === 'function')
                ? window.GridStack
                : (window.GridStack && window.GridStack.GridStack && typeof window.GridStack.GridStack.init === 'function')
                    ? window.GridStack.GridStack
                    : null;

        if (!GridStackClass) {
            if (editState) editState.textContent = 'Grid no disponible';
            if (editBtn) {
                editBtn.disabled = true;
                editBtn.style.opacity = '0.55';
                editBtn.style.cursor = 'not-allowed';
            }
            if (resetBtn) {
                resetBtn.disabled = true;
                resetBtn.style.opacity = '0.55';
                resetBtn.style.cursor = 'not-allowed';
            }
            return;
        }

        const canvasConfigs = [
            {
                canvasId: 'dashboardTopCanvas',
                storageKey: 'dashboard.global.top.layout.v6',
                defaultLayout: [
                    { id: 'widget-kpi-total-procesos', x: 0, y: 0, w: 2, h: 1 },
                    { id: 'widget-kpi-en-curso', x: 2, y: 0, w: 2, h: 1 },
                    { id: 'widget-kpi-finalizados', x: 4, y: 0, w: 2, h: 1 },
                    { id: 'widget-kpi-rechazados', x: 6, y: 0, w: 2, h: 1 },
                    { id: 'widget-kpi-creados-mes', x: 8, y: 0, w: 2, h: 1 },
                    { id: 'widget-kpi-alertas-criticas', x: 10, y: 0, w: 2, h: 1 },
                    { id: 'widget-secretarias', x: 0, y: 1, w: 7, h: 3 },
                    { id: 'widget-alertas', x: 7, y: 1, w: 5, h: 3 },
                    { id: 'widget-estado', x: 0, y: 4, w: 6, h: 4 },
                    { id: 'widget-tendencia', x: 6, y: 4, w: 6, h: 4 },
                    { id: 'widget-area', x: 0, y: 8, w: 12, h: 2 },
                    @if($scope === 'unidad')
                    { id: 'widget-docs-estado', x: 0, y: 10, w: 12, h: 3 },
                    @endif
                ],
            },
            {
                canvasId: 'dashboardBottomCanvas',
                storageKey: 'dashboard.global.bottom.layout.v3',
                defaultLayout: [
                    { id: 'widget-recientes', x: 0, y: 0, w: 12, h: 5 },
                    { id: 'widget-seguimiento', x: 0, y: 5, w: 12, h: 7 },
                ],
            },
        ];

        const toInt = (value, fallback, min = 0) => {
            const parsed = Number(value);
            return Number.isFinite(parsed) ? Math.max(min, Math.round(parsed)) : fallback;
        };

        const normalizeLayout = (layout, defaults) => {
            const source = Array.isArray(layout) ? layout : [];
            const map = new Map(source.map(item => [item.id, item]));
            return defaults.map(item => {
                const found = map.get(item.id) || {};
                return {
                    id: item.id,
                    x: toInt(found.x, item.x),
                    y: toInt(found.y, item.y),
                    w: toInt(found.w, item.w, 1),
                    h: toInt(found.h, item.h, 1),
                };
            });
        };

        const readStoredLayout = (storageKey, defaults) => {
            try {
                const raw = localStorage.getItem(storageKey);
                if (!raw) return null;
                return normalizeLayout(JSON.parse(raw), defaults);
            } catch (_error) {
                return null;
            }
        };

        const applyLayout = (grid, layout, defaults) => {
            const normalized = normalizeLayout(layout, defaults);
            grid.batchUpdate();
            normalized.forEach(item => {
                const el = document.getElementById(item.id);
                if (!el) return;
                grid.update(el, { x: item.x, y: item.y, w: item.w, h: item.h });
            });
            grid.batchUpdate(false);
        };

        const saveLayout = (grid, defaults, storageKey) => {
            const layout = defaults.map(item => {
                const el = document.getElementById(item.id);
                const node = el ? el.gridstackNode : null;
                if (!node) return item;
                return {
                    id: item.id,
                    x: node.x,
                    y: node.y,
                    w: node.w,
                    h: node.h,
                };
            });
            localStorage.setItem(storageKey, JSON.stringify(layout));
        };

        const resizeCharts = () => {
            Object.values(window.dashboardCharts || {}).forEach(chart => {
                if (chart && typeof chart.resize === 'function') {
                    chart.resize();
                }
            });
        };

        const gridEntries = [];

        canvasConfigs.forEach(config => {
            const canvas = document.getElementById(config.canvasId);
            if (!canvas) return;

            const grid = GridStackClass.init({
                column: 12,
                float: true,
                margin: 1,
                cellHeight: 44,
                animate: true,
                disableDrag: false,
                disableResize: false,
                handle: '.grid-stack-item-content',
                draggable: {
                    handle: '.grid-stack-item-content',
                    cancel: 'a,button,input,textarea,select,option'
                },
                resizable: { handles: 'all' },
            }, canvas);

            grid.setStatic(true);
            applyLayout(grid, readStoredLayout(config.storageKey, config.defaultLayout) || config.defaultLayout, config.defaultLayout);

            gridEntries.push({ grid, canvas, config });
        });

        if (!gridEntries.length) return;

        const setEditMode = (enabled) => {
            gridEntries.forEach(({ grid, canvas }) => {
                grid.setStatic(!enabled);
                if (typeof grid.enableMove === 'function') {
                    grid.enableMove(enabled);
                }
                if (typeof grid.enableResize === 'function') {
                    grid.enableResize(enabled);
                }
                canvas.classList.toggle('graph-editing', enabled);

                if (enabled) {
                    canvas.querySelectorAll('.grid-stack-item').forEach(item => {
                        item.classList.remove('ui-draggable-disabled', 'ui-resizable-disabled');
                    });
                }
            });

            if (editBtn) {
                editBtn.classList.toggle('active', enabled);
                editBtn.textContent = enabled ? 'Bloquear' : 'Organizar';
            }
            if (editState) {
                editState.textContent = enabled ? 'Editando' : 'Bloqueado';
                editState.style.color = enabled ? '#166534' : '#94a3b8';
            }
        };

        const persistAll = () => {
            gridEntries.forEach(({ grid, config }) => {
                saveLayout(grid, config.defaultLayout, config.storageKey);
            });
            window.requestAnimationFrame(resizeCharts);
        };

        let persistTimer = null;
        const onGridChanged = () => {
            if (persistTimer) window.clearTimeout(persistTimer);
            persistTimer = window.setTimeout(persistAll, 140);
        };

        gridEntries.forEach(({ grid }) => {
            grid.on('dragstop', onGridChanged);
            grid.on('resizestop', onGridChanged);
        });

        setEditMode(false);

        if (editBtn) {
            editBtn.addEventListener('click', () => {
                const isEditing = gridEntries.some(({ canvas }) => canvas.classList.contains('graph-editing'));
                const enable = !isEditing;
                setEditMode(enable);
                if (!enable) {
                    persistAll();
                }
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                gridEntries.forEach(({ grid, config }) => {
                    localStorage.removeItem(config.storageKey);
                    applyLayout(grid, config.defaultLayout, config.defaultLayout);
                });
                setEditMode(false);
                persistAll();
            });
        }

        window.addEventListener('resize', resizeCharts);
    })();
    </script>

</x-app-layout>

