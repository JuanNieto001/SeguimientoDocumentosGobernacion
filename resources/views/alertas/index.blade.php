<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Centro de notificaciones</h1>
                <p class="text-xs text-gray-400 mt-1">Alertas y avisos del sistema — Gobernación de Caldas</p>
            </div>
            @if(($alertas->total() ?? 0) > 0)
            <form method="POST" action="{{ route('alertas.leer.todas') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-xl border transition-colors"
                        style="border-color:#e2e8f0;color:#374151;background:#fff"
                        onclick="return confirm('¿Marcar todas las alertas como leídas?')">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Marcar todas leídas
                </button>
            </form>
            @endif
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Estadísticas --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
            $kpiData = [
                ['label'=>'Sin leer','val'=>$estadisticas['total'],'bg'=>'#fee2e2','icon'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9','text'=>'#b91c1c'],
                ['label'=>'Alta prioridad','val'=>$estadisticas['alta'],'bg'=>'#fef2f2','icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z','text'=>'#dc2626'],
                ['label'=>'Media prioridad','val'=>$estadisticas['media'],'bg'=>'#fefce8','icon'=>'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z','text'=>'#ca8a04'],
                ['label'=>'Baja prioridad','val'=>$estadisticas['baja'],'bg'=>'#f0fdf4','icon'=>'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z','text'=>'#15803d'],
            ];
            @endphp
            @foreach($kpiData as $k)
            <div class="bg-white rounded-2xl p-4 flex items-center gap-3" style="border:1px solid #e2e8f0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $k['bg'] }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:{{ $k['text'] }}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $k['icon'] }}"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 leading-none">{{ $k['val'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $k['label'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('alertas.index') }}" class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <select name="prioridad" class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                    <option value="">Todas las prioridades</option>
                    @foreach(['alta'=>'Alta','media'=>'Media','baja'=>'Baja','critica'=>'Crítica'] as $val=>$lbl)
                    <option value="{{ $val }}" @selected(request('prioridad')==$val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                <select name="tipo" class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                    <option value="">Todos los tipos</option>
                    @foreach(['tiempo_excedido'=>'Tiempo excedido','certificado_por_vencer'=>'Certificado por vencer','documento_rechazado'=>'Documento rechazado','sin_movimiento'=>'Sin movimiento'] as $val=>$lbl)
                    <option value="{{ $val }}" @selected(request('tipo')==$val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                <select name="leida" class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                    <option value="no_leidas" @selected(request('leida','no_leidas')=='no_leidas')>No leídas</option>
                    <option value="leidas" @selected(request('leida')=='leidas')>Leídas</option>
                    <option value="todas" @selected(request('leida')=='todas')>Todas</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white rounded-xl" style="background:#166534">
                    Filtrar
                </button>
            </div>
        </form>

        {{-- Lista de alertas --}}
        <div class="space-y-3">
            @forelse($alertas as $alerta)
            @php
            $priConfig = [
                'critica' => ['bg'=>'#fef2f2','border'=>'#fca5a5','icon-bg'=>'#dc2626','label'=>'Crítica','dot'=>'#dc2626'],
                'alta'    => ['bg'=>'#fff7ed','border'=>'#fed7aa','icon-bg'=>'#ea580c','label'=>'Alta','dot'=>'#ea580c'],
                'media'   => ['bg'=>'#fefce8','border'=>'#fde68a','icon-bg'=>'#ca8a04','label'=>'Media','dot'=>'#ca8a04'],
                'baja'    => ['bg'=>'#f0fdf4','border'=>'#bbf7d0','icon-bg'=>'#16a34a','label'=>'Baja','dot'=>'#16a34a'],
            ][$alerta->prioridad] ?? ['bg'=>'#f8fafc','border'=>'#e2e8f0','icon-bg'=>'#64748b','label'=>ucfirst($alerta->prioridad),'dot'=>'#64748b'];

            $tipoIcons = [
                'tiempo_excedido'       => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'certificado_por_vencer'=> 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'documento_rechazado'   => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                'sin_movimiento'        => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            ][$alerta->tipo] ?? 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
            @endphp
            <div class="bg-white rounded-2xl p-4 transition-all {{ $alerta->leida ? 'opacity-60' : '' }}"
                 style="border:1px solid {{ $alerta->leida ? '#e2e8f0' : $priConfig['border'] }};background:{{ $alerta->leida ? '#fff' : $priConfig['bg'] }}">
                <div class="flex items-start gap-4">
                    {{-- Icono --}}
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $priConfig['icon-bg'] }}">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tipoIcons }}"/></svg>
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-sm font-semibold text-gray-900">{{ $alerta->titulo ?? ucwords(str_replace('_',' ',$alerta->tipo)) }}</p>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold" style="background:{{ $priConfig['icon-bg'] }};color:#fff">{{ $priConfig['label'] }}</span>
                                    @if(!$alerta->leida)
                                    <span class="w-2 h-2 rounded-full inline-block" style="background:{{ $priConfig['dot'] }}" title="No leída"></span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ $alerta->mensaje }}</p>
                                @if($alerta->proceso)
                                <a href="{{ route('procesos.show', $alerta->proceso->id) }}"
                                   class="inline-flex items-center gap-1 text-xs text-green-700 hover:text-green-900 mt-1.5 font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Ver proceso {{ $alerta->proceso->codigo }}
                                </a>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <span class="text-xs text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($alerta->created_at)->diffForHumans() }}</span>
                                @if(!$alerta->leida)
                                <form method="POST" action="{{ route('alertas.leer', $alerta->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" title="Marcar como leída"
                                            class="ml-2 p-1.5 rounded-lg hover:bg-white/80 transition-colors text-gray-400 hover:text-green-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('alertas.destroy', $alerta->id) }}" class="inline"
                                      onsubmit="return confirm('¿Eliminar esta alerta?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Eliminar alerta"
                                            class="ml-1 p-1.5 rounded-lg hover:bg-white/80 transition-colors text-gray-300 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Área responsable --}}
                        @if($alerta->area_responsable)
                        <div class="flex items-center gap-1.5 mt-2">
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="text-xs text-gray-400">Área: <strong>{{ ucfirst($alerta->area_responsable) }}</strong></span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl flex flex-col items-center gap-3 py-16" style="border:1px solid #e2e8f0">
                <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <p class="text-sm font-medium text-gray-400">No hay alertas que mostrar</p>
                <p class="text-xs text-gray-300">Las alertas se generan automáticamente según la actividad del sistema</p>
            </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        @if($alertas->hasPages())
        <div class="flex justify-center">
            {{ $alertas->links() }}
        </div>
        @endif

    </div>
</x-app-layout>
