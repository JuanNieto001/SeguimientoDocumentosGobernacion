<x-app-layout>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Panel del Gobernador</h1>
                <p class="text-xs text-gray-400 mt-1">Visión ejecutiva — Gobernación de Caldas</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reportes.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-xl border"
                   style="border-color:#e2e8f0;color:#374151">
                    Ver reportes →
                </a>
                <a href="{{ route('secop.consulta') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl text-white"
                   style="background:#1e3a5f">
                    Consulta SECOP II
                </a>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- KPIs principales --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
            $kpis = [
                ['valor' => $totalProcesos,      'label' => 'Total procesos',     'color' => '#1e3a5f', 'bg' => '#eff6ff', 'icon' => '📋'],
                ['valor' => $procesosActivos,    'label' => 'En curso',           'color' => '#15803d', 'bg' => '#f0fdf4', 'icon' => '🔄'],
                ['valor' => $procesosFinalizados,'label' => 'Finalizados',        'color' => '#0f766e', 'bg' => '#f0fdfa', 'icon' => '✅'],
                ['valor' => $procesosMes,        'label' => 'Nuevos este mes',    'color' => '#7c3aed', 'bg' => '#fdf4ff', 'icon' => '📊'],
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
            {{-- Gráfica: distribución por área --}}
            <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Procesos activos por área</h2>
                @if($distribucionArea->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">No hay procesos en curso.</p>
                @else
                @php
                $areaLabels = [
                    'unidad_solicitante' => 'Unidad Solicitante',
                    'planeacion'         => 'Planeación',
                    'hacienda'           => 'Hacienda',
                    'juridica'           => 'Jurídica',
                    'secop'              => 'SECOP',
                ];
                $areaColors = ['#3b82f6','#16a34a','#ca8a04','#ea580c','#9333ea','#64748b'];
                $labels = [];
                $data = [];
                $colors = [];
                $i = 0;
                foreach ($distribucionArea as $row) {
                    $labels[] = $areaLabels[$row->area_actual_role] ?? ucfirst(str_replace('_',' ',$row->area_actual_role));
                    $data[] = $row->total;
                    $colors[] = $areaColors[$i % count($areaColors)];
                    $i++;
                }
                @endphp
                <canvas id="chartArea" height="180"></canvas>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new Chart(document.getElementById('chartArea'), {
                        type: 'doughnut',
                        data: {
                            labels: @json($labels),
                            datasets: [{
                                data: @json($data),
                                backgroundColor: @json($colors),
                                borderWidth: 2,
                                borderColor: '#fff',
                            }]
                        },
                        options: {
                            plugins: {
                                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } }
                            },
                            cutout: '60%',
                        }
                    });
                });
                </script>
                @endif
            </div>

            {{-- Gráfica: tendencia mensual --}}
            <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Tendencia últimos 6 meses</h2>
                @if(empty($tendencia))
                <p class="text-sm text-gray-400 text-center py-8">Sin datos de tendencia.</p>
                @else
                @php
                $tLabels = collect($tendencia)->pluck('mes')->toArray();
                $tData   = collect($tendencia)->pluck('total')->toArray();
                @endphp
                <canvas id="chartTendencia" height="180"></canvas>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new Chart(document.getElementById('chartTendencia'), {
                        type: 'bar',
                        data: {
                            labels: @json($tLabels),
                            datasets: [{
                                label: 'Procesos creados',
                                data: @json($tData),
                                backgroundColor: 'rgba(30,58,95,0.75)',
                                borderRadius: 6,
                            }]
                        },
                        options: {
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { stepSize: 1 } }
                            }
                        }
                    });
                });
                </script>
                @endif
            </div>
        </div>

        {{-- Contratos próximos a vencer --}}
        @if($contratosProxVencer->isNotEmpty())
        <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">Contratos de aplicaciones por vencer (60 días)</h2>
                <a href="{{ route('contratos-app.index') }}"
                   class="text-xs font-medium" style="color:#1e3a5f">Ver todos →</a>
            </div>
            <div class="space-y-2">
                @foreach($contratosProxVencer as $c)
                @php
                    $dias = max(0, (int) now()->diffInDays($c->fecha_fin, false));
                    $urgente = $dias <= 30;
                @endphp
                <div class="flex items-center justify-between py-2 px-3 rounded-xl"
                     style="background:{{ $urgente ? '#fffbeb' : '#f8fafc' }}">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $c->nombre_aplicacion }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $c->secretaria?->nombre ?? '—' }}
                            @if($c->proveedor) · {{ $c->proveedor }} @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold" style="color:{{ $urgente ? '#b45309' : '#374151' }}">
                            {{ $dias }} días
                        </p>
                        <p class="text-xs text-gray-400">{{ $c->fecha_fin->format('d/m/Y') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Alertas recientes --}}
        @if($alertas->isNotEmpty())
        <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">Alertas sin leer</h2>
                <a href="{{ route('alertas.index') }}" class="text-xs font-medium" style="color:#1e3a5f">Ver todas →</a>
            </div>
            <div class="space-y-2">
                @foreach($alertas as $alerta)
                <div class="flex items-start gap-3 py-2 px-3 rounded-xl" style="background:#fef2f2">
                    <span class="text-lg mt-0.5">⚠️</span>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $alerta->titulo ?? $alerta->mensaje }}</p>
                        <p class="text-xs text-gray-400">{{ $alerta->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
