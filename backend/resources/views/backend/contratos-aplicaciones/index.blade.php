{{-- Archivo: backend/resources/views/backend/contratos-aplicaciones/index.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <link rel="stylesheet" href="{{ asset('vendor/gridstack/gridstack.min.css') }}">

    <style>
        .ca-chart-toolbar-btn {
            border: 1px solid #dbe3ec;
            background: #ffffff;
            color: #475569;
            border-radius: 9px;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 9px;
            transition: all .14s ease;
        }
        .ca-chart-toolbar-btn:hover { background: #f8fafc; color: #0f172a; }
        .ca-chart-toolbar-btn.active {
            background: linear-gradient(135deg,#166534,#14532d);
            border-color: #14532d;
            color: #ffffff;
        }

        .ca-layout-canvas {
            min-height: 560px;
        }
        .ca-layout-canvas .grid-stack-item-content {
            inset: 0 !important;
            background: transparent;
            border: none;
            overflow: visible;
        }

        .ca-widget-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            padding: 16px;
        }
        .ca-widget-body {
            flex: 1;
            min-height: 170px;
        }
        .ca-widget-handle {
            cursor: default;
            user-select: none;
        }
        .ca-layout-editing .ca-widget-handle {
            cursor: move;
        }

        .ca-kpi-card {
            position: relative;
            overflow: hidden;
            border: 1px solid color-mix(in srgb, var(--kpi-accent) 20%, #ffffff);
            border-radius: 16px;
            padding: 12px;
            height: 100%;
            background: linear-gradient(135deg, var(--kpi-from), var(--kpi-to));
            box-shadow: 0 10px 20px -16px color-mix(in srgb, var(--kpi-accent) 45%, #0f172a);
        }
        .ca-kpi-orb {
            position: absolute;
            width: 110px;
            height: 110px;
            border-radius: 999px;
            right: -30px;
            top: -40px;
            background: radial-gradient(circle, color-mix(in srgb, var(--kpi-accent) 28%, #ffffff) 0%, rgba(255,255,255,0) 70%);
            pointer-events: none;
        }
        .ca-kpi-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .02em;
            background: rgba(255,255,255,0.85);
            color: color-mix(in srgb, var(--kpi-accent) 82%, #0f172a);
            border: 1px solid color-mix(in srgb, var(--kpi-accent) 25%, #ffffff);
        }
        .ca-kpi-icon {
            width: 28px;
            height: 28px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.82);
            border: 1px solid color-mix(in srgb, var(--kpi-accent) 28%, #ffffff);
            color: color-mix(in srgb, var(--kpi-accent) 85%, #0f172a);
        }

        .ca-secop-results {
            flex: 1;
            min-height: 180px;
            overflow-y: auto;
            padding-right: 4px;
        }
    </style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Contratos de Aplicaciones</h1>
                <p class="text-xs text-gray-400 mt-1">Inventario contractual de aplicaciones con sincronizacion SECOP y monitoreo de vigencias.</p>
            </div>
            @if(auth()->user()->hasAnyRole(['admin', 'admin_general', 'admin_secretaria', 'gobernador', 'secretario', 'jefe_unidad']))
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('contratos-aplicaciones.sincronizar-secop') }}">
                        @csrf
                        <button class="px-3 py-2 rounded-xl text-xs font-semibold text-white"
                                style="background:linear-gradient(135deg,#0f766e,#155e75)">
                            Sincronizar SECOP
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="p-6 space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 rounded-xl text-sm font-medium" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534">{{ session('success') }}</div>
        @endif

        @if($errors->has('secop_id'))
            <div class="px-4 py-3 rounded-xl text-sm font-medium" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c">
                {{ $errors->first('secop_id') }}
            </div>
        @endif

        @php
            $canManageContratos = auth()->user()->hasAnyRole(['admin', 'admin_general', 'admin_secretaria', 'gobernador', 'secretario', 'jefe_unidad']);
            $diasProximo = ($proximoVencer && $proximoVencer->fecha_fin)
                ? now()->startOfDay()->diffInDays($proximoVencer->fecha_fin->copy()->startOfDay(), false)
                : null;
            $proximoDetalle = ($proximoVencer && $proximoVencer->fecha_fin)
                ? optional($proximoVencer->fecha_fin)->format('Y-m-d') . ' (' . $diasProximo . ' dias)'
                : 'Sin proximo vencimiento';

            $kpiWidgets = [
                [
                    'id' => 'ca-kpi-activos',
                    'x' => 0,
                    'w' => 2,
                    'titulo' => 'Activos vigentes',
                    'valor' => number_format($resumen['activos_vigentes']),
                    'sub' => 'Contratos en ejecucion',
                    'badge' => 'Vigentes',
                    'from' => '#ecfeff',
                    'to' => '#e0f2fe',
                    'accent' => '#0284c7',
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'id' => 'ca-kpi-por-vencer',
                    'x' => 2,
                    'w' => 2,
                    'titulo' => 'Por vencer (90 dias)',
                    'valor' => number_format($resumen['por_vencer_90']),
                    'sub' => 'Requieren seguimiento',
                    'badge' => 'Atencion',
                    'from' => '#fff7ed',
                    'to' => '#ffedd5',
                    'accent' => '#ea580c',
                    'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                ],
                [
                    'id' => 'ca-kpi-vencidos',
                    'x' => 4,
                    'w' => 2,
                    'titulo' => 'Vencidos',
                    'valor' => number_format($resumen['vencidos']),
                    'sub' => 'Requieren accion',
                    'badge' => 'Critico',
                    'from' => '#fef2f2',
                    'to' => '#ffe4e6',
                    'accent' => '#dc2626',
                    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'id' => 'ca-kpi-valor',
                    'x' => 6,
                    'w' => 3,
                    'titulo' => 'Valor total activos',
                    'valor' => '$ ' . number_format((float) $resumen['valor_total_activos'], 0, ',', '.'),
                    'sub' => 'Monto acumulado',
                    'badge' => 'Financiero',
                    'from' => '#ecfdf3',
                    'to' => '#dcfce7',
                    'accent' => '#16a34a',
                    'icon' => 'M12 8c-2.21 0-4 .896-4 2s1.79 2 4 2 4 .896 4 2-1.79 2-4 2m0-8v8m0 0v2m0-10V6',
                ],
                [
                    'id' => 'ca-kpi-proximo',
                    'x' => 9,
                    'w' => 3,
                    'titulo' => 'Mas proximo a vencer',
                    'valor' => $proximoVencer ? $proximoVencer->aplicacion : 'Sin proximos',
                    'sub' => $proximoDetalle,
                    'badge' => 'Prioridad',
                    'from' => '#eef2ff',
                    'to' => '#e0e7ff',
                    'accent' => '#4338ca',
                    'icon' => 'M9 17v-2a4 4 0 014-4h6M9 7h6m-6 4h6m-9 8h12a2 2 0 002-2V7.414a2 2 0 00-.586-1.414l-2.414-2.414A2 2 0 0015.586 3H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                ],
            ];
        @endphp

        @if($canManageContratos)
            <div class="flex items-center justify-end gap-2">
                <span id="caLayoutStateManage" class="text-[11px] font-semibold text-gray-400">Bloqueado</span>
                <button id="caLayoutToggleManage" type="button" class="ca-chart-toolbar-btn">Organizar paneles</button>
                <button id="caLayoutResetManage" type="button" class="ca-chart-toolbar-btn">Restablecer</button>
            </div>

            <div id="caLayoutCanvasManage" class="grid-stack ca-layout-canvas">
                @foreach($kpiWidgets as $kpi)
                    <div class="grid-stack-item" id="{{ $kpi['id'] }}-manage" gs-x="{{ $kpi['x'] }}" gs-y="0" gs-w="{{ $kpi['w'] }}" gs-h="3">
                        <div class="grid-stack-item-content">
                            <div class="ca-kpi-card" style="--kpi-from:{{ $kpi['from'] }};--kpi-to:{{ $kpi['to'] }};--kpi-accent:{{ $kpi['accent'] }}">
                                <div class="ca-kpi-orb"></div>
                                <div class="ca-widget-handle flex items-center justify-between gap-2">
                                    <span class="ca-kpi-chip">{{ $kpi['badge'] }}</span>
                                    <span class="ca-kpi-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/>
                                        </svg>
                                    </span>
                                </div>
                                @php $isLongValue = strlen((string) $kpi['valor']) > 18; @endphp
                                <p class="mt-3 text-[11px] font-semibold text-slate-600 uppercase tracking-wide">{{ $kpi['titulo'] }}</p>
                                <p class="mt-1 font-black leading-tight text-slate-900 {{ $isLongValue ? 'text-lg' : 'text-2xl' }}">{{ $kpi['valor'] }}</p>
                                <p class="mt-2 text-xs font-medium text-slate-500">{{ $kpi['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="grid-stack-item" id="ca-chart-estado-manage" gs-x="0" gs-y="3" gs-w="4" gs-h="7">
                    <div class="grid-stack-item-content">
                        <div class="ca-widget-card">
                            <div class="ca-widget-handle flex items-start justify-between gap-2">
                                <div>
                                    <h2 class="text-sm font-bold text-gray-800">Distribucion por estado</h2>
                                    <p class="text-xs text-gray-400">Contratos de aplicaciones por estado interno</p>
                                </div>
                            </div>
                            <div class="mt-3 ca-widget-body">
                                <canvas id="chartEstadoContratos"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid-stack-item" id="ca-chart-venc-manage" gs-x="4" gs-y="3" gs-w="4" gs-h="7">
                    <div class="grid-stack-item-content">
                        <div class="ca-widget-card">
                            <div class="ca-widget-handle flex items-start justify-between gap-2">
                                <div>
                                    <h2 class="text-sm font-bold text-gray-800">Dias para vencimiento</h2>
                                    <p class="text-xs text-gray-400">Contratos activos con fecha fin mas cercana</p>
                                </div>
                            </div>
                            <div class="mt-3 ca-widget-body">
                                <canvas id="chartVencimientosContratos"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid-stack-item" id="ca-widget-secop-manage" gs-x="8" gs-y="3" gs-w="4" gs-h="10">
                    <div class="grid-stack-item-content">
                        <div class="ca-widget-card">
                            <div class="ca-widget-handle">
                                <h2 class="text-sm font-bold text-gray-800">Adicionar desde SECOP</h2>
                                <p class="text-xs text-gray-400">Busca por ID o referencia y adiciona directo a la lista.</p>
                            </div>

                            <form method="GET" action="{{ route('contratos-aplicaciones.index') }}" class="mt-3 flex gap-2">
                                <input name="secop_buscar" value="{{ $secopBuscar }}"
                                       placeholder="CO1.PCCNTR.9206029"
                                       class="flex-1 rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
                                <button class="px-3 py-2 rounded-xl text-xs font-semibold text-white" style="background:#0f766e">Buscar</button>
                            </form>

                            <div class="mt-3 space-y-2 ca-secop-results">
                                @if($secopBuscar === '')
                                    <p class="text-xs text-gray-500">Ingresa un ID SECOP para consultar y adicionar.</p>
                                @elseif(count($secopResultados) === 0)
                                    <p class="text-xs text-gray-500">No se encontraron resultados para "{{ $secopBuscar }}".</p>
                                @else
                                    @foreach($secopResultados as $resultado)
                                        <div class="rounded-xl p-3" style="border:1px solid #e2e8f0;background:#f8fafc">
                                            <p class="text-sm font-semibold text-gray-800">{{ $resultado['aplicacion'] }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $resultado['secop_id'] }}</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Vence: {{ $resultado['fecha_fin'] ?: 'Sin fecha fin' }}</p>

                                            <div class="mt-2">
                                                @if($resultado['ya_agregado'])
                                                    <span class="text-xs font-semibold px-3 py-2 rounded-xl" style="background:#dcfce7;color:#166534">Ya esta en la lista</span>
                                                @else
                                                    <form method="POST" action="{{ route('contratos-aplicaciones.adicionar-secop') }}">
                                                        @csrf
                                                        <input type="hidden" name="secop_id" value="{{ $resultado['secop_id'] }}">
                                                        <input type="hidden" name="aplicacion_secop" value="{{ $resultado['aplicacion'] }}">
                                                        <input type="hidden" name="activo_secop" value="1">
                                                        <button class="px-3 py-2 rounded-xl text-xs font-semibold text-white" style="background:#2563eb">Adicionar</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="flex items-center justify-end gap-2">
                <span id="caLayoutStateBasic" class="text-[11px] font-semibold text-gray-400">Bloqueado</span>
                <button id="caLayoutToggleBasic" type="button" class="ca-chart-toolbar-btn">Organizar paneles</button>
                <button id="caLayoutResetBasic" type="button" class="ca-chart-toolbar-btn">Restablecer</button>
            </div>

            <div id="caLayoutCanvasBasic" class="grid-stack ca-layout-canvas">
                @foreach($kpiWidgets as $kpi)
                    <div class="grid-stack-item" id="{{ $kpi['id'] }}-basic" gs-x="{{ $kpi['x'] }}" gs-y="0" gs-w="{{ $kpi['w'] }}" gs-h="3">
                        <div class="grid-stack-item-content">
                            <div class="ca-kpi-card" style="--kpi-from:{{ $kpi['from'] }};--kpi-to:{{ $kpi['to'] }};--kpi-accent:{{ $kpi['accent'] }}">
                                <div class="ca-kpi-orb"></div>
                                <div class="ca-widget-handle flex items-center justify-between gap-2">
                                    <span class="ca-kpi-chip">{{ $kpi['badge'] }}</span>
                                    <span class="ca-kpi-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/>
                                        </svg>
                                    </span>
                                </div>
                                @php $isLongValue = strlen((string) $kpi['valor']) > 18; @endphp
                                <p class="mt-3 text-[11px] font-semibold text-slate-600 uppercase tracking-wide">{{ $kpi['titulo'] }}</p>
                                <p class="mt-1 font-black leading-tight text-slate-900 {{ $isLongValue ? 'text-lg' : 'text-2xl' }}">{{ $kpi['valor'] }}</p>
                                <p class="mt-2 text-xs font-medium text-slate-500">{{ $kpi['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="grid-stack-item" id="ca-chart-estado-basic" gs-x="0" gs-y="3" gs-w="6" gs-h="7">
                    <div class="grid-stack-item-content">
                        <div class="ca-widget-card">
                            <div class="ca-widget-handle flex items-start justify-between gap-2">
                                <div>
                                    <h2 class="text-sm font-bold text-gray-800">Distribucion por estado</h2>
                                    <p class="text-xs text-gray-400">Contratos de aplicaciones por estado interno</p>
                                </div>
                            </div>
                            <div class="mt-3 ca-widget-body">
                                <canvas id="chartEstadoContratos"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid-stack-item" id="ca-chart-venc-basic" gs-x="6" gs-y="3" gs-w="6" gs-h="7">
                    <div class="grid-stack-item-content">
                        <div class="ca-widget-card">
                            <div class="ca-widget-handle flex items-start justify-between gap-2">
                                <div>
                                    <h2 class="text-sm font-bold text-gray-800">Dias para vencimiento</h2>
                                    <p class="text-xs text-gray-400">Contratos activos con fecha fin mas cercana</p>
                                </div>
                            </div>
                            <div class="mt-3 ca-widget-body">
                                <canvas id="chartVencimientosContratos"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form method="GET" class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <div class="flex gap-2">
                <input type="text" name="q" value="{{ $q }}" placeholder="Filtrar lista local por aplicacion, proveedor, numero o SECOP..."
                       class="flex-1 rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
                <button class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#2563eb">Buscar</button>
            </div>
        </form>

        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="background:#f8fafc">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Aplicacion</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Contrato</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Vigencia</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Dias restantes</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">SECOP</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Estado</th>
                            <th class="px-4 py-3 text-right text-xs text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:#f1f5f9">
                        @forelse($contratos as $c)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-800">{{ $c->aplicacion }}</p>
                                    <p class="text-xs text-gray-400">{{ $c->proveedor }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-700">{{ $c->numero_contrato ?: 'N/A' }}</p>
                                    <p class="text-xs text-gray-400">$ {{ number_format((float) $c->valor_total, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <div>{{ optional($c->fecha_inicio)->format('Y-m-d') ?: 'N/A' }}</div>
                                    <div>{{ optional($c->fecha_fin)->format('Y-m-d') ?: 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    @php
                                        $diasRestantes = $c->fecha_fin
                                            ? now()->startOfDay()->diffInDays($c->fecha_fin->copy()->startOfDay(), false)
                                            : null;
                                    @endphp
                                    @if($diasRestantes === null)
                                        <span class="text-xs text-gray-400">Sin fecha fin</span>
                                    @elseif($diasRestantes < 0)
                                        <span class="text-xs font-semibold text-red-600">Vencido {{ abs($diasRestantes) }} dias</span>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-700">{{ $diasRestantes }} dias</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($c->secop_url)
                                        <a href="{{ $c->secop_url }}" target="_blank" class="text-blue-600 hover:underline text-xs">Abrir SECOP</a>
                                    @elseif($c->secop_proceso_id)
                                        <span class="text-xs text-gray-500">{{ $c->secop_proceso_id }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">Sin referencia</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs px-2 py-1 rounded-full" style="background:#f1f5f9;color:#334155">{{ strtoupper($c->estado) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2 flex-wrap justify-end">
                                        <a href="{{ route('contratos-aplicaciones.show', $c) }}" class="text-xs font-semibold text-green-700 hover:underline">Ver</a>
                                        @if(auth()->user()->hasAnyRole(['admin', 'admin_general', 'admin_secretaria', 'gobernador', 'secretario', 'jefe_unidad']))
                                            <form method="POST" action="{{ route('contratos-aplicaciones.sincronizar-uno', $c) }}" class="inline">
                                                @csrf
                                                <button class="text-xs font-semibold text-cyan-700 hover:underline">Actualizar SECOP</button>
                                            </form>

                                            <form method="POST" action="{{ route('contratos-aplicaciones.actualizar-activo', $c) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="activo" value="{{ $c->activo ? 0 : 1 }}">
                                                <button class="text-xs font-semibold {{ $c->activo ? 'text-orange-700' : 'text-emerald-700' }} hover:underline">
                                                    {{ $c->activo ? 'Marcar inactivo' : 'Marcar activo' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">No hay contratos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t" style="border-color:#f1f5f9">{{ $contratos->links() }}</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="{{ asset('vendor/gridstack/gridstack-all.js') }}"></script>
    <script>
        (function () {
            const estadoData = @json($chartEstado);
            const vencimientosData = @json($chartVencimientos);
            const chartRegistry = {};

            const estadoCanvas = document.getElementById('chartEstadoContratos');
            if (estadoCanvas && estadoData.labels.length > 0) {
                chartRegistry.estado = new Chart(estadoCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: estadoData.labels,
                        datasets: [{
                            data: estadoData.data,
                            backgroundColor: estadoData.colors,
                            borderColor: '#ffffff',
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            const vencCanvas = document.getElementById('chartVencimientosContratos');
            if (vencCanvas && vencimientosData.labels.length > 0) {
                chartRegistry.vencimientos = new Chart(vencCanvas, {
                    type: 'bar',
                    data: {
                        labels: vencimientosData.labels,
                        datasets: [{
                            label: 'Dias restantes',
                            data: vencimientosData.data,
                            backgroundColor: '#0f766e',
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            }

            const GridStackClass =
                (window.GridStack && typeof window.GridStack.init === 'function')
                    ? window.GridStack
                    : (window.GridStack && window.GridStack.GridStack && typeof window.GridStack.GridStack.init === 'function')
                        ? window.GridStack.GridStack
                        : null;

            const resizeCharts = () => {
                Object.values(chartRegistry).forEach(chart => {
                    if (chart && typeof chart.resize === 'function') {
                        chart.resize();
                    }
                });
            };

            const toInt = (value, fallback, min = 0) => {
                const parsed = Number(value);
                return Number.isFinite(parsed) ? Math.max(min, Math.round(parsed)) : fallback;
            };

            const readDefaultLayout = (canvas) => {
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

            const readStoredLayout = (storageKey, defaults) => {
                try {
                    const raw = localStorage.getItem(storageKey);
                    if (!raw) return null;
                    return normalizeLayout(JSON.parse(raw), defaults);
                } catch (_error) {
                    return null;
                }
            };

            const saveLayout = (grid, defaults, storageKey) => {
                const layout = defaults.map(item => {
                    const el = document.getElementById(item.id);
                    const node = el ? el.gridstackNode : null;
                    if (!node) return item;
                    return { id: item.id, x: node.x, y: node.y, w: node.w, h: node.h };
                });
                localStorage.setItem(storageKey, JSON.stringify(layout));
            };

            const setupLayoutCanvas = ({ canvasId, toggleId, resetId, stateId, storageKey }) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas) return;

                const editBtn = document.getElementById(toggleId);
                const resetBtn = document.getElementById(resetId);
                const stateLabel = document.getElementById(stateId);

                if (!GridStackClass) {
                    if (stateLabel) stateLabel.textContent = 'Grid no disponible';
                    [editBtn, resetBtn].forEach(btn => {
                        if (!btn) return;
                        btn.disabled = true;
                        btn.style.opacity = '0.55';
                        btn.style.cursor = 'not-allowed';
                    });
                    return;
                }

                const defaultLayout = readDefaultLayout(canvas);

                const grid = GridStackClass.init({
                    column: 12,
                    float: true,
                    margin: 12,
                    cellHeight: 44,
                    animate: true,
                    disableDrag: false,
                    disableResize: false,
                    handle: '.ca-widget-handle',
                    draggable: {
                        handle: '.ca-widget-handle',
                        cancel: 'a,button,input,textarea,select,option,label'
                    },
                    resizable: { handles: 'all' },
                }, canvas);

                applyLayout(grid, readStoredLayout(storageKey, defaultLayout) || defaultLayout, defaultLayout);

                const setEditMode = (enabled) => {
                    grid.setStatic(!enabled);
                    if (typeof grid.enableMove === 'function') {
                        grid.enableMove(enabled);
                    }
                    if (typeof grid.enableResize === 'function') {
                        grid.enableResize(enabled);
                    }
                    canvas.classList.toggle('ca-layout-editing', enabled);

                    if (editBtn) {
                        editBtn.classList.toggle('active', enabled);
                        editBtn.textContent = enabled ? 'Bloquear' : 'Organizar paneles';
                    }
                    if (stateLabel) {
                        stateLabel.textContent = enabled ? 'Editando' : 'Bloqueado';
                        stateLabel.style.color = enabled ? '#166534' : '#94a3b8';
                    }
                };

                let persistTimer = null;
                const persistLayout = () => {
                    saveLayout(grid, defaultLayout, storageKey);
                    window.requestAnimationFrame(resizeCharts);
                };

                const onGridChanged = () => {
                    if (persistTimer) window.clearTimeout(persistTimer);
                    persistTimer = window.setTimeout(persistLayout, 140);
                };

                grid.on('dragstop', onGridChanged);
                grid.on('resizestop', onGridChanged);

                setEditMode(false);

                if (editBtn) {
                    editBtn.addEventListener('click', () => {
                        const enabled = !canvas.classList.contains('ca-layout-editing');
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
            };

            setupLayoutCanvas({
                canvasId: 'caLayoutCanvasManage',
                toggleId: 'caLayoutToggleManage',
                resetId: 'caLayoutResetManage',
                stateId: 'caLayoutStateManage',
                storageKey: 'contratos.aplicaciones.layout.manage.v1'
            });

            setupLayoutCanvas({
                canvasId: 'caLayoutCanvasBasic',
                toggleId: 'caLayoutToggleBasic',
                resetId: 'caLayoutResetBasic',
                stateId: 'caLayoutStateBasic',
                storageKey: 'contratos.aplicaciones.layout.basic.v1'
            });

            window.addEventListener('resize', resizeCharts);
        })();
    </script>
</x-app-layout>
