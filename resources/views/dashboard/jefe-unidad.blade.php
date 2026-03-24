<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">
                    Panel — {{ $unidad?->nombre ?? 'Mi Unidad' }}
                </h1>
                <p class="text-xs text-gray-400 mt-1">Jefe de Unidad — Gobernación de Caldas</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('procesos.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-xl text-white"
                   style="background:#166534">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva solicitud
                </a>
                <a href="{{ route('procesos.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-xl border"
                   style="border-color:#e2e8f0;color:#374151">
                    Ver procesos
                </a>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
            $kpis = [
                ['valor' => $totalProcesos,      'label' => 'Total procesos mi unidad', 'color' => '#1e3a5f', 'bg' => '#eff6ff', 'icon' => '📋'],
                ['valor' => $procesosActivos,    'label' => 'En curso',                'color' => '#15803d', 'bg' => '#f0fdf4', 'icon' => '🔄'],
                ['valor' => $procesosFinalizados,'label' => 'Finalizados',             'color' => '#0f766e', 'bg' => '#f0fdfa', 'icon' => '✅'],
                ['valor' => $procesosMes,        'label' => 'Nuevos este mes',         'color' => '#7c3aed', 'bg' => '#fdf4ff', 'icon' => '📊'],
            ];
            @endphp
            @foreach($kpis as $k)
            <div class="rounded-2xl p-4 shadow-sm border" style="background:{{ $k['bg'] }};border-color:#e2e8f0">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium" style="color:#64748b">{{ $k['label'] }}</p>
                        <p class="text-3xl font-bold mt-1" style="color:{{ $k['color'] }}">{{ $k['valor'] }}</p>
                    </div>
                    <span class="text-2xl">{{ $k['icon'] }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Procesos en curso --}}
            <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Procesos de mi unidad en curso</h2>
                    <a href="{{ route('procesos.index') }}" class="text-xs font-medium" style="color:#1e3a5f">Ver todos →</a>
                </div>
                @if($procesosEnCurso->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">No hay procesos en curso.</p>
                @else
                <div class="space-y-2">
                    @foreach($procesosEnCurso as $p)
                    <a href="{{ route('procesos.show', $p->id) }}"
                       class="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-gray-50 transition-colors">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $p->objeto }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $p->codigo }}
                                @if($p->etapa) · {{ $p->etapa }} @endif
                            </p>
                        </div>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full ml-3 shrink-0"
                              style="background:#eff6ff;color:#1e40af">
                            {{ ucfirst(str_replace('_', ' ', $p->area_actual_role ?? '—')) }}
                        </span>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Alertas --}}
            <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Mis alertas pendientes</h2>
                    <a href="{{ route('alertas.index') }}" class="text-xs font-medium" style="color:#1e3a5f">Ver todas →</a>
                </div>
                @if($alertas->isEmpty())
                <div class="text-center py-8">
                    <p class="text-2xl mb-2">🎉</p>
                    <p class="text-sm text-gray-400">No tienes alertas pendientes.</p>
                </div>
                @else
                <div class="space-y-2">
                    @foreach($alertas as $alerta)
                    <div class="flex items-start gap-3 py-2 px-3 rounded-xl" style="background:#fef2f2">
                        <span class="text-lg mt-0.5">⚠️</span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">
                                {{ $alerta->titulo ?? $alerta->mensaje }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $alerta->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Accesos rápidos --}}
        <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Accesos rápidos</h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('procesos.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border hover:bg-gray-50 transition-colors"
                   style="border-color:#e2e8f0;color:#374151">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva solicitud de contrato
                </a>
                <a href="{{ route('procesos.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border hover:bg-gray-50 transition-colors"
                   style="border-color:#e2e8f0;color:#374151">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Ver todos los procesos
                </a>
                <a href="{{ route('contratos-app.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border hover:bg-gray-50 transition-colors"
                   style="border-color:#e2e8f0;color:#374151">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Contratos de aplicaciones
                </a>
                <a href="{{ route('alertas.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border hover:bg-gray-50 transition-colors"
                   style="border-color:#e2e8f0;color:#374151">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notificaciones
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
