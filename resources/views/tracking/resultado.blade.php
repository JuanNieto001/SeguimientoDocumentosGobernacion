<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tracking.index') }}"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none font-mono">{{ $codigo }}</h1>
                <p class="text-xs text-gray-400 mt-1">Trazabilidad del proceso</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f0fdf4;min-height:calc(100vh - 65px)">

        {{-- ╔══════════════════════════════════════════════════════╗ --}}
        {{-- ║  TARJETA PRINCIPAL DEL PROCESO                      ║ --}}
        {{-- ╚══════════════════════════════════════════════════════╝ --}}
        @php
            $estadoBadge = match($proceso->estado) {
                'EN_CURSO'   => ['bg'=>'#dcfce7','c'=>'#15803d','dot'=>'#22c55e'],
                'FINALIZADO' => ['bg'=>'#dbeafe','c'=>'#1d4ed8','dot'=>'#3b82f6'],
                'EN_ESPERA'  => ['bg'=>'#fef3c7','c'=>'#b45309','dot'=>'#f59e0b'],
                default      => ['bg'=>'#f1f5f9','c'=>'#64748b','dot'=>'#94a3b8'],
            };
            $creador    = optional($proceso->creador);
            $secretaria = optional($proceso->secretariaOrigen)->nombre ?? null;
            $unidad     = optional($proceso->unidadOrigen)->nombre ?? null;
            $initials   = strtoupper(substr($creador->name ?? 'S', 0, 1)).(strlen($creador->name ?? '') > 1 ? strtoupper(substr(explode(' ', $creador->name ?? 'S')[1] ?? $creador->name ?? 'S', 0, 1)) : '');
        @endphp

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #bbf7d0">

            {{-- Header de la tarjeta --}}
            <div class="px-6 py-4 flex items-center justify-between"
                 style="background:linear-gradient(135deg,#14532d 0%,#15803d 60%,#16a34a 100%)">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl"
                         style="background:rgba(255,255,255,0.15)">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium" style="color:rgba(255,255,255,0.65)">Código del proceso</p>
                        <p class="text-base font-bold text-white font-mono tracking-wide">{{ $proceso->codigo }}</p>
                    </div>
                </div>
                <span class="px-3 py-1.5 rounded-full text-xs font-bold"
                      style="background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.3)">
                    {{ $proceso->estado }}
                </span>
            </div>

            {{-- Barra de progreso --}}
            @php
                $barColor = $porcentaje >= 100 ? '#22c55e' : ($porcentaje >= 60 ? '#16a34a' : ($porcentaje >= 30 ? '#f59e0b' : '#ef4444'));
            @endphp
            <div class="px-6 py-3" style="background:#f0fdf4;border-bottom:1px solid #dcfce7">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold" style="color:#15803d">Avance del proceso</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">Etapa {{ $ordenActual }} de {{ $totalEtapas }}</span>
                        <span class="text-sm font-black" style="color:{{ $barColor }}">{{ $porcentaje }}%</span>
                    </div>
                </div>
                <div class="w-full rounded-full overflow-hidden" style="background:#dcfce7;height:10px">
                    <div class="h-full rounded-full transition-all"
                         style="width:{{ $porcentaje }}%;background:linear-gradient(90deg,#16a34a,{{ $barColor }});
                                box-shadow:0 0 8px {{ $barColor }}66">
                    </div>
                </div>
                {{-- Mini etapas --}}
                @if(optional($proceso->workflow)->etapas && $proceso->workflow->etapas->count() > 0)
                <div class="flex items-center mt-2 gap-0.5">
                    @foreach($proceso->workflow->etapas->sortBy('orden') as $etapa)
                    @php
                        $done    = $etapa->orden < $ordenActual;
                        $current = $etapa->orden === $ordenActual;
                    @endphp
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full h-1.5 rounded-full"
                             style="background:{{ $done ? '#16a34a' : ($current ? '#22c55e' : '#bbf7d0') }}"></div>
                        @if($current)
                        <span class="text-center mt-1 leading-tight"
                              style="font-size:9px;color:#15803d;font-weight:700">{{ $etapa->nombre }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Cuerpo principal --}}
            <div class="p-6 grid sm:grid-cols-2 md:grid-cols-3 gap-5">
                <div class="md:col-span-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Objeto del contrato</p>
                    <p class="text-sm font-medium text-gray-800 leading-relaxed">{{ $proceso->objeto }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Etapa actual</p>
                    <p class="text-sm font-semibold text-gray-800">{{ optional($proceso->etapaActual)->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Área responsable</p>
                    <p class="text-sm text-gray-700">{{ $proceso->area_actual_role ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Valor estimado</p>
                    <p class="text-sm font-bold" style="color:#1d4ed8">
                        ${{ number_format($proceso->valor_estimado ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                @if($proceso->contratista_nombre)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Contratista</p>
                    <p class="text-sm text-gray-700">{{ $proceso->contratista_nombre }}</p>
                </div>
                @endif
            </div>

            {{-- Franja inferior: quien creó el proceso --}}
            <div class="px-6 py-4 flex flex-wrap items-center gap-5"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0">
                <div class="flex items-center gap-3">
                    {{-- Avatar iniciales --}}
                    <div class="flex items-center justify-center w-9 h-9 rounded-full text-white text-xs font-bold shrink-0"
                         style="background:linear-gradient(135deg,#15803d,#16a34a)">
                        {{ $initials }}
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Creado por</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $creador->name ?? '—' }}</p>
                        @if($creador->email)
                        <p class="text-xs text-gray-400 font-mono">{{ $creador->email }}</p>
                        @endif
                    </div>
                </div>
                @if($secretaria || $unidad)
                <div class="h-8 w-px" style="background:#e2e8f0"></div>
                <div>
                    <p class="text-xs text-gray-400">Unidad solicitante</p>
                    @if($secretaria)
                    <p class="text-sm font-medium text-gray-700">{{ $secretaria }}</p>
                    @endif
                    @if($unidad)
                    <p class="text-xs text-gray-500">{{ $unidad }}</p>
                    @endif
                </div>
                @endif
                <div class="h-8 w-px" style="background:#e2e8f0"></div>
                <div>
                    <p class="text-xs text-gray-400">Fecha de creación</p>
                    <p class="text-sm font-semibold text-gray-700">
                        {{ \Carbon\Carbon::parse($proceso->created_at)->translatedFormat('d \d\e F \d\e Y') }}
                    </p>
                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($proceso->created_at)->format('H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- ╔══════════════════════════════════════════════════════╗ --}}
        {{-- ║  HISTORIAL COMPLETO                                 ║ --}}
        {{-- ╚══════════════════════════════════════════════════════╝ --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #bbf7d0">

            <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color:#dcfce7">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg"
                         style="background:linear-gradient(135deg,#15803d,#16a34a)">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Historial completo del proceso</h3>
                        <p class="text-xs text-gray-400">Todas las acciones, usuarios y cambios de estado</p>
                    </div>
                </div>
                @if($timeline->isNotEmpty())
                <span class="px-3 py-1 rounded-full text-xs font-bold"
                      style="background:linear-gradient(135deg,#15803d,#16a34a);color:#fff">
                    {{ $timeline->count() }} eventos
                </span>
                @endif
            </div>

            @if($timeline->isEmpty())
            <div class="px-5 py-14 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4"
                     style="background:#f1f5f9">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-500">Sin eventos registrados</p>
                <p class="text-xs text-gray-400 mt-1">El historial se construye automáticamente con cada acción.</p>
            </div>
            @else
            <div class="px-6 py-6">
                <ol class="relative space-y-4" style="border-left:3px solid #bbf7d0;margin-left:1rem">
                    @foreach($timeline as $i => $ev)
                    @php
                        $isLast = $i === $timeline->count() - 1;

                        // Icono SVG según tipo
                        $iconPath = match($ev['tipo']) {
                            'proceso_creado'      => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                            'etapa_avanzada'      => 'M13 5l7 7-7 7M5 5l7 7-7 7',
                            'documento_subido'    => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
                            'proceso_aprobado'    => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            'proceso_rechazado'   => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                            'proceso_devuelto'    => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
                            'proceso_finalizado'  => 'M5 13l4 4L19 7',
                            'revision_solicitada' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                            'entrega'             => 'M5 10l7-7m0 0l7 7m-7-7v18',
                            'recepcion'           => 'M19 14l-7 7m0 0l-7-7m7 7V3',
                            default               => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        };

                        // Colores de borde izquierdo de la card
                        $borderAccent = match($ev['tipo']) {
                            'proceso_creado'      => '#22c55e',
                            'etapa_avanzada'      => '#0d9488',
                            'documento_subido'    => '#a855f7',
                            'proceso_aprobado'    => '#10b981',
                            'proceso_rechazado'   => '#ef4444',
                            'proceso_devuelto'    => '#f97316',
                            'proceso_finalizado'  => '#16a34a',
                            'revision_solicitada' => '#f59e0b',
                            'entrega'             => '#3b82f6',
                            'recepcion'           => '#14b8a6',
                            default               => '#16a34a',
                        };

                        // Iniciales del usuario
                        $parts    = explode(' ', trim($ev['usuario']));
                        $uInit    = strtoupper(substr($parts[0] ?? '?', 0, 1)).(isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '');

                        // Color del avatar según tipo
                        $avatarGrad = match($ev['tipo']) {
                            'proceso_creado'      => 'linear-gradient(135deg,#15803d,#16a34a)',
                            'etapa_avanzada'      => 'linear-gradient(135deg,#0f766e,#0d9488)',
                            'documento_subido'    => 'linear-gradient(135deg,#9333ea,#a855f7)',
                            'proceso_aprobado'    => 'linear-gradient(135deg,#059669,#10b981)',
                            'proceso_rechazado'   => 'linear-gradient(135deg,#dc2626,#ef4444)',
                            'proceso_devuelto'    => 'linear-gradient(135deg,#ea580c,#f97316)',
                            'proceso_finalizado'  => 'linear-gradient(135deg,#15803d,#22c55e)',
                            'revision_solicitada' => 'linear-gradient(135deg,#d97706,#f59e0b)',
                            'entrega'             => 'linear-gradient(135deg,#1d4ed8,#3b82f6)',
                            'recepcion'           => 'linear-gradient(135deg,#14532d,#15803d)',
                            default               => 'linear-gradient(135deg,#15803d,#16a34a)',
                        };

                        // Tiempo relativo
                        $tiempoRelativo = $ev['fecha']->diffForHumans();
                    @endphp

                    <li class="ml-8 relative">
                        {{-- Dot sobre la línea --}}
                        <span class="absolute flex items-center justify-center w-9 h-9 rounded-full ring-4"
                              style="background:{{ $ev['dot'] }};ring-color:#f0f4f8;left:-4.25rem;top:0.5rem;
                                     box-shadow:0 0 0 4px #f0f4f8, 0 2px 8px {{ $ev['dot'] }}55">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
                            </svg>
                        </span>

                        {{-- Card con borde izquierdo de color --}}
                        <div class="rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow"
                             style="border:1px solid #e8edf5">

                            {{-- Franja superior coloreada + título + fecha --}}
                            <div class="flex items-center justify-between px-4 py-2.5"
                                 style="background:{{ $ev['bg'] }};border-left:4px solid {{ $borderAccent }}">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold" style="color:{{ $ev['color'] }}">
                                        {{ strtoupper($ev['label']) }}
                                    </span>
                                    @if(!empty($ev['etapa']))
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium"
                                          style="background:rgba(0,0,0,0.06);color:{{ $ev['color'] }}">
                                        {{ $ev['etapa'] }}
                                    </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs" style="color:{{ $ev['color'] }};opacity:0.7">
                                        {{ $tiempoRelativo }}
                                    </span>
                                    <span class="text-xs font-semibold" style="color:{{ $ev['color'] }}">
                                        {{ $ev['fecha']->format('d/m/Y') }}
                                    </span>
                                    <span class="text-xs font-mono" style="color:{{ $ev['color'] }};opacity:0.8">
                                        {{ $ev['fecha']->format('H:i:s') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Cuerpo de la card --}}
                            <div class="px-4 py-3" style="background:#ffffff;border-left:4px solid {{ $borderAccent }}">

                                {{-- Usuario --}}
                                <div class="flex items-center gap-2.5 mb-2">
                                    <div class="flex items-center justify-center w-7 h-7 rounded-full text-white text-xs font-bold shrink-0"
                                         style="background:{{ $avatarGrad }}">
                                        {{ $uInit }}
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold text-gray-800">{{ $ev['usuario'] }}</span>
                                        @if(!empty($ev['ip']))
                                        <span class="text-gray-300 mx-1">·</span>
                                        <span class="text-xs text-gray-400 font-mono">{{ $ev['ip'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Área origen → destino (movimientos físicos) --}}
                                @if(!empty($ev['area_origen']) || !empty($ev['area_destino']))
                                <div class="flex items-center gap-2 mb-2">
                                    @if(!empty($ev['area_origen']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold"
                                          style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        {{ $ev['area_origen'] }}
                                    </span>
                                    @endif
                                    @if(!empty($ev['area_origen']) && !empty($ev['area_destino']))
                                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    @endif
                                    @if(!empty($ev['area_destino']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold"
                                          style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                        {{ $ev['area_destino'] }}
                                    </span>
                                    @endif
                                </div>
                                @endif

                                {{-- Detalle / observaciones --}}
                                @if(!empty($ev['detalle']))
                                <div class="flex items-start gap-2 mt-1.5">
                                    <svg class="w-3.5 h-3.5 mt-0.5 shrink-0" style="color:{{ $borderAccent }};opacity:0.6"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    <p class="text-xs text-gray-500 leading-relaxed">{{ $ev['detalle'] }}</p>
                                </div>
                                @endif

                            </div>
                        </div>
                    </li>
                    @endforeach
                </ol>
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
