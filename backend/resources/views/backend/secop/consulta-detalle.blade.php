<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-0.5">
                    <a href="{{ route('secop.consulta') }}" class="hover:text-purple-600 transition-colors">Consulta SECOP II</a>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">{{ $contrato['referencia_del_contrato'] ?? $contrato['id_contrato'] ?? 'Detalle' }}</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Detalle del Contrato SECOP II
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('secop.consulta.refrescar', ['idContrato' => $contrato['id_contrato'] ?? '']) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-purple-600 hover:bg-purple-50 transition-all"
                            style="border-color:#e9d5ff">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Actualizar
                    </button>
                </form>
                <a href="{{ route('secop.consulta') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50 transition-all"
                   style="border-color:#e2e8f0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $estado = $contrato['estado_contrato'] ?? 'Sin estado';
        $colorEstado = match($estado) {
            'Activo' => ['bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#bbf7d0'],
            'Borrador' => ['bg' => '#dbeafe', 'text' => '#2563eb', 'border' => '#bfdbfe'],
            'Cerrado' => ['bg' => '#fef3c7', 'text' => '#d97706', 'border' => '#fde68a'],
            'Liquidado' => ['bg' => '#f3e8ff', 'text' => '#7c3aed', 'border' => '#e9d5ff'],
            default => ['bg' => '#f1f5f9', 'text' => '#64748b', 'border' => '#e2e8f0'],
        };
    @endphp

    <div class="p-6 space-y-5">

        @if(session('success'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Estado y encabezado --}}
        <div class="bg-white rounded-2xl border p-6" style="border-color:#e2e8f0">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ $contrato['referencia_del_contrato'] ?? 'Sin referencia' }}
                        </h2>
                        <span class="text-xs px-3 py-1 rounded-full font-bold"
                              style="background:{{ $colorEstado['bg'] }};color:{{ $colorEstado['text'] }};border:1px solid {{ $colorEstado['border'] }}">
                            {{ $estado }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        {{ $contrato['objeto_del_contrato'] ?? $contrato['descripcion_del_proceso'] ?? 'Sin descripción' }}
                    </p>
                </div>
                @if(!empty($contrato['urlproceso']['url']))
                <a href="{{ $contrato['urlproceso']['url'] }}" target="_blank"
                   class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:shadow-lg ml-4"
                   style="background:linear-gradient(135deg,#7c3aed,#9333ea)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Ver en SECOP II
                </a>
                @endif
            </div>

            {{-- IDs --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-xl" style="background:#f8fafc">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Proceso de Compra</p>
                    <p class="text-sm font-mono font-medium text-gray-700 mt-0.5">{{ $contrato['proceso_de_compra'] ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">ID Contrato SECOP</p>
                    <p class="text-sm font-mono font-medium text-gray-700 mt-0.5">{{ $contrato['id_contrato'] ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Última Actualización</p>
                    <p class="text-sm font-medium text-gray-700 mt-0.5">
                        {{ !empty($contrato['ultima_actualizacion']) ? \Carbon\Carbon::parse($contrato['ultima_actualizacion'])->format('d/m/Y H:i') : 'N/D' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Información del Contrato --}}
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#dbeafe">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Información del Contrato</h3>
                </div>
                <dl class="space-y-3 text-sm">
                    @php
                        $infoContrato = [
                            'Tipo de Contrato' => $contrato['tipo_de_contrato'] ?? 'N/D',
                            'Modalidad' => $contrato['modalidad_de_contratacion'] ?? 'N/D',
                            'Justificación' => $contrato['justificacion_modalidad_de'] ?? 'N/D',
                            'Duración' => $contrato['duraci_n_del_contrato'] ?? 'N/D',
                            'Categoría' => $contrato['descripcion_del_proceso'] ? \Illuminate\Support\Str::limit($contrato['descripcion_del_proceso'], 80) : ($contrato['codigo_de_categoria_principal'] ?? 'N/D'),
                            'Orden' => $contrato['orden'] ?? 'N/D',
                            'Sector' => $contrato['sector'] ?? 'N/D',
                        ];
                    @endphp
                    @foreach($infoContrato as $label => $value)
                    <div class="flex justify-between items-start gap-4 py-1 border-b" style="border-color:#f8fafc">
                        <dt class="text-gray-400 shrink-0 text-xs uppercase tracking-wide font-medium">{{ $label }}</dt>
                        <dd class="text-gray-700 font-medium text-right">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>

            {{-- Fechas Clave --}}
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#dcfce7">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Fechas Clave</h3>
                </div>
                <div class="space-y-3">
                    @php
                        $fechas = [
                            ['label' => 'Firma', 'fecha' => $contrato['fecha_de_firma'] ?? null, 'icon' => '✍️'],
                            ['label' => 'Inicio', 'fecha' => $contrato['fecha_de_inicio_del_contrato'] ?? null, 'icon' => '🟢'],
                            ['label' => 'Fin', 'fecha' => $contrato['fecha_de_fin_del_contrato'] ?? null, 'icon' => '🔴'],
                        ];
                    @endphp
                    @foreach($fechas as $f)
                    <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#f8fafc">
                        <span class="text-lg">{{ $f['icon'] }}</span>
                        <div class="flex-1">
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">{{ $f['label'] }}</p>
                            <p class="text-sm font-bold text-gray-700">
                                {{ $f['fecha'] ? \Carbon\Carbon::parse($f['fecha'])->format('d/m/Y') : 'No definida' }}
                            </p>
                        </div>
                        @if($f['fecha'])
                        <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($f['fecha'])->diffForHumans() }}</span>
                        @endif
                    </div>
                    @endforeach

                    @if(!empty($contrato['condiciones_de_entrega']) && $contrato['condiciones_de_entrega'] !== 'No Definido')
                    <div class="p-3 rounded-xl" style="background:#fef3c7">
                        <p class="text-xs text-amber-700 font-medium">Condiciones de entrega</p>
                        <p class="text-sm text-amber-800 mt-0.5">{{ $contrato['condiciones_de_entrega'] }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Contratista --}}
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#fef3c7">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Contratista</h3>
                </div>
                <dl class="space-y-3 text-sm">
                    @php
                        $infoContratista = [
                            'Nombre / Razón Social' => $contrato['proveedor_adjudicado'] ?? 'N/D',
                            'Tipo Documento' => $contrato['tipodocproveedor'] ?? 'N/D',
                            'Documento' => $contrato['documento_proveedor'] ?? 'N/D',
                            'Es Grupo' => ($contrato['es_grupo'] ?? 'No') === 'Si' ? 'Sí — Grupo empresarial' : 'No',
                            'Es PYME' => ($contrato['es_pyme'] ?? 'No') === 'Si' ? 'Sí' : 'No',
                        ];
                    @endphp
                    @foreach($infoContratista as $label => $value)
                    <div class="flex justify-between items-start gap-4 py-1 border-b" style="border-color:#f8fafc">
                        <dt class="text-gray-400 shrink-0 text-xs uppercase tracking-wide font-medium">{{ $label }}</dt>
                        <dd class="text-gray-700 font-medium text-right">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>

            {{-- Valores Financieros --}}
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#ede9fe">
                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Valores Financieros</h3>
                </div>
                @php
                    $valores = [
                        ['label' => 'Valor del Contrato', 'valor' => $contrato['valor_del_contrato'] ?? 0, 'color' => '#14532d', 'bg' => '#dcfce7'],
                        ['label' => 'Valor Pagado', 'valor' => $contrato['valor_pagado'] ?? 0, 'color' => '#1e40af', 'bg' => '#dbeafe'],
                        ['label' => 'Valor Facturado', 'valor' => $contrato['valor_facturado'] ?? 0, 'color' => '#7c3aed', 'bg' => '#ede9fe'],
                        ['label' => 'Pendiente de Pago', 'valor' => $contrato['valor_pendiente_de_pago'] ?? 0, 'color' => '#dc2626', 'bg' => '#fee2e2'],
                        ['label' => 'Pendiente Ejecución', 'valor' => $contrato['valor_pendiente_de_ejecucion'] ?? 0, 'color' => '#d97706', 'bg' => '#fef3c7'],
                    ];
                    $valorContrato = (float) ($contrato['valor_del_contrato'] ?? 0);
                    $valorPagado = (float) ($contrato['valor_pagado'] ?? 0);
                    $pctEjecucion = $valorContrato > 0 ? round(($valorPagado / $valorContrato) * 100, 1) : 0;
                @endphp

                {{-- Barra de ejecución --}}
                <div class="mb-4 p-3 rounded-xl" style="background:#f8fafc">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-semibold text-gray-500">Ejecución financiera</span>
                        <span class="text-sm font-bold" style="color:#7c3aed">{{ $pctEjecucion }}%</span>
                    </div>
                    <div class="w-full h-2.5 rounded-full" style="background:#e2e8f0">
                        <div class="h-2.5 rounded-full transition-all duration-500" style="width:{{ min($pctEjecucion, 100) }}%;background:linear-gradient(90deg,#7c3aed,#9333ea)"></div>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach($valores as $v)
                    <div class="flex items-center justify-between p-2.5 rounded-lg" style="background:{{ $v['bg'] }}20">
                        <span class="text-xs font-medium" style="color:{{ $v['color'] }}">{{ $v['label'] }}</span>
                        <span class="text-sm font-bold" style="color:{{ $v['color'] }}">$ {{ number_format((float)$v['valor'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Valores adicionales --}}
                @php
                    $valoresExtra = [
                        ['label' => 'Pago Adelantado', 'valor' => $contrato['valor_de_pago_adelantado'] ?? 0, 'color' => '#0e7490', 'bg' => '#cffafe'],
                        ['label' => 'Valor Amortizado', 'valor' => $contrato['valor_amortizado'] ?? 0, 'color' => '#4338ca', 'bg' => '#e0e7ff'],
                        ['label' => 'Saldo CDP', 'valor' => $contrato['saldo_cdp'] ?? 0, 'color' => '#0f766e', 'bg' => '#ccfbf1'],
                        ['label' => 'Saldo Vigencia', 'valor' => $contrato['saldo_vigencia'] ?? 0, 'color' => '#b45309', 'bg' => '#fef3c7'],
                    ];
                    $tieneValoresExtra = collect($valoresExtra)->contains(fn($v) => (float)$v['valor'] > 0);
                @endphp
                @if($tieneValoresExtra)
                <div class="mt-3 space-y-2">
                    @foreach($valoresExtra as $v)
                        @if((float)$v['valor'] > 0)
                        <div class="flex items-center justify-between p-2.5 rounded-lg" style="background:{{ $v['bg'] }}20">
                            <span class="text-xs font-medium" style="color:{{ $v['color'] }}">{{ $v['label'] }}</span>
                            <span class="text-sm font-bold" style="color:{{ $v['color'] }}">$ {{ number_format((float)$v['valor'], 0, ',', '.') }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- Origen y destino de recursos --}}
                @if(!empty($contrato['origen_de_los_recursos']) || !empty($contrato['destino_gasto']))
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @if(!empty($contrato['origen_de_los_recursos']))
                    <div class="p-2.5 rounded-lg" style="background:#f8fafc">
                        <p class="text-xs text-gray-400 font-medium mb-0.5">Origen de los recursos</p>
                        <p class="text-sm text-gray-700 font-medium">{{ $contrato['origen_de_los_recursos'] }}</p>
                    </div>
                    @endif
                    @if(!empty($contrato['destino_gasto']))
                    <div class="p-2.5 rounded-lg" style="background:#f8fafc">
                        <p class="text-xs text-gray-400 font-medium mb-0.5">Destino del gasto</p>
                        <p class="text-sm text-gray-700 font-medium">{{ $contrato['destino_gasto'] }}</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Representante Legal y Supervisor --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {{-- Representante Legal --}}
            @if(!empty($contrato['nombre_representante_legal']))
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#dbeafe">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Representante Legal</h3>
                </div>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-400 text-xs">Nombre</dt><dd class="font-medium text-gray-700">{{ $contrato['nombre_representante_legal'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400 text-xs">Documento</dt><dd class="font-medium text-gray-700">{{ $contrato['tipo_de_identificaci_n_representante_legal'] ?? '' }} {{ $contrato['identificaci_n_representante_legal'] ?? '' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400 text-xs">Nacionalidad</dt><dd class="font-medium text-gray-700">{{ $contrato['nacionalidad_representante_legal'] ?? 'N/D' }}</dd></div>
                </dl>
            </div>
            @endif

            {{-- Supervisor --}}
            @if(!empty($contrato['nombre_supervisor']))
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#fce7f3">
                        <svg class="w-3.5 h-3.5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Supervisor del Contrato</h3>
                </div>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-400 text-xs">Nombre</dt><dd class="font-medium text-gray-700">{{ $contrato['nombre_supervisor'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400 text-xs">Documento</dt><dd class="font-medium text-gray-700">{{ $contrato['tipo_de_documento_supervisor'] ?? '' }} {{ $contrato['n_mero_de_documento_supervisor'] ?? '' }}</dd></div>
                </dl>
            </div>
            @endif
        </div>

        {{-- Entidad Contratante --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#e0f2fe">
                    <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-700">Entidad Contratante</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Entidad</p>
                    <p class="font-medium text-gray-700">{{ $contrato['nombre_entidad'] ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">NIT</p>
                    <p class="font-medium text-gray-700">{{ $contrato['nit_entidad'] ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Departamento</p>
                    <p class="font-medium text-gray-700">{{ $contrato['departamento'] ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Ciudad</p>
                    <p class="font-medium text-gray-700">{{ $contrato['ciudad'] ?? 'N/D' }}</p>
                </div>
            </div>
        </div>

        {{-- Características Adicionales --}}
        @php
            $flags = [
                'Habilita Pago Adelantado' => $contrato['habilita_pago_adelantado'] ?? null,
                'Liquidación' => $contrato['liquidaci_n'] ?? null,
                'Obligación Ambiental' => $contrato['obligaci_n_ambiental'] ?? null,
                'Obligaciones Postconsumo' => $contrato['obligaciones_postconsumo'] ?? null,
                'Reversión' => $contrato['reversion'] ?? null,
                'Días Adicionados' => $contrato['dias_adicionados'] ?? null,
            ];
            $flagsActivos = array_filter($flags, fn($v) => $v !== null && $v !== '' && $v !== '0');
            $esPostConflicto = ($contrato['espostconflicto'] ?? 'No') === 'Si';
        @endphp
        @if(count($flagsActivos) > 0 || $esPostConflicto)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {{-- Flags del contrato --}}
            @if(count($flagsActivos) > 0)
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#f3e8ff">
                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Características Adicionales</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($flagsActivos as $label => $value)
                    @php
                        $esSi = in_array(strtolower($value), ['si', 'sí', 'yes', '1', 'true']);
                        $badgeBg = $esSi ? '#dcfce7' : '#f1f5f9';
                        $badgeText = $esSi ? '#15803d' : '#64748b';
                        $badgeBorder = $esSi ? '#bbf7d0' : '#e2e8f0';
                        $displayVal = is_numeric($value) ? $value : ($esSi ? 'Sí' : $value);
                    @endphp
                    <span class="text-xs px-3 py-1.5 rounded-full font-medium"
                          style="background:{{ $badgeBg }};color:{{ $badgeText }};border:1px solid {{ $badgeBorder }}">
                        {{ $label }}: {{ $displayVal }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Post-conflicto --}}
            @if($esPostConflicto)
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#fef3c7">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Información Post-conflicto</h3>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="p-3 rounded-xl" style="background:#fef3c7">
                        <span class="text-xs font-bold text-amber-700 uppercase">Contrato Post-conflicto</span>
                    </div>
                    @if(!empty($contrato['pilares_del_acuerdo']))
                    <div class="p-2.5 rounded-lg" style="background:#f8fafc">
                        <p class="text-xs text-gray-400 font-medium mb-0.5">Pilares del Acuerdo</p>
                        <p class="text-sm text-gray-700 font-medium">{{ $contrato['pilares_del_acuerdo'] }}</p>
                    </div>
                    @endif
                    @if(!empty($contrato['puntos_del_acuerdo']))
                    <div class="p-2.5 rounded-lg" style="background:#f8fafc">
                        <p class="text-xs text-gray-400 font-medium mb-0.5">Puntos del Acuerdo</p>
                        <p class="text-sm text-gray-700 font-medium">{{ $contrato['puntos_del_acuerdo'] }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Fuente --}}
        <div class="flex items-center justify-center gap-2 py-3">
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-xs text-gray-300">Datos obtenidos en tiempo real desde
                <a href="https://www.datos.gov.co/Gastos-Gubernamentales/SECOP-II-Contratos/jbjy-vk9h" target="_blank" class="text-purple-400 hover:text-purple-600 transition-colors">datos.gov.co — SECOP II</a>
                · Última actualización: {{ !empty($contrato['ultima_actualizacion']) ? \Carbon\Carbon::parse($contrato['ultima_actualizacion'])->format('d/m/Y H:i') : 'N/D' }}
            </p>
        </div>
    </div>
</x-app-layout>
