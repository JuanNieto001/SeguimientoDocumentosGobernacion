{{-- Archivo: backend/resources/views/backend/dashboards/mi.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    @php $puedeVerTimeline = auth()->user()->hasAnyRole(['admin','admin_general','admin_secretaria']); @endphp

    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-gray-900 leading-none">Mi Dashboard</h1>
            <p class="text-xs text-gray-400 mt-1">Visualización basada en tu perfil y rol.</p>
        </div>
    </x-slot>

    <div class="p-6 space-y-5">
        @if(!$plantilla)
            @if($puedeVerTimeline && ($timeline ?? collect())->isNotEmpty())
                <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                    <h2 class="text-sm font-bold text-gray-800">Linea de tiempo de procesos</h2>
                    <p class="text-xs text-gray-400 mt-1">No tienes dashboard asignado. Aqui puedes seguir los procesos relacionados contigo.</p>

                    <div class="mt-4 space-y-3">
                        @foreach($timeline as $item)
                            <div class="rounded-xl p-3" style="border:1px solid #e2e8f0;background:#f8fafc">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-gray-800">{{ $item->codigo ?: ('Proceso #' . $item->id) }}</p>
                                    <span class="text-[11px] px-2 py-1 rounded-full" style="background:#e2e8f0;color:#334155">{{ $item->estado }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $item->objeto ?: 'Sin descripcion del objeto.' }}</p>
                                <p class="text-[11px] text-gray-400 mt-2">Area actual: {{ $item->area_actual_role ?: 'N/A' }} · Actualizado: {{ optional($item->updated_at)->format('Y-m-d H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl p-8 text-center" style="border:1px solid #e2e8f0">
                    <p class="text-sm text-gray-600">No tienes un dashboard asignado todavía.</p>
                    <p class="text-xs text-gray-400 mt-2">Contacta al administrador para asignar una plantilla en el Motor de Dashboards.</p>
                </div>
            @endif
        @else
            <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                <h2 class="text-sm font-bold text-gray-800">{{ $plantilla->nombre }}</h2>
                <p class="text-xs text-gray-400 mt-1">{{ $plantilla->descripcion }}</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($widgets->where('tipo', 'kpi') as $widget)
                    <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                        <p class="text-xs text-gray-400">{{ $widget->titulo }}</p>
                        <p class="text-3xl font-black text-gray-800 mt-1">{{ $kpis[$widget->id] ?? 0 }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid lg:grid-cols-2 gap-4">
                @foreach($widgets->where('tipo', 'chart') as $widget)
                    <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                        <h3 class="text-sm font-bold text-gray-800">{{ $widget->titulo }}</h3>
                        <div class="mt-3" style="height:280px">
                            <canvas id="chart_{{ $widget->id }}"></canvas>
                        </div>
                    </div>
                @endforeach
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const chartData = @json($charts);
                    const chartTypes = @json($chartTypes ?? []);
                    const widgets = @json($widgets->where('tipo', 'chart')->values()->map(fn($w) => ['id' => $w->id, 'metric' => $w->metrica]));

                    widgets.forEach(function (w) {
                        const canvas = document.getElementById('chart_' + w.id);
                        if (!canvas || !chartData[w.id]) return;

                        const cfg = chartData[w.id];
                        let type = chartTypes[w.id] || 'bar';
                        if (type === 'area') {
                            type = 'line';
                        }

                        new Chart(canvas, {
                            type: type,
                            data: cfg,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: true, position: 'bottom' }
                                }
                            }
                        });
                    });
                });
            </script>
        @endif
    </div>
</x-app-layout>

