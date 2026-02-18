<x-app-layout>
    {{-- Chart.js CDN --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Panel Administrativo</h1>
                <p class="text-xs text-gray-400 mt-1">Gobernación de Caldas &mdash; Sistema de Contratación Pública</p>
            </div>
            <div class="flex items-center gap-3 ml-8">
                <a href="{{ route('procesos.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold shadow-sm transition-all hover:shadow-md hover:opacity-95"
                   style="background:linear-gradient(135deg,#15803d,#14532d)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Nueva solicitud
                </a>
                <a href="{{ route('procesos.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-gray-600 text-sm font-medium bg-white border hover:bg-gray-50 transition-all"
                   style="border-color:#e2e8f0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Ver todos
                </a>
            </div>
        </div>
    </x-slot>

    @php
    $areaLabels = ['unidad_solicitante'=>'Unidad Solicitante','planeacion'=>'Planeación','hacienda'=>'Hacienda','juridica'=>'Jurídica','secop'=>'SECOP'];
    $areaColors = ['unidad_solicitante'=>'#3b82f6','planeacion'=>'#16a34a','hacienda'=>'#ca8a04','juridica'=>'#ea580c','secop'=>'#9333ea'];
    $areaBg     = ['unidad_solicitante'=>'#eff6ff','planeacion'=>'#f0fdf4','hacienda'=>'#fefce8','juridica'=>'#fff7ed','secop'=>'#fdf4ff'];
    $areaConfig = [
        'unidad_solicitante'=>['color'=>'#3b82f6','bg'=>'#eff6ff','step'=>'1'],
        'planeacion'        =>['color'=>'#16a34a','bg'=>'#f0fdf4','step'=>'2'],
        'hacienda'          =>['color'=>'#ca8a04','bg'=>'#fefce8','step'=>'3'],
        'juridica'          =>['color'=>'#ea580c','bg'=>'#fff7ed','step'=>'4'],
        'secop'             =>['color'=>'#9333ea','bg'=>'#fdf4ff','step'=>'5'],
    ];
    $maxProcesos = max(1, collect($estadisticasArea??[])->max('total'));

    // Datos para gráfica de donut (áreas)
    $donutLabels = [];
    $donutData   = [];
    $donutColors = [];
    foreach(($estadisticasArea??[]) as $area => $datos) {
        $donutLabels[] = $areaLabels[$area] ?? ucfirst(str_replace('_',' ',$area));
        $donutData[]   = $datos['total'] ?? 0;
        $donutColors[] = $areaColors[$area] ?? '#94a3b8';
    }
    // Si no hay datos reales, mostrar placeholders
    if(array_sum($donutData) === 0) {
        $donutData = [1,1,1,1,1];
    }
    $donutColorsSafe = !empty($donutColors) ? array_values($donutColors) : ['#3b82f6','#16a34a','#ca8a04','#ea580c','#9333ea'];
    $donutLabelsSafe = !empty($donutLabels) ? $donutLabels : ['Unidad','Planeación','Hacienda','Jurídica','SECOP'];
    $barTotals = collect($estadisticasArea??[])->pluck('total')->values()->toArray();
    $barPendientes = collect($estadisticasArea??[])->pluck('documentos_pendientes')->values()->toArray();
    @endphp

    <div class="p-6 space-y-5">

        {{-- ═══ KPI ROW ═══ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
            $kpis = [
                ['label'=>'Total procesos',  'value'=>$indicadores['total_procesos']??0,         'sub'=>'Registrados','color'=>'#2563eb','bg'=>'#eff6ff','border'=>'#bfdbfe',
                 'trend'=>'+0%','trendUp'=>true,
                 'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['label'=>'En curso',         'value'=>$indicadores['procesos_activos']??0,       'sub'=>'Activos ahora','color'=>'#0891b2','bg'=>'#ecfeff','border'=>'#a5f3fc',
                 'trend'=>'Activo','trendUp'=>true,
                 'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label'=>'Finalizados',      'value'=>$indicadores['procesos_finalizados']??0,   'sub'=>'Completados','color'=>'#15803d','bg'=>'#f0fdf4','border'=>'#bbf7d0',
                 'trend'=>'Éxito','trendUp'=>true,
                 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label'=>'Alertas activas',  'value'=>$indicadores['alertas_alta_prioridad']??0, 'sub'=>'Alta prioridad','color'=>'#dc2626','bg'=>'#fef2f2','border'=>'#fecaca',
                 'trend'=>'Revisar','trendUp'=>false,
                 'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ];
            @endphp
            @foreach($kpis as $k)
            <div class="bg-white rounded-2xl p-5 border relative overflow-hidden hover:shadow-lg transition-all duration-300 cursor-default group" style="border-color:{{ $k['border'] }}">
                <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full opacity-[0.06] group-hover:opacity-[0.1] transition-opacity" style="background:{{ $k['color'] }}"></div>
                <div class="absolute -right-2 -top-2 w-12 h-12 rounded-full opacity-[0.04]" style="background:{{ $k['color'] }}"></div>
                <div class="relative flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $k['bg'] }}">
                        <svg class="w-5 h-5" fill="none" stroke="{{ $k['color'] }}" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                          style="background:{{ $k['bg'] }};color:{{ $k['color'] }}">{{ $k['trend'] }}</span>
                </div>
                <p class="text-[2.4rem] font-black leading-none tracking-tight" style="color:{{ $k['color'] }}">{{ $k['value'] }}</p>
                <p class="text-xs font-bold text-gray-700 mt-2">{{ $k['label'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $k['sub'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- ═══ FILA GRÁFICAS ═══ --}}
        <div class="grid lg:grid-cols-5 gap-4">

            {{-- Gráfica de Donut: Distribución por área --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-6 py-4 border-b" style="border-color:#f1f5f9">
                    <h2 class="text-sm font-bold text-gray-800">Distribución por área</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Concentración de procesos activos</p>
                </div>
                <div class="p-5">
                    <div class="relative flex items-center justify-center" style="height:180px">
                        <canvas id="donutChart"></canvas>
                        <div class="absolute text-center pointer-events-none">
                            <p class="text-2xl font-black text-gray-800">{{ collect($estadisticasArea??[])->sum('total') }}</p>
                            <p class="text-[10px] text-gray-400 font-medium">procesos</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2">
                        @foreach($estadisticasArea??[] as $area=>$datos)
                        @php $cfg = $areaConfig[$area]??['color'=>'#6b7280','bg'=>'#f8fafc','step'=>'?']; $lbl = $areaLabels[$area]??$area; @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $cfg['color'] }}"></span>
                                <span class="text-xs text-gray-600">{{ $lbl }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(($datos['documentos_pendientes']??0)>0)
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold" style="background:#fefce8;color:#a16207">{{ $datos['documentos_pendientes'] }} pend.</span>
                                @endif
                                <span class="text-xs font-bold" style="color:{{ $cfg['color'] }}">{{ $datos['total']??0 }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Gráfica de barras: Procesos por estado --}}
            <div class="lg:col-span-3 bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Estado del flujo contractual</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Avance por área en el proceso</p>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full" style="background:#f0fdf4;color:#15803d">
                        Tiempo real
                    </span>
                </div>
                <div class="p-5" style="height:260px">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>

        {{-- ═══ ALERTAS + EFICIENCIA ═══ --}}
        <div class="grid lg:grid-cols-3 gap-4">

            {{-- Alertas --}}
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-6 py-4 border-b" style="border-color:#f1f5f9">
                    <h2 class="text-sm font-bold text-gray-800">Centro de alertas</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Situaciones que requieren atención</p>
                </div>
                <div class="p-4 space-y-2.5">
                    @php
                    $alertasList = [
                        ['l'=>'Procesos con retraso',  'v'=>$alertasRiesgos['procesos_con_retraso']??0,     'c'=>'#d97706','bg'=>'#fffbeb','b'=>'#fde68a','i'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['l'=>'Documentos rechazados', 'v'=>$alertasRiesgos['documentos_rechazados']??0,    'c'=>'#dc2626','bg'=>'#fef2f2','b'=>'#fecaca','i'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['l'=>'Sin actividad (7 días)','v'=>$alertasRiesgos['procesos_sin_actividad']??0,   'c'=>'#7c3aed','bg'=>'#faf5ff','b'=>'#e9d5ff','i'=>'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                        ['l'=>'Certificados por vencer','v'=>$alertasRiesgos['certificados_por_vencer']??0,'c'=>'#ea580c','bg'=>'#fff7ed','b'=>'#fed7aa','i'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                    ];
                    @endphp
                    @foreach($alertasList as $al)
                    <div class="flex items-center gap-3 p-3 rounded-xl border transition-all hover:shadow-sm" style="background:{{ $al['bg'] }};border-color:{{ $al['b'] }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $al['c'] }}22">
                            <svg class="w-4 h-4" fill="none" stroke="{{ $al['c'] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $al['i'] }}"/></svg>
                        </div>
                        <span class="flex-1 text-xs font-medium text-gray-700 leading-tight">{{ $al['l'] }}</span>
                        <div class="flex flex-col items-end">
                            <span class="text-lg font-black leading-none" style="color:{{ $al['c'] }}">{{ $al['v'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Gráfica Gauge / Eficiencia --}}
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-6 py-4 border-b" style="border-color:#f1f5f9">
                    <h2 class="text-sm font-bold text-gray-800">Eficiencia del proceso</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Indicadores clave de rendimiento</p>
                </div>
                <div class="p-5">
                    <div class="flex items-center justify-center" style="height:140px">
                        <canvas id="gaugeChart"></canvas>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div class="rounded-xl p-3 text-center" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7)">
                            <p class="text-2xl font-black leading-none" style="color:#14532d">
                                {{ number_format($eficiencia['promedio_general_dias']??0,1) }}
                            </p>
                            <p class="text-[10px] text-gray-500 mt-1 leading-tight">días promedio</p>
                        </div>
                        <div class="rounded-xl p-3 text-center" style="background:linear-gradient(135deg,#eff6ff,#dbeafe)">
                            <p class="text-2xl font-black leading-none" style="color:#1e3a8a">
                                {{ $eficiencia['procesos_finalizados_3meses']??0 }}
                            </p>
                            <p class="text-[10px] text-gray-500 mt-1 leading-tight">finalizados (3m)</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Etapas --}}
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-6 py-4 border-b" style="border-color:#f1f5f9">
                    <h2 class="text-sm font-bold text-gray-800">Procesos por etapa</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Concentración actual del flujo</p>
                </div>
                <div class="p-5 space-y-3">
                    @forelse($estadisticasEtapa['distribucion']??[] as $i=>$item)
                    @php $etapaColors=['#3b82f6','#16a34a','#ca8a04','#ea580c','#9333ea','#0891b2']; $ec=$etapaColors[$i%6]; @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-black text-white shrink-0" style="background:{{ $ec }}">{{ $item->total }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-700 truncate leading-none">{{ $item->etapa }}</p>
                            <p class="text-[10px] text-gray-400 truncate mt-0.5">{{ $item->workflow }}</p>
                        </div>
                        <div class="w-16 h-1.5 rounded-full overflow-hidden shrink-0" style="background:#f1f5f9">
                            <div class="h-1.5 rounded-full" style="width:{{ min(100,($item->total??0)*15) }}%;background:{{ $ec }}"></div>
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center gap-2 py-6">
                        <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-xs text-gray-400 text-center">No hay procesos<br>en etapas activas</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ═══ TABLA SEGUIMIENTO ═══ --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <div>
                    <h2 class="text-sm font-bold text-gray-800">Seguimiento en tiempo real</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Ubicación y estado actual de cada proceso en el flujo</p>
                </div>
                <a href="{{ route('procesos.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold hover:underline" style="color:#15803d">
                    Ver todos <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:2px solid #f1f5f9">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Código</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Objeto</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Área</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Etapa</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Progreso</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Actualizado</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($seguimientoProcesos??[] as $proc)
                    @php
                        $cfg2  = $areaConfig[$proc->area_actual_role] ?? ['color'=>'#6b7280','bg'=>'#f8fafc','step'=>'?'];
                        $lbl2  = $areaLabels[$proc->area_actual_role] ?? ucfirst(str_replace('_',' ',$proc->area_actual_role));
                        $ruta  = match($proc->area_actual_role) {
                            'unidad_solicitante' => url('/unidad?proceso_id='.$proc->id),
                            'planeacion'         => url('/planeacion/procesos/'.$proc->id),
                            'hacienda'           => url('/hacienda/procesos/'.$proc->id),
                            'juridica'           => url('/juridica/procesos/'.$proc->id),
                            'secop'              => url('/secop/procesos/'.$proc->id),
                            default              => route('procesos.index'),
                        };
                        // Progreso basado en el paso del área (1-5)
                        $paso = (int)$cfg2['step'];
                        $pct  = round($paso / 5 * 100);
                        if($proc->enviado)      { $badge=['txt'=>'Enviado',  'bg'=>'#eff6ff','c'=>'#1d4ed8','dot'=>'#3b82f6']; }
                        elseif($proc->recibido) { $badge=['txt'=>'Recibido', 'bg'=>'#f0fdf4','c'=>'#15803d','dot'=>'#22c55e']; }
                        else                    { $badge=['txt'=>'Pendiente','bg'=>'#fefce8','c'=>'#a16207','dot'=>'#eab308']; }
                    @endphp
                    <tr class="border-b hover:bg-slate-50/70 transition-colors" style="border-color:#f8fafc">
                        <td class="px-5 py-4">
                            <span class="text-xs font-mono font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-md">{{ $proc->codigo }}</span>
                        </td>
                        <td class="px-5 py-4 max-w-[15rem]">
                            <span class="block text-sm font-medium text-gray-700 truncate">{{ $proc->objeto }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold"
                                 style="background:{{ $cfg2['bg'] }};color:{{ $cfg2['color'] }}">
                                <span class="w-4 h-4 rounded-full text-white flex items-center justify-center text-[9px] font-black shrink-0"
                                      style="background:{{ $cfg2['color'] }}">{{ $cfg2['step'] }}</span>
                                {{ $lbl2 }}
                            </div>
                        </td>
                        <td class="px-5 py-4 max-w-[12rem]">
                            <span class="text-xs text-gray-500 truncate block">{{ $proc->etapa ?? 'N/D' }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center gap-2 justify-center">
                                <div class="w-20 h-1.5 rounded-full overflow-hidden" style="background:#f1f5f9">
                                    <div class="h-1.5 rounded-full transition-all" style="width:{{ $pct }}%;background:linear-gradient(90deg,#86efac,#15803d)"></div>
                                </div>
                                <span class="text-[10px] font-bold text-gray-500 w-7">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold"
                                  style="background:{{ $badge['bg'] }};color:{{ $badge['c'] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $badge['dot'] }}"></span>
                                {{ $badge['txt'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs text-gray-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($proc->updated_at)->diffForHumans() }}
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ $ruta }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all hover:shadow-sm"
                               style="color:#15803d;border-color:#bbf7d0;background:#f0fdf4">
                                Abrir
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background:#f1f5f9">
                                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-400">No hay procesos registrados</p>
                                <a href="{{ route('procesos.create') }}" class="text-xs font-bold hover:underline" style="color:#15803d">Crear primera solicitud &rarr;</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ═══ CHART.JS SCRIPTS ═══ --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // ── DONUT: Distribución por área ──
    (function() {
        const labels = @json($donutLabelsSafe);
        const data   = @json($donutData);
        const colors = @json($donutColorsSafe);
        const total  = data.reduce((a,b)=>a+b,0);
        const ctx = document.getElementById('donutChart');
        if(!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} (${total>0?Math.round(ctx.raw/total*100):0}%)`
                        }
                    }
                }
            }
        });
    })();

    // ── BAR: Estado del flujo contractual ──
    (function() {
        const labels    = @json($donutLabelsSafe);
        const colors    = @json($donutColorsSafe);
        const totals    = @json($barTotals);
        const pendientes = @json($barPendientes);
        const ctx = document.getElementById('barChart');
        if(!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Procesos',
                        data: totals,
                        backgroundColor: colors.map(c => c + 'cc'),
                        borderColor: colors,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    },
                    {
                        label: 'Docs. pendientes',
                        data: pendientes,
                        backgroundColor: '#fde68a99',
                        borderColor: '#ca8a04',
                        borderWidth: 1.5,
                        borderRadius: 8,
                        borderSkipped: false,
                    }
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
                    tooltip: { cornerRadius: 10 }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        grid: { color: '#f1f5f9', lineWidth: 1 },
                        border: { dash: [4,4] },
                        ticks: { font: { size: 11 }, stepSize: 1, precision: 0 },
                        beginAtZero: true,
                    }
                }
            }
        });
    })();

    // ── GAUGE: Eficiencia (doughnut semicircular) ──
    (function() {
        const dias     = {{ $eficiencia['promedio_general_dias'] ?? 0 }};
        const maxDias  = 60;
        const pct      = Math.min(100, Math.round(dias / maxDias * 100));
        const remaining = 100 - pct;
        const ctx = document.getElementById('gaugeChart');
        if(!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [pct, remaining],
                    backgroundColor: [
                        pct < 40 ? '#16a34a' : pct < 70 ? '#ca8a04' : '#dc2626',
                        '#f1f5f9'
                    ],
                    borderWidth: 0,
                    circumference: 180,
                    rotation: 270,
                    cutout: '78%',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            },
            plugins: [{
                id: 'gaugeLabel',
                afterDraw(chart) {
                    const { ctx: c, chartArea: { top, width, height } } = chart;
                    c.save();
                    c.textAlign = 'center';
                    c.fillStyle = '#1e293b';
                    c.font = 'bold 20px Inter';
                    c.fillText(dias.toFixed(1) + ' d', chart.width / 2, top + height * 0.72);
                    c.fillStyle = '#94a3b8';
                    c.font = '10px Inter';
                    c.fillText('días promedio', chart.width / 2, top + height * 0.88);
                    c.restore();
                }
            }]
        });
    })();
    </script>
</x-app-layout>
