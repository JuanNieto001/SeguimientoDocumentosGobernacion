<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    @if($scope === 'global')
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full" style="background:#dcfce7;color:#15803d">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>
                            VISTA GLOBAL
                        </span>
                    @elseif($scope === 'secretaria')
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full" style="background:#dbeafe;color:#1d4ed8">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span>
                            SECRETARÍA
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full" style="background:#fef3c7;color:#92400e">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                            UNIDAD
                        </span>
                    @endif
                </div>
                <h1 class="text-lg font-black text-gray-900 leading-none">Panel de Control</h1>
                <p class="text-xs text-gray-400 mt-0.5">{{ $scopeNombre }} &mdash; {{ now()->translatedFormat('F Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
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
    @endphp

    <div class="p-5 space-y-4" style="background:#f8fafc;min-height:calc(100vh - 80px)">

        {{-- ═══ KPI CARDS ═══ --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
            @php
            $kpis = [
                ['v'=>$totalProcesos,   'l'=>'Total procesos',   's'=>'Registrados',      'c'=>'#2563eb','bg'=>'#eff6ff','b'=>'#bfdbfe',
                 'ic'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['v'=>$enCurso,         'l'=>'En curso',         's'=>'Procesos activos', 'c'=>'#0891b2','bg'=>'#ecfeff','b'=>'#a5f3fc',
                 'ic'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['v'=>$finalizados,     'l'=>'Finalizados',      's'=>'Completados',      'c'=>'#15803d','bg'=>'#f0fdf4','b'=>'#bbf7d0',
                 'ic'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['v'=>$rechazados,      'l'=>'Rechazados',       's'=>'Total histórico',  'c'=>'#dc2626','bg'=>'#fef2f2','b'=>'#fecaca',
                 'ic'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['v'=>$creadosMes,      'l'=>'Creados hoy',      's'=>ucfirst(now()->translatedFormat('F')), 'c'=>'#7c3aed','bg'=>'#faf5ff','b'=>'#e9d5ff',
                 'ic'=>'M12 4v16m8-8H4'],
                ['v'=>$alertasAltas,    'l'=>'Alertas críticas', 's'=>'Alta prioridad',   'c'=>'#d97706','bg'=>'#fffbeb','b'=>'#fde68a',
                 'ic'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ];
            @endphp
            @foreach($kpis as $k)
            <div class="bg-white rounded-2xl p-4 border relative overflow-hidden hover:shadow-md transition-all duration-200 group" style="border-color:{{ $k['b'] }}">
                <div class="absolute -right-4 -bottom-4 w-16 h-16 rounded-full opacity-[0.07] group-hover:opacity-[0.12] transition-opacity" style="background:{{ $k['c'] }}"></div>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center mb-3" style="background:{{ $k['bg'] }}">
                    <svg class="w-4 h-4" fill="none" stroke="{{ $k['c'] }}" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['ic'] }}"/>
                    </svg>
                </div>
                <p class="text-2xl font-black leading-none tracking-tight" style="color:{{ $k['c'] }}">{{ number_format($k['v']) }}</p>
                <p class="text-xs font-bold text-gray-700 mt-1.5 leading-tight">{{ $k['l'] }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $k['s'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- ═══ FILA PRINCIPAL: Lista lateral + Gráfica principal + Filtros/resumen ═══ --}}
        <div class="grid xl:grid-cols-12 gap-4">

            {{-- PANEL IZQUIERDO: Secretarías o Unidades --}}
            <div class="xl:col-span-3 bg-white rounded-2xl border overflow-hidden flex flex-col" style="border-color:#e2e8f0">
                <div class="px-4 py-3 border-b flex items-center justify-between" style="border-color:#f1f5f9;background:#fafafa">
                    <div>
                        <p class="text-xs font-black text-gray-700 uppercase tracking-wider">
                            {{ $listaLateralTipo === 'secretarias' ? 'Secretarías' : ($listaLateralTipo === 'unidades' ? 'Unidades' : 'Alcance') }}
                        </p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Procesos activos por dependencia</p>
                    </div>
                    @if($listaLateralTipo === 'secretarias')
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:#f0fdf4;color:#15803d">{{ $listaLateral->count() }}</span>
                    @endif
                </div>
                <div class="flex-1 overflow-y-auto divide-y" style="divide-color:#f8fafc;max-height:340px">
                    @forelse($listaLateral as $item)
                    @php
                        $pct = $item->total > 0 ? min(100, round($item->en_curso / max($listaLateral->max('total'), 1) * 100)) : 0;
                        $barW = $listaLateral->max('total') > 0 ? round($item->total / $listaLateral->max('total') * 100) : 0;
                    @endphp
                    <div class="px-4 py-3 hover:bg-slate-50 transition-colors cursor-default">
                        <div class="flex items-start justify-between mb-1.5">
                            <p class="text-xs font-semibold text-gray-700 leading-tight flex-1 pr-2">{{ $item->nombre }}</p>
                            <div class="text-right shrink-0">
                                <span class="text-sm font-black text-gray-800">{{ $item->total }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-1.5 rounded-full overflow-hidden" style="background:#f1f5f9">
                                <div class="h-1.5 rounded-full transition-all" style="width:{{ $barW }}%;background:linear-gradient(90deg,#86efac,#15803d)"></div>
                            </div>
                            <span class="text-[10px] text-green-600 font-bold shrink-0">{{ $item->en_curso }} activos</span>
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                        <svg class="w-8 h-8 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                        <p class="text-xs text-gray-400">Sin dependencias con procesos</p>
                    </div>
                    @endforelse
                </div>
                {{-- Total al pie --}}
                <div class="px-4 py-2.5 border-t" style="border-color:#f1f5f9;background:#fafafa">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-gray-400 font-medium">Total procesos</span>
                        <span class="text-sm font-black text-gray-800">{{ number_format($totalProcesos) }}</span>
                    </div>
                </div>
            </div>

            {{-- GRÁFICA CENTRAL: Procesos por mes (barras agrupadas) --}}
            <div class="xl:col-span-6 bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-5 py-3 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                    <div>
                        <h2 class="text-sm font-black text-gray-800">Procesos por mes</h2>
                        <p class="text-[11px] text-gray-400 mt-0.5">Últimos 6 meses — creados, finalizados y rechazados</p>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full" style="background:#f0fdf4;color:#15803d">Tiempo real</span>
                </div>
                <div class="p-4" style="height:300px">
                    <canvas id="barMensualChart"></canvas>
                </div>
            </div>

            {{-- PANEL DERECHO: Distribución por área --}}
            <div class="xl:col-span-3 bg-white rounded-2xl border overflow-hidden flex flex-col" style="border-color:#e2e8f0">
                <div class="px-4 py-3 border-b" style="border-color:#f1f5f9;background:#fafafa">
                    <p class="text-xs font-black text-gray-700 uppercase tracking-wider">Por área</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Procesos EN CURSO por área actual</p>
                </div>
                <div class="flex-1 p-3 space-y-2">
                    @foreach($porArea as $a)
                    @php $maxArea = max(1, collect($porArea)->max('total')); @endphp
                    <div class="group">
                        <div class="flex items-center justify-between mb-1">
                            <a href="{{ $areaRoutes[$a['area']] ?? '#' }}"
                               class="text-xs font-semibold text-gray-600 hover:underline truncate">{{ $a['label'] }}</a>
                            <span class="text-xs font-black ml-2 shrink-0" style="color:{{ $a['color'] }}">{{ $a['total'] }}</span>
                        </div>
                        <div class="h-2 rounded-full overflow-hidden" style="background:#f1f5f9">
                            <div class="h-2 rounded-full transition-all"
                                 style="width:{{ $maxArea>0 ? round($a['total']/$maxArea*100) : 0 }}%;background:{{ $a['color'] }}"></div>
                        </div>
                        @if($a['alertas'] > 0)
                        <p class="text-[10px] mt-0.5" style="color:#d97706">{{ $a['alertas'] }} alerta(s)</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                {{-- Donut mini --}}
                <div class="px-4 pb-4 flex items-center justify-center">
                    <div style="width:120px;height:120px;position:relative">
                        <canvas id="donutAreaChart"></canvas>
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none">
                            <span class="text-lg font-black text-gray-800">{{ collect($porArea)->sum('total') }}</span>
                            <span class="text-[9px] text-gray-400">activos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ FILA INFERIOR: Tendencia + Modalidades + Alertas + Tabla ═══ --}}
        <div class="grid xl:grid-cols-12 gap-4">

            {{-- Gráfica línea: Tendencia --}}
            <div class="xl:col-span-5 bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-5 py-3 border-b" style="border-color:#f1f5f9">
                    <h2 class="text-sm font-black text-gray-800">Tendencia de procesos</h2>
                    <p class="text-[11px] text-gray-400 mt-0.5">Evolución mensual por estado</p>
                </div>
                <div class="p-4" style="height:220px">
                    <canvas id="lineaTendenciaChart"></canvas>
                </div>
            </div>

            {{-- Por modalidad + alertas --}}
            <div class="xl:col-span-3 bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-5 py-3 border-b" style="border-color:#f1f5f9">
                    <h2 class="text-sm font-black text-gray-800">Por modalidad</h2>
                    <p class="text-[11px] text-gray-400 mt-0.5">Tipos de contratación</p>
                </div>
                <div class="p-4 space-y-2.5">
                    @forelse($porModalidad as $m)
                    @php $maxMod = max(1, $porModalidad->max('total')); @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-600 font-medium truncate flex-1 pr-2">{{ $m->nombre }}</span>
                            <span class="text-xs font-black text-gray-800 shrink-0">{{ $m->total }}</span>
                        </div>
                        <div class="h-1.5 rounded-full overflow-hidden" style="background:#f1f5f9">
                            <div class="h-1.5 rounded-full" style="width:{{ round($m->total/$maxMod*100) }}%;background:linear-gradient(90deg,#818cf8,#4f46e5)"></div>
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center py-6 text-center">
                        <svg class="w-8 h-8 text-gray-200 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586"/></svg>
                        <p class="text-xs text-gray-400">Sin datos de modalidades</p>
                    </div>
                    @endforelse
                </div>

                {{-- Alertas y riesgos --}}
                <div class="px-4 pb-4 pt-2 border-t space-y-1.5" style="border-color:#f1f5f9">
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-wider mb-2">Centro de alertas</p>
                    @php
                    $alertasList = [
                        ['l'=>'Con retraso',       'v'=>$alertasRiesgos['procesos_con_retraso'],   'c'=>'#d97706','bg'=>'#fffbeb'],
                        ['l'=>'Docs. rechazados',  'v'=>$alertasRiesgos['documentos_rechazados'],  'c'=>'#dc2626','bg'=>'#fef2f2'],
                        ['l'=>'Sin actividad',     'v'=>$alertasRiesgos['procesos_sin_actividad'], 'c'=>'#7c3aed','bg'=>'#faf5ff'],
                        ['l'=>'Cert. por vencer',  'v'=>$alertasRiesgos['certificados_por_vencer'],'c'=>'#ea580c','bg'=>'#fff7ed'],
                    ];
                    @endphp
                    @foreach($alertasList as $al)
                    <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg" style="background:{{ $al['bg'] }}">
                        <span class="text-[11px] font-medium text-gray-600">{{ $al['l'] }}</span>
                        <span class="text-xs font-black" style="color:{{ $al['c'] }}">{{ $al['v'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Tabla de procesos recientes --}}
            <div class="xl:col-span-4 bg-white rounded-2xl border overflow-hidden flex flex-col" style="border-color:#e2e8f0">
                <div class="px-5 py-3 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                    <div>
                        <h2 class="text-sm font-black text-gray-800">Procesos recientes</h2>
                        <p class="text-[11px] text-gray-400 mt-0.5">Últimos registros del sistema</p>
                    </div>
                    <a href="{{ route('procesos.index') }}" class="text-[11px] font-bold hover:underline" style="color:#15803d">
                        Ver todos →
                    </a>
                </div>
                <div class="flex-1 overflow-y-auto divide-y" style="divide-color:#f8fafc;max-height:360px">
                    @forelse($procesosRecientes as $proc)
                    @php
                        $eCfg = $estadoConfig[$proc->estado] ?? ['label'=>$proc->estado,'color'=>'#6b7280','bg'=>'#f8fafc'];
                        $rutaProc = match($proc->area_actual_role) {
                            'planeacion' => url('/planeacion/procesos/'.$proc->id),
                            'hacienda'   => url('/hacienda/procesos/'.$proc->id),
                            'juridica'   => url('/juridica/procesos/'.$proc->id),
                            'secop'      => url('/secop/procesos/'.$proc->id),
                            default      => route('procesos.show', $proc->id),
                        };
                    @endphp
                    <a href="{{ $rutaProc }}" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors block">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5" style="background:{{ $eCfg['bg'] }}">
                            @if($proc->estado === 'EN_CURSO')
                            <svg class="w-3.5 h-3.5" fill="none" stroke="{{ $eCfg['color'] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif(in_array($proc->estado, ['FINALIZADO','completado','cerrado']))
                            <svg class="w-3.5 h-3.5" fill="none" stroke="{{ $eCfg['color'] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="{{ $eCfg['color'] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <span class="text-[10px] font-mono font-bold text-gray-500">{{ $proc->codigo }}</span>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full shrink-0" style="background:{{ $eCfg['bg'] }};color:{{ $eCfg['color'] }}">{{ $eCfg['label'] }}</span>
                            </div>
                            <p class="text-xs font-semibold text-gray-700 leading-tight truncate">{{ $proc->objeto }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($proc->updated_at)->diffForHumans() }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="flex flex-col items-center py-10 text-center">
                        <svg class="w-10 h-10 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-xs text-gray-400 font-medium">Sin procesos registrados</p>
                        @can('procesos.crear')
                        <a href="{{ route('procesos.create') }}" class="text-xs font-bold mt-1 hover:underline" style="color:#15803d">Crear primera solicitud</a>
                        @endcan
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ═══ TABLA COMPLETA DE SEGUIMIENTO ═══ --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-3 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <div>
                    <h2 class="text-sm font-black text-gray-800">Seguimiento en tiempo real</h2>
                    <p class="text-[11px] text-gray-400 mt-0.5">Ubicación y estado actual de cada proceso en el flujo contractual</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-gray-400">{{ $procesosRecientes->count() }} procesos</span>
                    <a href="{{ route('procesos.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold hover:underline" style="color:#15803d">
                        Ver todos <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:2px solid #f1f5f9">
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Código</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Objeto</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Modalidad</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Etapa actual</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Área</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actualizado</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($procesosRecientes as $proc)
                    @php
                        $eCfg2 = $estadoConfig[$proc->estado] ?? ['label'=>$proc->estado,'color'=>'#6b7280','bg'=>'#f8fafc'];
                        $areaLabel = ['unidad_solicitante'=>'Unidad','planeacion'=>'Planeación','hacienda'=>'Hacienda','juridica'=>'Jurídica','secop'=>'SECOP'][$proc->area_actual_role] ?? ucfirst(str_replace('_',' ',$proc->area_actual_role));
                        $areaColor = ['unidad_solicitante'=>'#3b82f6','planeacion'=>'#16a34a','hacienda'=>'#ca8a04','juridica'=>'#ea580c','secop'=>'#9333ea'][$proc->area_actual_role] ?? '#6b7280';
                        $areaStep  = ['unidad_solicitante'=>1,'planeacion'=>2,'hacienda'=>3,'juridica'=>4,'secop'=>5][$proc->area_actual_role] ?? 0;
                        $pct = $areaStep > 0 ? round($areaStep/5*100) : 0;
                        $rutaProc2 = match($proc->area_actual_role) {
                            'planeacion' => url('/planeacion/procesos/'.$proc->id),
                            'hacienda'   => url('/hacienda/procesos/'.$proc->id),
                            'juridica'   => url('/juridica/procesos/'.$proc->id),
                            'secop'      => url('/secop/procesos/'.$proc->id),
                            default      => route('procesos.show', $proc->id),
                        };
                    @endphp
                    <tr class="border-b hover:bg-slate-50/60 transition-colors" style="border-color:#f8fafc">
                        <td class="px-4 py-3">
                            <span class="text-xs font-mono font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">{{ $proc->codigo }}</span>
                        </td>
                        <td class="px-4 py-3 max-w-[14rem]">
                            <span class="text-xs font-medium text-gray-700 truncate block">{{ $proc->objeto }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[11px] text-gray-500">{{ $proc->workflow ?? 'N/D' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[11px] text-gray-500 truncate block max-w-[10rem]">{{ $proc->etapa ?? 'N/D' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[11px] font-semibold"
                                 style="background:{{ $areaColor }}18;color:{{ $areaColor }}">
                                <span class="w-3.5 h-3.5 rounded-full text-white flex items-center justify-center text-[8px] font-black" style="background:{{ $areaColor }}">{{ $areaStep ?: '?' }}</span>
                                {{ $areaLabel }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  style="background:{{ $eCfg2['bg'] }};color:{{ $eCfg2['color'] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $eCfg2['color'] }}"></span>
                                {{ $eCfg2['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($proc->updated_at)->diffForHumans() }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ $rutaProc2 }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all hover:shadow-sm"
                               style="color:#15803d;border-color:#bbf7d0;background:#f0fdf4">
                                Abrir →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background:#f1f5f9">
                                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-400">No hay procesos registrados</p>
                                @can('procesos.crear')
                                <a href="{{ route('procesos.create') }}" class="text-xs font-bold hover:underline" style="color:#15803d">Crear primera solicitud &rarr;</a>
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

    {{-- ═══ CHART.JS ═══ --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#94a3b8';

    // ─── 1. BAR CHART: Procesos por mes ───────────────────────────────────────
    (function () {
        const meses      = @json(collect($tendencia)->pluck('mes_corto'));
        const creados    = @json(collect($tendencia)->pluck('creados'));
        const finalizados = @json(collect($tendencia)->pluck('finalizados'));
        const rechazados = @json(collect($tendencia)->pluck('rechazados'));
        const ctx = document.getElementById('barMensualChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Creados',
                        data: creados,
                        backgroundColor: '#3b82f688',
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Finalizados',
                        data: finalizados,
                        backgroundColor: '#22c55e88',
                        borderColor: '#16a34a',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Rechazados',
                        data: rechazados,
                        backgroundColor: '#ef444488',
                        borderColor: '#dc2626',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { boxWidth: 10, boxHeight: 10, borderRadius: 4, useBorderRadius: true, font: { size: 11 } }
                    },
                    tooltip: { cornerRadius: 8 }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        grid: { color: '#f1f5f9', lineWidth: 1 },
                        border: { dash: [4, 4], color: 'transparent' },
                        ticks: { font: { size: 11 }, precision: 0, stepSize: 1 },
                        beginAtZero: true,
                    }
                }
            }
        });
    })();

    // ─── 2. DONUT: Por área ────────────────────────────────────────────────────
    (function () {
        const labels = @json(collect($porArea)->pluck('label'));
        const data   = @json(collect($porArea)->pluck('total'));
        const colors = @json(collect($porArea)->pluck('color'));
        const total  = data.reduce((a, b) => a + b, 0);
        const ctx    = document.getElementById('donutAreaChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: total > 0 ? data : [1, 1, 1, 1, 1],
                    backgroundColor: total > 0 ? colors : ['#e2e8f0', '#e2e8f0', '#e2e8f0', '#e2e8f0', '#e2e8f0'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw}`
                        }
                    }
                }
            }
        });
    })();

    // ─── 3. LINE: Tendencia ────────────────────────────────────────────────────
    (function () {
        const meses       = @json(collect($tendencia)->pluck('mes_corto'));
        const creados     = @json(collect($tendencia)->pluck('creados'));
        const finalizados = @json(collect($tendencia)->pluck('finalizados'));
        const rechazados  = @json(collect($tendencia)->pluck('rechazados'));
        const ctx = document.getElementById('lineaTendenciaChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Creados',
                        data: creados,
                        borderColor: '#3b82f6',
                        backgroundColor: '#3b82f612',
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#3b82f6',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Finalizados',
                        data: finalizados,
                        borderColor: '#16a34a',
                        backgroundColor: '#16a34a08',
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: false,
                        pointBackgroundColor: '#16a34a',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Rechazados',
                        data: rechazados,
                        borderColor: '#dc2626',
                        backgroundColor: '#dc262608',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: false,
                        borderDash: [5, 3],
                        pointBackgroundColor: '#dc2626',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { boxWidth: 10, boxHeight: 10, borderRadius: 4, useBorderRadius: true, font: { size: 10 } }
                    },
                    tooltip: { cornerRadius: 8 }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        border: { dash: [4, 4], color: 'transparent' },
                        ticks: { font: { size: 10 }, precision: 0, stepSize: 1 },
                        beginAtZero: true,
                    }
                }
            }
        });
    })();
    </script>

</x-app-layout>
