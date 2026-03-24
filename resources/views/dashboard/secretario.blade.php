<x-app-layout>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">
                    Panel — {{ $secretaria?->nombre ?? 'Mi Secretaría' }}
                </h1>
                <p class="text-xs text-gray-400 mt-1">Secretario de Despacho — Gobernación de Caldas</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('procesos.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-xl border"
                   style="border-color:#e2e8f0;color:#374151">
                    Ver procesos
                </a>
                <a href="{{ route('reportes.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl text-white"
                   style="background:#1e3a5f">
                    Reportes
                </a>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
            $kpis = [
                ['valor' => $totalProcesos,      'label' => 'Total procesos',   'color' => '#1e3a5f', 'bg' => '#eff6ff', 'icon' => '📋'],
                ['valor' => $procesosActivos,    'label' => 'En curso',         'color' => '#15803d', 'bg' => '#f0fdf4', 'icon' => '🔄'],
                ['valor' => $procesosFinalizados,'label' => 'Finalizados',      'color' => '#0f766e', 'bg' => '#f0fdfa', 'icon' => '✅'],
                ['valor' => $procesosMes,        'label' => 'Nuevos este mes',  'color' => '#7c3aed', 'bg' => '#fdf4ff', 'icon' => '📊'],
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
                    <h2 class="text-sm font-semibold text-gray-700">Procesos en curso</h2>
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

            {{-- Contratos de aplicaciones --}}
            <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Contratos de aplicaciones</h2>
                    <a href="{{ route('contratos-app.index') }}" class="text-xs font-medium" style="color:#1e3a5f">Ver todos →</a>
                </div>
                @if($contratos->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">No hay contratos registrados.</p>
                @else
                <div class="space-y-2">
                    @foreach($contratos as $c)
                    @php
                        $estadoEfectivo = $c->estado_efectivo;
                        $badgePalette = [
                            'activo'     => ['bg' => '#f0fdf4', 'text' => '#15803d'],
                            'por_vencer' => ['bg' => '#fffbeb', 'text' => '#b45309'],
                            'vencido'    => ['bg' => '#fef2f2', 'text' => '#b91c1c'],
                            'cancelado'  => ['bg' => '#f8fafc', 'text' => '#64748b'],
                        ];
                        $bp = $badgePalette[$estadoEfectivo] ?? $badgePalette['cancelado'];
                    @endphp
                    <div class="flex items-center justify-between py-2 px-3 rounded-xl" style="background:#f8fafc">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $c->nombre_aplicacion }}</p>
                            <p class="text-xs text-gray-400">Vence: {{ $c->fecha_fin->format('d/m/Y') }}</p>
                        </div>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full ml-3 shrink-0"
                              style="background:{{ $bp['bg'] }};color:{{ $bp['text'] }}">
                            {{ ucfirst(str_replace('_', ' ', $estadoEfectivo)) }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
