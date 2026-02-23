<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                    <a href="{{ route('reportes.index') }}" class="hover:text-gray-700">Reportes</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">Actividad por actor</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Actividad por actor</h1>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
            $totalAcciones   = collect($estadisticas)->sum('total_acciones');
            $totalActores    = count($estadisticas);
            $topActor        = collect($estadisticas)->sortByDesc('total_acciones')->first();
            @endphp
            <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                <p class="text-2xl font-bold text-gray-900">{{ $auditorias->count() }}</p>
                <p class="text-xs text-gray-400 mt-1">Eventos totales</p>
            </div>
            <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                <p class="text-2xl font-bold text-purple-600">{{ $totalActores }}</p>
                <p class="text-xs text-gray-400 mt-1">Actores involucrados</p>
            </div>
            <div class="bg-white rounded-2xl p-4 col-span-2" style="border:1px solid #e2e8f0">
                <p class="text-xs text-gray-400 mb-1">Actor más activo</p>
                <p class="text-sm font-bold text-gray-800">{{ $topActor['nombre'] ?? '-' }}</p>
                <p class="text-xs text-gray-400">{{ $topActor['total_acciones'] ?? 0 }} acciones en el período</p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio', now()->subMonth()->format('Y-m-d')) }}"
                           class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin', now()->format('Y-m-d')) }}"
                           class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none">
                </div>
                <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-semibold text-white" style="background:#1d4ed8">Filtrar</button>
            </form>
        </div>

        {{-- Cards por actor --}}
        @forelse($estadisticas as $uid => $actor)
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #f1f5f9">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm" style="background:#f0fdf4;color:#15803d">
                        {{ strtoupper(substr($actor['nombre'], 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800">{{ $actor['nombre'] }}</p>
                        <p class="text-xs text-gray-400">{{ $actor['email'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <span class="font-bold text-gray-700 text-base">{{ $actor['total_acciones'] }}</span>
                    <span>acciones totales</span>
                </div>
            </div>
            @if($actor['acciones_por_tipo']->isNotEmpty())
            <div class="px-5 py-3 flex flex-wrap gap-2">
                @foreach($actor['acciones_por_tipo'] as $accion => $cnt)
                @php
                $colors = ['crear'=>['#dcfce7','#15803d'],'editar'=>['#dbeafe','#1d4ed8'],'enviar'=>['#fef9c3','#854d0e'],'aprobar'=>['#f0fdf4','#15803d'],'rechazar'=>['#fee2e2','#b91c1c'],'comentar'=>['#f5f3ff','#7e22ce']];
                $c = $colors[$accion] ?? ['#f1f5f9','#475569'];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background:{{ $c[0] }};color:{{ $c[1] }}">
                    {{ ucfirst($accion) }}: {{ $cnt }}
                </span>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl p-12 text-center text-gray-400 text-sm" style="border:1px solid #e2e8f0">
            No hay actividad registrada en el período seleccionado.
        </div>
        @endforelse

        {{-- Línea de tiempo reciente --}}
        @if($auditorias->isNotEmpty())
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="px-5 py-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-gray-800">Eventos recientes</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($auditorias->take(30) as $auditoria)
                <div class="flex items-start gap-3 px-5 py-3">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5" style="background:#f1f5f9">
                        <span class="text-xs font-bold text-gray-400">{{ strtoupper(substr($auditoria->usuario->name ?? 'S', 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-700">
                            <span class="font-semibold">{{ $auditoria->usuario->name ?? 'Sistema' }}</span>
                            realizó
                            <span class="font-semibold text-blue-600">{{ $auditoria->accion ?? 'acción' }}</span>
                            en proceso
                            <a href="{{ route('procesos.show', $auditoria->proceso_id) }}" class="text-blue-600 hover:underline font-semibold">
                                #{{ $auditoria->proceso_id }}
                            </a>
                        </p>
                        @if($auditoria->descripcion ?? $auditoria->detalle ?? null)
                        <p class="text-xs text-gray-400 truncate">{{ $auditoria->descripcion ?? $auditoria->detalle }}</p>
                        @endif
                    </div>
                    <span class="text-xs text-gray-300 shrink-0">{{ $auditoria->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
