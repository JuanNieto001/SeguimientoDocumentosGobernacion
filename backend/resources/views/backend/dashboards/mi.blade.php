{{-- Archivo: backend/resources/views/backend/dashboards/mi.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <link rel="stylesheet" href="{{ asset('vendor/gridstack/gridstack.min.css') }}">

    <style>
        .mi-chart-toolbar-btn {
            border: 1px solid #dbe3ec;
            background: #ffffff;
            color: #475569;
            border-radius: 9px;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 9px;
            transition: all .14s ease;
        }
        .mi-chart-toolbar-btn:hover { background: #f8fafc; color: #0f172a; }
        .mi-chart-toolbar-btn.active {
            background: linear-gradient(135deg,#166534,#14532d);
            border-color: #14532d;
            color: #ffffff;
        }
        .mi-dashboard-chart-canvas {
            min-height: 300px;
        }
        .mi-dashboard-chart-canvas .grid-stack-item-content {
            inset: 0 !important;
            background: transparent;
            border: none;
            overflow: visible;
        }
        .mi-dashboard-chart-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .mi-dashboard-chart-card .mi-dashboard-chart-body {
            flex: 1;
            min-height: 180px;
        }
        .mi-dashboard-chart-canvas.mi-charts-editing .mi-dashboard-chart-header {
            cursor: move;
            user-select: none;
        }
    </style>

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

            @if($widgets->where('tipo', 'chart')->isNotEmpty())
                <div class="flex items-center justify-end gap-2">
                    <span id="miChartsEditState" class="text-[11px] font-semibold text-gray-400">Bloqueado</span>
                    <button id="miChartsEditToggle" type="button" class="mi-chart-toolbar-btn">Organizar gráficas</button>
                    <button id="miChartsLayoutReset" type="button" class="mi-chart-toolbar-btn">Restablecer</button>
                </div>

                <div id="miDashboardChartCanvas" class="grid-stack mi-dashboard-chart-canvas">
                    @foreach($widgets->where('tipo', 'chart') as $widget)
                        @php
                            $chartX = ($loop->index % 2) * 6;
                            $chartY = intdiv($loop->index, 2) * 6;
                        @endphp
                        <div class="grid-stack-item" id="mi-chart-widget-{{ $widget->id }}" gs-x="{{ $chartX }}" gs-y="{{ $chartY }}" gs-w="6" gs-h="6">
                            <div class="grid-stack-item-content">
                                <div class="bg-white rounded-2xl p-4 mi-dashboard-chart-card" style="border:1px solid #e2e8f0">
                                    <h3 class="mi-dashboard-chart-header text-sm font-bold text-gray-800">{{ $widget->titulo }}</h3>
                                    <div class="mt-3 mi-dashboard-chart-body">
                                        <canvas id="chart_{{ $widget->id }}"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script src="{{ asset('vendor/gridstack/gridstack-all.js') }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const chartData = @json($charts);
                    const chartTypes = @json($chartTypes ?? []);
                    const widgets = @json($widgets->where('tipo', 'chart')->values()->map(fn($w) => ['id' => $w->id, 'metric' => $w->metrica]));
                    const chartRegistry = {};

                    widgets.forEach(function (w) {
                        const canvas = document.getElementById('chart_' + w.id);
                        if (!canvas || !chartData[w.id]) return;

                        const cfg = chartData[w.id];
                        let type = chartTypes[w.id] || 'bar';
                        if (type === 'area') {
                            type = 'line';
                        }

                        chartRegistry[w.id] = new Chart(canvas, {
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

                    const GridStackClass =
                        (window.GridStack && typeof window.GridStack.init === 'function')
                            ? window.GridStack
                            : (window.GridStack && window.GridStack.GridStack && typeof window.GridStack.GridStack.init === 'function')
                                ? window.GridStack.GridStack
                                : null;

                    const canvas = document.getElementById('miDashboardChartCanvas');
                    const editBtn = document.getElementById('miChartsEditToggle');
                    const resetBtn = document.getElementById('miChartsLayoutReset');
                    const editState = document.getElementById('miChartsEditState');

                    if (!canvas) {
                        return;
                    }

                    const resizeCharts = () => {
                        Object.values(chartRegistry).forEach(chart => {
                            if (chart && typeof chart.resize === 'function') {
                                chart.resize();
                            }
                        });
                    };

                    if (!GridStackClass) {
                        if (editState) editState.textContent = 'Grid no disponible';
                        [editBtn, resetBtn].forEach(btn => {
                            if (!btn) return;
                            btn.disabled = true;
                            btn.style.opacity = '0.55';
                            btn.style.cursor = 'not-allowed';
                        });
                        return;
                    }

                    const storageKey = 'dashboard.mi.charts.layout.{{ auth()->id() }}.{{ $plantilla->id ?? 0 }}.v1';

                    const toInt = (value, fallback, min = 0) => {
                        const parsed = Number(value);
                        return Number.isFinite(parsed) ? Math.max(min, Math.round(parsed)) : fallback;
                    };

                    const readDefaultLayout = () => {
                        return Array.from(canvas.querySelectorAll('.grid-stack-item'))
                            .map(item => ({
                                id: item.id,
                                x: toInt(item.getAttribute('gs-x'), 0),
                                y: toInt(item.getAttribute('gs-y'), 0),
                                w: toInt(item.getAttribute('gs-w'), 6, 1),
                                h: toInt(item.getAttribute('gs-h'), 6, 1),
                            }))
                            .sort((a, b) => (a.y - b.y) || (a.x - b.x));
                    };

                    const normalizeLayout = (layout, defaults) => {
                        const source = Array.isArray(layout) ? layout : [];
                        const map = new Map(source.map(item => [item.id, item]));
                        return defaults.map(item => {
                            const found = map.get(item.id) || {};
                            return {
                                id: item.id,
                                x: toInt(found.x, item.x),
                                y: toInt(found.y, item.y),
                                w: toInt(found.w, item.w, 1),
                                h: toInt(found.h, item.h, 1),
                            };
                        });
                    };

                    const applyLayout = (grid, layout, defaults) => {
                        const normalized = normalizeLayout(layout, defaults);
                        grid.batchUpdate();
                        normalized.forEach(item => {
                            const el = document.getElementById(item.id);
                            if (!el) return;
                            grid.update(el, { x: item.x, y: item.y, w: item.w, h: item.h });
                        });
                        grid.batchUpdate(false);
                    };

                    const readStoredLayout = (defaults) => {
                        try {
                            const raw = localStorage.getItem(storageKey);
                            if (!raw) return null;
                            return normalizeLayout(JSON.parse(raw), defaults);
                        } catch (_error) {
                            return null;
                        }
                    };

                    const saveLayout = (grid, defaults) => {
                        const layout = defaults.map(item => {
                            const el = document.getElementById(item.id);
                            const node = el ? el.gridstackNode : null;
                            if (!node) return item;
                            return { id: item.id, x: node.x, y: node.y, w: node.w, h: node.h };
                        });
                        localStorage.setItem(storageKey, JSON.stringify(layout));
                    };

                    const defaultLayout = readDefaultLayout();
                    const grid = GridStackClass.init({
                        column: 12,
                        float: true,
                        margin: 12,
                        cellHeight: 44,
                        animate: true,
                        disableDrag: false,
                        disableResize: false,
                        handle: '.grid-stack-item-content',
                        draggable: {
                            handle: '.grid-stack-item-content',
                            cancel: 'a,button,input,textarea,select,option'
                        },
                        resizable: { handles: 'all' },
                    }, canvas);

                    applyLayout(grid, readStoredLayout(defaultLayout) || defaultLayout, defaultLayout);

                    const setEditMode = (enabled) => {
                        grid.setStatic(!enabled);
                        if (typeof grid.enableMove === 'function') {
                            grid.enableMove(enabled);
                        }
                        if (typeof grid.enableResize === 'function') {
                            grid.enableResize(enabled);
                        }
                        canvas.classList.toggle('mi-charts-editing', enabled);

                        if (editBtn) {
                            editBtn.classList.toggle('active', enabled);
                            editBtn.textContent = enabled ? 'Bloquear' : 'Organizar gráficas';
                        }
                        if (editState) {
                            editState.textContent = enabled ? 'Editando' : 'Bloqueado';
                            editState.style.color = enabled ? '#166534' : '#94a3b8';
                        }
                    };

                    const persistLayout = () => {
                        saveLayout(grid, defaultLayout);
                        window.requestAnimationFrame(resizeCharts);
                    };

                    let persistTimer = null;
                    const onGridChanged = () => {
                        if (persistTimer) window.clearTimeout(persistTimer);
                        persistTimer = window.setTimeout(persistLayout, 140);
                    };

                    grid.on('dragstop', onGridChanged);
                    grid.on('resizestop', onGridChanged);

                    setEditMode(false);

                    if (editBtn) {
                        editBtn.addEventListener('click', () => {
                            const enabled = !canvas.classList.contains('mi-charts-editing');
                            setEditMode(enabled);
                            if (!enabled) {
                                persistLayout();
                            }
                        });
                    }

                    if (resetBtn) {
                        resetBtn.addEventListener('click', () => {
                            localStorage.removeItem(storageKey);
                            applyLayout(grid, defaultLayout, defaultLayout);
                            setEditMode(false);
                            persistLayout();
                        });
                    }

                    window.addEventListener('resize', resizeCharts);
                });
            </script>
        @endif
    </div>
</x-app-layout>

