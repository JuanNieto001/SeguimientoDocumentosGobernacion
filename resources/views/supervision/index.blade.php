<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex-1 min-w-0">
                <h1 class="text-lg font-bold text-gray-900 leading-none truncate">Supervisión — {{ $proceso->codigo }}</h1>
                <p class="text-xs text-gray-400 mt-1 truncate">{{ Str::limit($proceso->objeto, 80) }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('supervision.crear-informe', $proceso->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-white"
                   style="background:#2563eb">
                    + Informe
                </a>
                <a href="{{ route('supervision.crear-pago', $proceso->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-white"
                   style="background:linear-gradient(135deg,#15803d,#14532d)">
                    + Pago
                </a>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border text-sm font-medium"
             style="background:#dcfce7;border-color:#bbf7d0;color:#14532d">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
            $kpis = [
                ['l'=>'Informes registrados','v'=>$stats['total_informes'],   'c'=>'#2563eb','bg'=>'#dbeafe','b'=>'#bfdbfe'],
                ['l'=>'Aprobados',           'v'=>$stats['aprobados'],        'c'=>'#15803d','bg'=>'#dcfce7','b'=>'#bbf7d0'],
                ['l'=>'Pagos registrados',   'v'=>$stats['total_pagos'],      'c'=>'#7c3aed','bg'=>'#ede9fe','b'=>'#ddd6fe'],
                ['l'=>'Pagos realizados',    'v'=>$stats['pagos_realizados'], 'c'=>'#ca8a04','bg'=>'#fef9c3','b'=>'#fde68a'],
            ];
            @endphp
            @foreach($kpis as $k)
            <div class="bg-white rounded-2xl p-4" style="border:1px solid {{ $k['b'] }}">
                <p class="text-2xl font-bold" style="color:{{ $k['c'] }}">{{ $k['v'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $k['l'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Alerta próximo pago --}}
        @if($stats['prox_pago'])
        @php $pp = $stats['prox_pago']; @endphp
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border text-sm"
             style="background:#fef9c3;border-color:#fde68a;color:#92400e">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>
                <strong>Pago #{{ $pp->numero_pago }}</strong> por
                ${{ number_format($pp->valor, 0, ',', '.') }} vence el
                {{ \Carbon\Carbon::parse($pp->fecha_estimada_pago)->format('d/m/Y') }}
                ({{ \Carbon\Carbon::parse($pp->fecha_estimada_pago)->diffForHumans() }})
            </span>
        </div>
        @endif

        <div class="grid lg:grid-cols-2 gap-5">

            {{-- Informes --}}
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                    <h3 class="text-sm font-bold text-gray-800">Informes de supervisión</h3>
                    <a href="{{ route('supervision.crear-informe', $proceso->id) }}"
                       class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                        + Nuevo
                    </a>
                </div>
                @if($informes->isEmpty())
                <div class="px-5 py-12 text-center">
                    <p class="text-sm text-gray-400">No hay informes registrados.</p>
                    <a href="{{ route('supervision.crear-informe', $proceso->id) }}"
                       class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 rounded-xl text-xs font-semibold text-white"
                       style="background:#2563eb">
                        Registrar primer informe
                    </a>
                </div>
                @else
                <div class="divide-y divide-gray-100">
                    @foreach($informes as $inf)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-bold text-gray-900">Informe #{{ $inf->numero_informe }}</span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                                          style="background:{{ $inf->bg_estado }};color:{{ $inf->color_estado }}">
                                        {{ $inf->label_estado_informe }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Período: {{ $inf->periodo_inicio }} — {{ $inf->periodo_fin }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $inf->fecha_informe->format('d/m/Y') }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs font-semibold" style="color:{{ $inf->estado_avance==='con_retraso' ? '#dc2626' : '#15803d' }}">
                                    {{ $inf->porcentaje_avance }}% avance
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $inf->label_estado_avance }}</p>
                            </div>
                        </div>
                        @if($inf->descripcion_actividades)
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $inf->descripcion_actividades }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Pagos --}}
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Pagos del contrato</h3>
                        @if($stats['valor_pagado'] > 0)
                        <p class="text-xs text-gray-400 mt-0.5">
                            Pagado: ${{ number_format($stats['valor_pagado'], 0, ',', '.') }}
                        </p>
                        @endif
                    </div>
                    <a href="{{ route('supervision.crear-pago', $proceso->id) }}"
                       class="text-xs font-semibold text-green-700 hover:text-green-900">
                        + Nuevo
                    </a>
                </div>
                @if($pagos->isEmpty())
                <div class="px-5 py-12 text-center">
                    <p class="text-sm text-gray-400">No hay pagos registrados.</p>
                    <a href="{{ route('supervision.crear-pago', $proceso->id) }}"
                       class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 rounded-xl text-xs font-semibold text-white"
                       style="background:#15803d">
                        Registrar primer pago
                    </a>
                </div>
                @else
                <div class="divide-y divide-gray-100">
                    @foreach($pagos as $pago)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-bold text-gray-900">Pago #{{ $pago->numero_pago }}</span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                                          style="background:{{ $pago->bg_estado }};color:{{ $pago->color_estado }}">
                                        {{ $pago->label_estado }}
                                    </span>
                                    @if($pago->proximo)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#fef9c3;color:#92400e">
                                        ⏰ Próximo
                                    </span>
                                    @elseif($pago->vencido)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#fee2e2;color:#b91c1c">
                                        ⚠ Vencido
                                    </span>
                                    @endif
                                </div>
                                <p class="text-sm font-semibold text-gray-900 mt-1">
                                    ${{ number_format($pago->valor, 0, ',', '.') }}
                                </p>
                                @if($pago->fecha_estimada_pago)
                                <p class="text-xs text-gray-400">
                                    Fecha estimada: {{ $pago->fecha_estimada_pago->format('d/m/Y') }}
                                </p>
                                @endif
                            </div>
                            {{-- Cambio rápido de estado --}}
                            @if(!in_array($pago->estado, ['pagado','rechazado']))
                            <form method="POST" action="{{ route('supervision.pago.actualizar', [$proceso->id, $pago->id]) }}"
                                  class="text-right shrink-0">
                                @csrf
                                <select name="estado" onchange="this.form.submit()"
                                        class="text-xs rounded-lg px-2 py-1 border focus:outline-none"
                                        style="border-color:#e2e8f0">
                                    <option value="pendiente"  @selected($pago->estado=='pendiente')>Pendiente</option>
                                    <option value="en_tramite" @selected($pago->estado=='en_tramite')>En trámite</option>
                                    <option value="aprobado"   @selected($pago->estado=='aprobado')>Aprobado</option>
                                    <option value="pagado"     @selected($pago->estado=='pagado')>Pagado</option>
                                    <option value="rechazado"  @selected($pago->estado=='rechazado')>Rechazado</option>
                                </select>
                            </form>
                            @endif
                        </div>
                        @if($pago->numero_referencia)
                        <p class="text-xs text-gray-400 mt-1">Ref: {{ $pago->numero_referencia }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
