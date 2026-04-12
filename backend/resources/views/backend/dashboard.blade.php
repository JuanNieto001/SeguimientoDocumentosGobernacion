{{-- Archivo: backend/resources/views/backend/dashboard.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">
                    Bienvenido, {{ explode(' ', auth()->user()->name)[0] }} 👋
                </h1>
                <p class="text-xs text-gray-400 mt-1">
                    @php
                        $roleLabels = [
                            'unidad_solicitante'  => 'Unidad Solicitante',
                            'planeacion'          => 'Secretaría de Planeación',
                            'compras'             => 'Unidad de Compras — Secretaría General',
                            'talento_humano'      => 'Talento Humano — Secretaría General',
                            'rentas'              => 'Unidad de Rentas — Secretaría de Hacienda',
                            'contabilidad'        => 'Unidad de Contabilidad — Secretaría de Hacienda',
                            'inversiones_publicas'=> 'Regalías e Inversiones — Secretaría de Planeación',
                            'presupuesto'         => 'Unidad de Presupuesto — Secretaría de Hacienda',
                            'radicacion'          => 'Radicación y Correspondencia — Secretaría General',
                            'hacienda'            => 'Secretaría de Hacienda',
                            'juridica'            => 'Secretaría Jurídica',
                            'secop'               => 'Grupo SECOP',
                        ];
                        $userRoleLabel = collect($roleLabels)->first(fn($label, $role) => auth()->user()->hasRole($role)) ?? 'Usuario';
                    @endphp
                    {{ $userRoleLabel }} &mdash; Gobernación de Caldas
                </p>
            </div>
        </div>
    </x-slot>

    @php
    $bandejaRoutes = [
        'unidad_solicitante' => 'unidad.index',
        'planeacion'         => 'planeacion.index',
        'hacienda'           => 'hacienda.index',
        'juridica'           => 'juridica.index',
        'secop'              => 'secop.index',
    ];
    // Usuarios con rol de área-doc NO deben ver la bandeja de planeacion/hacienda aunque tengan ese rol
    $rolesDocCheck2 = ['compras','talento_humano','rentas','contabilidad','inversiones_publicas','presupuesto','radicacion'];
    $esAreaDoc2 = collect($rolesDocCheck2)->contains(fn($r) => auth()->user()->hasRole($r));
    $myBandeja = $esAreaDoc2 ? null : collect($bandejaRoutes)->first(fn($route, $role) => auth()->user()->hasRole($role));
    $myBandejaLabel = $esAreaDoc2 ? '' : (collect(['unidad_solicitante'=>'Unidad','planeacion'=>'Planeación','hacienda'=>'Hacienda','juridica'=>'Jurídica','secop'=>'SECOP'])
        ->first(fn($lbl, $role) => auth()->user()->hasRole($role)) ?? '');

    $rolesDocumentosCheck = ['compras','talento_humano','rentas','contabilidad','inversiones_publicas','presupuesto','radicacion'];
    $esAreaDocumentos = collect($rolesDocumentosCheck)->contains(fn($r) => auth()->user()->hasRole($r));

    $solicitudesPendientes = $solicitudesPendientes ?? collect();
    $solicitudesPendientesArea = $solicitudesPendientesArea ?? collect();
    $pendientesAreaTotal = $solicitudesPendientesArea->sum('docs_pendientes');
    $totalSolPendientes = $solicitudesPendientes->sum('docs_pendientes');
    $totalSolSubidos    = $solicitudesPendientes->sum('docs_subidos');
    $totalSolDocs       = $solicitudesPendientes->sum('total_docs');
    $kpisDocumentos = $kpisDocumentos ?? (object) ['aprobados' => 0, 'rechazados' => 0];

    $enCursoCount = ($enCurso ?? collect())->count();
    $finalizadoCount = ($finalizados ?? collect())->count();
    $total = $enCursoCount + $finalizadoCount;

    $filtroPeriodo = $filtroPeriodo ?? 'mes';
    $filtroMes = $filtroMes ?? now()->month;
    $filtroAnio = $filtroAnio ?? now()->year;
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $anios = range(now()->year, now()->year - 4);
    $mostrarAcciones = $myBandeja
        || (!$esAreaDocumentos && auth()->user()->can('procesos.crear') && (auth()->user()->hasRole('planeacion') || auth()->user()->hasRole('unidad_solicitante') || auth()->user()->hasRole('admin')))
        || (!$esAreaDocumentos && (auth()->user()->hasRole('planeacion') || auth()->user()->hasRole('admin')));
    @endphp

    <link rel="stylesheet" href="{{ asset('vendor/gridstack/gridstack.min.css') }}">

    <style>
        .dash-user-grid { min-height: 0; }
        .dash-user-grid .grid-stack-item-content {
            inset: 0 !important;
            background: transparent;
            border: none;
            overflow: auto;
        }
        .dash-user-editing .grid-stack-item-content { cursor: move; }
        .dash-user-toolbar-btn {
            border: 1px solid #dbe3ec;
            background: #ffffff;
            color: #475569;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            transition: all .14s ease;
        }
        .dash-user-toolbar-btn:hover { background: #f8fafc; color: #0f172a; }
        .dash-user-toolbar-btn.active {
            background: linear-gradient(135deg,#166534,#14532d);
            border-color: #14532d;
            color: #ffffff;
        }
        .kpi-mini-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .kpi-mini-table thead th {
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: 10px;
            color: #94a3b8;
            background: #f8fafc;
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .kpi-mini-table td { padding: 8px 10px; }
        .kpi-mini-row:hover { background: #f8fafc; }
        .kpi-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            display: inline-block;
        }
        .kpi-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid transparent;
        }
        .kpi-badge-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
        .kpi-badge-danger { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
        .dash-filter-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 6px 10px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
        }
        .dash-filter-pill select {
            font-size: 11px;
        }
        .dash-filter-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
    </style>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        <div class="flex items-start justify-between flex-wrap gap-3">
            <div>
                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Dashboard personal</p>
                <p class="text-[11px] text-gray-400">Arrastra y ajusta los bloques @if(!empty($metricas['mes'])) • {{ $metricas['mes'] }} @endif</p>
            </div>
            <div class="flex flex-col items-end gap-2">
                <form method="GET" class="dash-filter-pill">
                    <span class="dash-filter-label">Filtro</span>
                    <select id="userDashPeriodo" name="periodo" class="border rounded-lg px-2 py-1 bg-white" style="border-color:#e2e8f0">
                        <option value="mes" @if($filtroPeriodo === 'mes') selected @endif>Mes</option>
                        <option value="anio" @if($filtroPeriodo === 'anio') selected @endif>Año</option>
                    </select>
                    <select id="userDashMes" name="mes" class="border rounded-lg px-2 py-1 bg-white" style="border-color:#e2e8f0">
                        @foreach($meses as $num => $label)
                        <option value="{{ $num }}" @if((int) $filtroMes === $num) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select id="userDashAnio" name="anio" class="border rounded-lg px-2 py-1 bg-white" style="border-color:#e2e8f0">
                        @foreach($anios as $anio)
                        <option value="{{ $anio }}" @if((int) $filtroAnio === (int) $anio) selected @endif>{{ $anio }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="dash-user-toolbar-btn">Aplicar</button>
                </form>
                <div class="flex items-center gap-2">
                    <span id="userDashEditState" class="text-[11px] font-semibold text-gray-400">Bloqueado</span>
                    <button id="userDashEditToggle" type="button" class="dash-user-toolbar-btn">Organizar</button>
                    <button id="userDashLayoutReset" type="button" class="dash-user-toolbar-btn">Restablecer</button>
                </div>
            </div>
        </div>

        <div id="dashboardUserCanvas" class="grid-stack dash-user-grid">

        {{-- ── Métricas del mes por rol ────────────────────────────────── --}}
        @php
            $colorMap = [
                'blue'   => ['bg'=>'#dbeafe','text'=>'#1d4ed8','num'=>'#1e40af'],
                'green'  => ['bg'=>'#dcfce7','text'=>'#15803d','num'=>'#166534'],
                'yellow' => ['bg'=>'#fef9c3','text'=>'#a16207','num'=>'#92400e'],
                'red'    => ['bg'=>'#fee2e2','text'=>'#b91c1c','num'=>'#991b1b'],
                'gray'   => ['bg'=>'#f1f5f9','text'=>'#475569','num'=>'#334155'],
            ];
            $kpiItems = $metricas['tarjetas'] ?? [];
            $kpiCount = is_countable($kpiItems) ? count($kpiItems) : 0;
            $kpiRows = $kpiCount > 0 ? (int) ceil($kpiCount / 4) : 0;
            $layoutY = $kpiRows * 2;
        @endphp
        @if(!empty($kpiItems))
            @foreach($kpiItems as $idx => $t)
            @php
                $c = $colorMap[$t['color']] ?? $colorMap['gray'];
                $x = ($idx % 4) * 3;
                $y = intdiv($idx, 4) * 2;
            @endphp
            <div class="grid-stack-item" id="widget-kpi-{{ $idx }}" gs-x="{{ $x }}" gs-y="{{ $y }}" gs-w="3" gs-h="2">
                <div class="grid-stack-item-content">
                    <div class="bg-white rounded-2xl" style="border:1px solid #e2e8f0">
                        <div class="flex items-center gap-3 px-4 py-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg"
                                 style="background:{{ $c['bg'] }}">
                                {{ $t['icono'] }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-black leading-none" style="color:{{ $c['num'] }}">{{ $t['valor'] }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 leading-tight">{{ $t['label'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
        {{-- ── Fin métricas del mes ─────────────────────────────────────── --}}

        {{-- Acciones rápidas --}}
        @if($mostrarAcciones)
        <div class="grid-stack-item" id="widget-acciones" gs-x="0" gs-y="{{ $layoutY }}" gs-w="12" gs-h="2">
            <div class="grid-stack-item-content">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @if($myBandeja)
                    <a href="{{ route($myBandeja) }}"
                       class="flex items-center gap-3 p-4 bg-white rounded-2xl transition-all hover:shadow-md hover:-translate-y-0.5"
                       style="border:1px solid #e2e8f0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#f0fdf4">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800">Mi bandeja</p>
                            <p class="text-xs text-gray-400 truncate">{{ $myBandejaLabel }}</p>
                        </div>
                    </a>
                    @endif

                        @if(!$esAreaDocumentos && auth()->user()->can('procesos.crear') && (auth()->user()->hasRole('planeacion') || auth()->user()->hasRole('unidad_solicitante') || auth()->user()->hasRole('admin')))
                    <a href="{{ route('procesos.create') }}"
                       class="flex items-center gap-3 p-4 bg-white rounded-2xl transition-all hover:shadow-md hover:-translate-y-0.5"
                       style="border:1px solid #e2e8f0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#dbeafe">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Nueva solicitud</p>
                            <p class="text-xs text-gray-400">CD-PN</p>
                        </div>
                    </a>
                    @endif

                    @if(!$esAreaDocumentos && (auth()->user()->hasRole('planeacion') || auth()->user()->hasRole('admin')))
                    <a href="{{ route('paa.index') }}"
                       class="flex items-center gap-3 p-4 bg-white rounded-2xl transition-all hover:shadow-md hover:-translate-y-0.5"
                       style="border:1px solid #e2e8f0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#fff7ed">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Plan Anual</p>
                            <p class="text-xs text-gray-400">PAA</p>
                        </div>
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @php $layoutY += 2; @endphp
        @endif

        {{-- ── Panel de solicitudes de documentos para áreas específicas ── --}}
        @if($esAreaDocumentos)
        <div class="grid-stack-item" id="widget-docs-exitosos" gs-x="0" gs-y="{{ $layoutY }}" gs-w="6" gs-h="2">
            <div class="grid-stack-item-content">
                <div class="bg-white rounded-2xl" style="border:1px solid #e2e8f0">
                    <div class="flex items-center justify-between px-4 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f0fdf4">
                                <svg class="w-4 h-4" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Resultados</p>
                                <p class="text-sm font-semibold text-gray-800">Exitosos</p>
                                <p class="text-xs text-gray-400">{{ $metricas['mes'] ?? '' }}</p>
                            </div>
                        </div>
                        <p class="text-2xl font-black" style="color:#15803d">{{ $kpisDocumentos->aprobados }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-stack-item" id="widget-docs-devueltos" gs-x="6" gs-y="{{ $layoutY }}" gs-w="6" gs-h="2">
            <div class="grid-stack-item-content">
                <div class="bg-white rounded-2xl" style="border:1px solid #e2e8f0">
                    <div class="flex items-center justify-between px-4 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#fef2f2">
                                <svg class="w-4 h-4" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Resultados</p>
                                <p class="text-sm font-semibold text-gray-800">Devueltos</p>
                                <p class="text-xs text-gray-400">{{ $metricas['mes'] ?? '' }}</p>
                            </div>
                        </div>
                        <p class="text-2xl font-black" style="color:#b91c1c">{{ $kpisDocumentos->rechazados }}</p>
                    </div>
                </div>
            </div>
        </div>
        @php $layoutY += 2; @endphp

        <div class="grid-stack-item" id="widget-docs-grafica" gs-x="0" gs-y="{{ $layoutY }}" gs-w="12" gs-h="5">
            <div class="grid-stack-item-content">
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                        <div>
                            <h2 class="text-sm font-bold text-gray-700">Estado de documentos</h2>
                            <p class="text-xs text-gray-400">Exitosos, devueltos y pendientes</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $metricas['mes'] ?? '' }}</span>
                    </div>
                    <div class="p-4" style="height:220px">
                        <canvas id="docsEstadoChart" aria-label="Estado de documentos" role="img"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @php $layoutY += 5; @endphp

        <div class="grid-stack-item" id="widget-docs-pendientes" gs-x="0" gs-y="{{ $layoutY }}" gs-w="12" gs-h="7">
            <div class="grid-stack-item-content">
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                        <div>
                            <h2 class="text-sm font-bold text-gray-700">Pendientes por subir</h2>
                            <p class="text-xs text-gray-400">Mis procesos con documentos solicitados</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400">{{ $pendientesAreaTotal }} pendientes</span>
                            <a href="{{ route('solicitudes.index') }}" class="text-xs text-blue-700 hover:text-blue-900 font-medium">Ver todos →</a>
                        </div>
                    </div>
                    @if($solicitudesPendientesArea->isEmpty())
                    <div class="flex flex-col items-center gap-2 py-10">
                        <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm text-gray-400">No tienes documentos pendientes por subir en este periodo</p>
                    </div>
                    @else
                    <div class="divide-y" style="divide-color:#f8fafc">
                        @foreach($solicitudesPendientesArea as $sol)
                        @php
                            $allDone = $sol->docs_pendientes == 0;
                        @endphp
                        <div class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg"
                                     style="background:{{ $allDone ? '#dcfce7' : '#fff7ed' }}">
                                    {{ $allDone ? '✅' : '📄' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900 font-mono">{{ $sol->proceso_codigo }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $sol->proceso_objeto }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Etapa: {{ $sol->etapa_nombre }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 ml-4">
                                {{-- Barra de progreso --}}
                                <div class="hidden sm:flex flex-col items-end gap-1">
                                    <span class="text-xs font-semibold" style="color:{{ $allDone ? '#15803d' : '#92400e' }}">
                                        {{ $sol->docs_subidos }}/{{ $sol->total_docs }} entregados
                                    </span>
                                    <div class="w-24 bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full"
                                             style="width:{{ $sol->total_docs > 0 ? ($sol->docs_subidos/$sol->total_docs)*100 : 0 }}%;
                                                    background:{{ $allDone ? '#16a34a' : '#f59e0b' }}"></div>
                                    </div>
                                </div>
                                <a href="{{ route('solicitudes.detalle', $sol->proceso_id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                                   style="background:{{ $allDone ? '#f0fdf4' : '#fff7ed' }};color:{{ $allDone ? '#15803d' : '#c2410c' }}">
                                    {{ $allDone ? 'Ver' : 'Subir' }}
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @php $layoutY += 7; @endphp
        @endif
        {{-- ── FIN Panel solicitudes ── --}}

        {{-- Historial --}}
        @if(($finalizados ?? collect())->isNotEmpty())
        <div class="grid-stack-item" id="widget-historial" gs-x="0" gs-y="{{ $layoutY }}" gs-w="12" gs-h="4">
            <div class="grid-stack-item-content">
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                        <h2 class="text-sm font-bold text-gray-700">Historial (finalizados)</h2>
                    </div>
                    <div class="divide-y" style="divide-color:#f8fafc">
                        @foreach($finalizados->take(5) as $p)
                        <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                            <div class="min-w-0">
                                <a href="{{ route('procesos.show', $p->id) }}" class="text-sm font-semibold text-gray-700 font-mono hover:text-green-700">{{ $p->codigo }}</a>
                                <p class="text-xs text-gray-400 truncate">{{ $p->objeto }}</p>
                            </div>
                            <span class="text-xs text-gray-400 shrink-0 ml-4">{{ \Carbon\Carbon::parse($p->updated_at)->format('d/m/Y') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @php $layoutY += 4; @endphp
        @endif

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="{{ asset('vendor/gridstack/gridstack-all.js') }}"></script>
    <script>
    (function () {
        const periodoSelect = document.getElementById('userDashPeriodo');
        const mesSelect = document.getElementById('userDashMes');
        if (periodoSelect && mesSelect) {
            const syncPeriodo = () => {
                const disabled = periodoSelect.value === 'anio';
                mesSelect.disabled = disabled;
                mesSelect.style.opacity = disabled ? '0.6' : '1';
            };
            periodoSelect.addEventListener('change', syncPeriodo);
            syncPeriodo();
        }

        const canvas = document.getElementById('dashboardUserCanvas');
        const editBtn = document.getElementById('userDashEditToggle');
        const resetBtn = document.getElementById('userDashLayoutReset');
        const editState = document.getElementById('userDashEditState');

        const GridStackClass =
            (window.GridStack && typeof window.GridStack.init === 'function')
                ? window.GridStack
                : (window.GridStack && window.GridStack.GridStack && typeof window.GridStack.GridStack.init === 'function')
                    ? window.GridStack.GridStack
                    : null;

        if (!canvas || !GridStackClass) {
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

        const storageKey = 'dashboard.user.layout.{{ auth()->id() }}.v6';

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

        const readStoredLayout = (defaults) => {
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

        const saveLayout = (grid, defaults) => {
            const layout = defaults.map(item => {
                const el = document.getElementById(item.id);
                const node = el ? el.gridstackNode : null;
                if (!node) return item;
                return { id: item.id, x: node.x, y: node.y, w: node.w, h: node.h };
            });
            localStorage.setItem(storageKey, JSON.stringify(layout));
        };

        const readDefaultLayout = () => {
            const items = Array.from(canvas.querySelectorAll('.grid-stack-item'));
            return items
                .map(item => ({
                    id: item.id,
                    x: toInt(item.getAttribute('gs-x'), 0),
                    y: toInt(item.getAttribute('gs-y'), 0),
                    w: toInt(item.getAttribute('gs-w'), 12, 1),
                    h: toInt(item.getAttribute('gs-h'), 2, 1),
                }))
                .sort((a, b) => (a.y - b.y) || (a.x - b.x));
        };

        const defaultLayout = readDefaultLayout();

        const grid = GridStackClass.init({
            column: 12,
            float: true,
            margin: 12,
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

        const removeEmptyWidgets = () => {
            const items = Array.from(canvas.querySelectorAll('.grid-stack-item'));
            items.forEach(item => {
                const content = item.querySelector('.grid-stack-item-content');
                if (!content || !content.children.length) {
                    grid.removeWidget(item, true, true);
                }
            });
        };

        applyLayout(grid, readStoredLayout(defaultLayout) || defaultLayout, defaultLayout);
        removeEmptyWidgets();
        if (typeof grid.compact === 'function') {
            grid.compact();
        }

        const setEditMode = (enabled) => {
            grid.setStatic(!enabled);
            if (typeof grid.enableMove === 'function') {
                grid.enableMove(enabled);
            }
            if (typeof grid.enableResize === 'function') {
                grid.enableResize(enabled);
            }
            canvas.classList.toggle('dash-user-editing', enabled);
            if (editBtn) {
                editBtn.classList.toggle('active', enabled);
                editBtn.textContent = enabled ? 'Bloquear' : 'Organizar';
            }
            if (editState) {
                editState.textContent = enabled ? 'Editando' : 'Bloqueado';
                editState.style.color = enabled ? '#166534' : '#94a3b8';
            }
            if (!enabled && typeof grid.compact === 'function') {
                grid.compact();
            }
        };

        let persistTimer = null;
        const onGridChanged = () => {
            if (persistTimer) window.clearTimeout(persistTimer);
            persistTimer = window.setTimeout(() => saveLayout(grid, defaultLayout), 140);
        };

        grid.on('dragstop', onGridChanged);
        grid.on('resizestop', onGridChanged);

        setEditMode(false);

        if (editBtn) {
            editBtn.addEventListener('click', () => {
                const enabled = !canvas.classList.contains('dash-user-editing');
                setEditMode(enabled);
                if (!enabled) saveLayout(grid, defaultLayout);
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                localStorage.removeItem(storageKey);
                applyLayout(grid, defaultLayout, defaultLayout);
                removeEmptyWidgets();
                if (typeof grid.compact === 'function') {
                    grid.compact();
                }
                setEditMode(false);
                saveLayout(grid, defaultLayout);
            });
        }

        const estadoChartEl = document.getElementById('docsEstadoChart');
        if (estadoChartEl && typeof Chart !== 'undefined') {
            const data = {
                labels: ['Exitosos', 'Devueltos', 'Pendientes'],
                datasets: [{
                    data: [
                        {{ (int) ($kpisDocumentos->aprobados ?? 0) }},
                        {{ (int) ($kpisDocumentos->rechazados ?? 0) }},
                        {{ (int) ($pendientesAreaTotal ?? 0) }}
                    ],
                    backgroundColor: ['#16a34a88', '#dc262688', '#f59e0b88'],
                    borderColor: ['#16a34a', '#dc2626', '#f59e0b'],
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 22,
                }]
            };

            new Chart(estadoChartEl, {
                type: 'bar',
                data,
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { cornerRadius: 8 }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { font: { size: 10 }, precision: 0 }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } }
                        }
                    }
                }
            });
        }
    })();
    </script>
</x-app-layout>
