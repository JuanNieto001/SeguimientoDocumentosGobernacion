<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-gray-900 leading-none">Centro de reportes</h1>
            <p class="text-xs text-gray-400 mt-1">Exportables y análisis — Gobernación de Caldas</p>
        </div>
    </x-slot>

    <div class="p-6 space-y-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- Sección PAA --}}
        <div>
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#fff7ed">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Plan Anual de Adquisiciones (PAA)</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @php $anioActual = date('Y'); $anios = [$anioActual-1, $anioActual, $anioActual+1]; @endphp
                @foreach($anios as $a)
                <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-base font-bold text-gray-900">PAA {{ $a }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $a == $anioActual ? 'Vigencia actual' : ($a < $anioActual ? 'Año anterior' : 'Año siguiente') }}</p>
                        </div>
                        @if($a == $anioActual)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full" style="background:#dcfce7;color:#15803d">Activo</span>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('paa.exportar.csv', ['anio' => $a]) }}"
                           class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors"
                           style="background:#dcfce7;color:#15803d">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            CSV
                        </a>
                        <a href="{{ route('paa.exportar.pdf', ['anio' => $a]) }}" target="_blank"
                           class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors"
                           style="background:#fee2e2;color:#b91c1c">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            PDF
                        </a>
                        <a href="{{ route('paa.index', ['anio' => $a]) }}"
                           class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors"
                           style="background:#dbeafe;color:#1d4ed8">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Ver
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Sección Procesos --}}
        <div>
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#dbeafe">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Reportes de Procesos</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                $reportesProc = [
                    [
                        'titulo'   => 'Estado general',
                        'desc'     => 'Todos los procesos con su estado, etapa actual y área responsable',
                        'route'    => 'reportes.estado.general',
                        'bg'       => '#dbeafe', 'text' => '#1d4ed8',
                        'icon'     => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    ],
                    [
                        'titulo'   => 'Por dependencia',
                        'desc'     => 'Procesos agrupados por secretaría y unidad solicitante',
                        'route'    => 'reportes.por.dependencia',
                        'bg'       => '#f0fdf4', 'text' => '#15803d',
                        'icon'     => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    ],
                    [
                        'titulo'   => 'Eficiencia y tiempos',
                        'desc'     => 'Tiempos promedio por etapa, días de retraso y cuellos de botella',
                        'route'    => 'reportes.eficiencia',
                        'bg'       => '#fff7ed', 'text' => '#c2410c',
                        'icon'     => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    ],
                    [
                        'titulo'   => 'Actividad por actor',
                        'desc'     => 'Acciones realizadas por cada usuario: recibos, envíos, aprobaciones',
                        'route'    => 'reportes.actividad.actor',
                        'bg'       => '#fdf4ff', 'text' => '#7e22ce',
                        'icon'     => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    ],
                    [
                        'titulo'   => 'Certificados por vencer',
                        'desc'     => 'Documentos con vigencia próxima a expirar (menos de 5 días)',
                        'route'    => 'reportes.certificados.vencer',
                        'bg'       => '#fef2f2', 'text' => '#b91c1c',
                        'icon'     => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    ],
                    [
                        'titulo'   => 'Ver todos los procesos',
                        'desc'     => 'Lista completa de procesos con filtros avanzados',
                        'route'    => 'procesos.index',
                        'bg'       => '#f1f5f9', 'text' => '#475569',
                        'icon'     => 'M4 6h16M4 10h16M4 14h16M4 18h16',
                    ],
                ];
                @endphp
                @foreach($reportesProc as $rep)
                <a href="{{ route($rep['route']) }}"
                   class="bg-white rounded-2xl p-5 flex flex-col gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all"
                   style="border:1px solid #e2e8f0">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $rep['bg'] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:{{ $rep['text'] }}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $rep['icon'] }}"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $rep['titulo'] }}</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $rep['desc'] }}</p>
                    </div>
                    <div class="flex items-center gap-1 text-xs font-semibold mt-auto" style="color:{{ $rep['text'] }}">
                        Ver reporte
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Sección Alertas --}}
        @role('admin|planeacion')
        <div>
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#fee2e2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Centro de Alertas</h2>
            </div>
            <div class="bg-white rounded-2xl p-5 flex items-center justify-between" style="border:1px solid #e2e8f0">
                <div class="flex items-center gap-3">
                    @php $totalAlertas = \App\Models\Alerta::where('leida', false)->count(); @endphp
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $totalAlertas > 0 ? '#fee2e2' : '#f0fdf4' }}">
                        <span class="text-base font-bold" style="color:{{ $totalAlertas > 0 ? '#b91c1c' : '#15803d' }}">{{ $totalAlertas }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $totalAlertas }} alerta(s) sin leer</p>
                        <p class="text-xs text-gray-400">Generadas automáticamente por el sistema</p>
                    </div>
                </div>
                <a href="{{ route('alertas.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-xl transition-colors"
                   style="background:#fee2e2;color:#b91c1c">
                    Ver todas las alertas →
                </a>
            </div>
        </div>
        @endrole

    </div>
</x-app-layout>
