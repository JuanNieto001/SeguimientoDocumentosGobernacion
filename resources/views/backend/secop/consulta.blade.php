<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    Consulta SECOP II — Datos Abiertos
                </h1>
                <p class="text-xs text-gray-400 mt-0.5">Información en tiempo real desde datos.gov.co</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5">

        @if(session('success'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('error') }}
        </div>
        @endif

        {{-- Estadísticas resumen --}}
        @if($estadisticas)
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#ede9fe">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Total Contratos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($estadisticas['total']) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#dbeafe">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Valor Total</p>
                        <p class="text-lg font-bold text-gray-900">$ {{ number_format($estadisticas['valor_total'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @php
                $activos = ($estadisticas['por_estado']['Activo']['cantidad'] ?? 0) + ($estadisticas['por_estado']['Borrador']['cantidad'] ?? 0);
                $cerrados = $estadisticas['por_estado']['Cerrado']['cantidad'] ?? 0;
            @endphp
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#dcfce7">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Activos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($activos) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Cerrados</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($cerrados) }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Buscador --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <form method="GET" action="{{ route('secop.consulta') }}">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#ede9fe">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Buscar en SECOP II</h3>
                </div>

                {{-- Búsqueda rápida --}}
                <div class="flex gap-3 mb-4">
                    <div class="flex-1 relative">
                        <input type="text" name="busqueda" value="{{ $busqueda ?? '' }}"
                               placeholder="Buscar por N° proceso, contrato, cédula o nombre del contratista..."
                               class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-purple-200 focus:border-purple-400 transition-all"
                               style="border-color:#e2e8f0">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:shadow-lg"
                            style="background:linear-gradient(135deg,#7c3aed,#9333ea)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Consultar
                    </button>
                </div>

                {{-- Filtros rápidos (visibles tras buscar) --}}
                @if($busqueda || count($contratos) > 0 || array_filter($filtros ?? []))
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Año</label>
                        <select name="anio" onchange="this.form.submit()" class="w-full border rounded-xl text-sm py-2 px-3" style="border-color:#e2e8f0">
                            <option value="">Todos los años</option>
                            @for($y = date('Y'); $y >= 2018; $y--)
                            <option value="{{ $y }}" {{ ($filtros['anio'] ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Estado</label>
                        <select name="estado" onchange="this.form.submit()" class="w-full border rounded-xl text-sm py-2 px-3" style="border-color:#e2e8f0">
                            <option value="">Todos</option>
                            <option value="Activo" {{ ($filtros['estado'] ?? '') === 'Activo' ? 'selected' : '' }}>Activo</option>
                            <option value="Borrador" {{ ($filtros['estado'] ?? '') === 'Borrador' ? 'selected' : '' }}>Borrador</option>
                            <option value="Cerrado" {{ ($filtros['estado'] ?? '') === 'Cerrado' ? 'selected' : '' }}>Cerrado</option>
                            <option value="Liquidado" {{ ($filtros['estado'] ?? '') === 'Liquidado' ? 'selected' : '' }}>Liquidado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tipo de Contrato</label>
                        <select name="tipo_contrato" onchange="this.form.submit()" class="w-full border rounded-xl text-sm py-2 px-3" style="border-color:#e2e8f0">
                            <option value="">Todos</option>
                            <option value="Prestación de servicios" {{ ($filtros['tipo_contrato'] ?? '') === 'Prestación de servicios' ? 'selected' : '' }}>Prestación de Servicios</option>
                            <option value="Compraventa" {{ ($filtros['tipo_contrato'] ?? '') === 'Compraventa' ? 'selected' : '' }}>Compraventa</option>
                            <option value="Suministros" {{ ($filtros['tipo_contrato'] ?? '') === 'Suministros' ? 'selected' : '' }}>Suministros</option>
                            <option value="Consultoría" {{ ($filtros['tipo_contrato'] ?? '') === 'Consultoría' ? 'selected' : '' }}>Consultoría</option>
                            <option value="Obra" {{ ($filtros['tipo_contrato'] ?? '') === 'Obra' ? 'selected' : '' }}>Obra</option>
                            <option value="Interventoría" {{ ($filtros['tipo_contrato'] ?? '') === 'Interventoría' ? 'selected' : '' }}>Interventoría</option>
                            <option value="Arrendamiento" {{ ($filtros['tipo_contrato'] ?? '') === 'Arrendamiento' ? 'selected' : '' }}>Arrendamiento</option>
                            <option value="Seguros" {{ ($filtros['tipo_contrato'] ?? '') === 'Seguros' ? 'selected' : '' }}>Seguros</option>
                            <option value="Concesión" {{ ($filtros['tipo_contrato'] ?? '') === 'Concesión' ? 'selected' : '' }}>Concesión</option>
                            <option value="Comodato" {{ ($filtros['tipo_contrato'] ?? '') === 'Comodato' ? 'selected' : '' }}>Comodato</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Modalidad</label>
                        <select name="modalidad" onchange="this.form.submit()" class="w-full border rounded-xl text-sm py-2 px-3" style="border-color:#e2e8f0">
                            <option value="">Todas</option>
                            <option value="Contratación directa" {{ ($filtros['modalidad'] ?? '') === 'Contratación directa' ? 'selected' : '' }}>Contratación Directa</option>
                            <option value="Mínima cuantía" {{ ($filtros['modalidad'] ?? '') === 'Mínima cuantía' ? 'selected' : '' }}>Mínima Cuantía</option>
                            <option value="Selección abreviada" {{ ($filtros['modalidad'] ?? '') === 'Selección abreviada' ? 'selected' : '' }}>Selección Abreviada</option>
                            <option value="Licitación pública" {{ ($filtros['modalidad'] ?? '') === 'Licitación pública' ? 'selected' : '' }}>Licitación Pública</option>
                            <option value="Concurso de méritos" {{ ($filtros['modalidad'] ?? '') === 'Concurso de méritos' ? 'selected' : '' }}>Concurso de Méritos</option>
                            <option value="régimen especial" {{ ($filtros['modalidad'] ?? '') === 'régimen especial' ? 'selected' : '' }}>Régimen Especial</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:shadow-lg"
                                style="background:linear-gradient(135deg,#7c3aed,#9333ea)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Filtrar
                        </button>
                    </div>
                </div>

                {{-- Filtros avanzados --}}
                <div x-data="{ open: {{ !empty($filtros['contratista'] ?? '') || !empty($filtros['cedula'] ?? '') || !empty($filtros['objeto'] ?? '') || !empty($filtros['fecha_desde'] ?? '') || !empty($filtros['fecha_hasta'] ?? '') ? 'true': 'false' }} }">
                    <button type="button" @click="open = !open"
                            class="text-xs font-medium text-purple-600 hover:text-purple-800 flex items-center gap-1 mb-3 transition-colors">
                        <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        Más filtros
                    </button>
                    <div x-show="open" x-transition class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Contratista</label>
                            <input type="text" name="contratista" value="{{ $filtros['contratista'] ?? '' }}"
                                   placeholder="Nombre del contratista..."
                                   class="w-full border rounded-xl text-sm py-2 px-3" style="border-color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Cédula / NIT Contratista</label>
                            <input type="text" name="cedula" value="{{ $filtros['cedula'] ?? '' }}"
                                   placeholder="Número de documento..."
                                   class="w-full border rounded-xl text-sm py-2 px-3" style="border-color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Objeto (palabra clave)</label>
                            <input type="text" name="objeto" value="{{ $filtros['objeto'] ?? '' }}"
                                   placeholder="Buscar en el objeto del contrato..."
                                   class="w-full border rounded-xl text-sm py-2 px-3" style="border-color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Fecha firma desde</label>
                            <input type="date" name="fecha_desde" value="{{ $filtros['fecha_desde'] ?? '' }}"
                                   class="w-full border rounded-xl text-sm py-2 px-3" style="border-color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Fecha firma hasta</label>
                            <input type="date" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] ?? '' }}"
                                   class="w-full border rounded-xl text-sm py-2 px-3" style="border-color:#e2e8f0">
                        </div>
                    </div>
                </div>

                {{-- Filtros activos --}}
                @php
                    $filtrosActivos = array_filter($filtros ?? []);
                @endphp
                @if(count($filtrosActivos) > 0 || $busqueda)
                <div class="flex flex-wrap items-center gap-2 mt-3 pt-3" style="border-top:1px solid #f1f5f9">
                    <span class="text-xs text-gray-400 font-medium">Filtros activos:</span>
                    @if($busqueda)
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium" style="background:#ede9fe;color:#7c3aed">
                        🔍 {{ \Illuminate\Support\Str::limit($busqueda, 25) }}
                    </span>
                    @endif
                    @php
                        $labelMap = [
                            'anio' => 'Año',
                            'estado' => 'Estado',
                            'tipo_contrato' => 'Tipo',
                            'modalidad' => 'Modalidad',
                            'contratista' => 'Contratista',
                            'cedula' => 'Cédula/NIT',
                            'objeto' => 'Objeto',
                            'fecha_desde' => 'Desde',
                            'fecha_hasta' => 'Hasta',
                        ];
                    @endphp
                    @foreach($filtrosActivos as $key => $value)
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium" style="background:#f1f5f9;color:#64748b">
                        {{ $labelMap[$key] ?? $key }}: {{ \Illuminate\Support\Str::limit($value, 20) }}
                    </span>
                    @endforeach
                    <a href="{{ route('secop.consulta') }}" class="text-xs text-red-400 hover:text-red-600 font-medium ml-1 transition-colors">✕ Limpiar todo</a>
                </div>
                @endif
                @endif
            </form>
        </div>

        {{-- Resultados --}}
        <div class="bg-white rounded-2xl border" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <h3 class="text-sm font-bold text-gray-700">Contratos SECOP II</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:#ede9fe;color:#7c3aed">{{ count($contratos) }} resultados</span>
                </div>
                @if($busqueda || array_filter($filtros ?? []))
                <a href="{{ route('secop.consulta') }}" class="text-xs text-gray-400 hover:text-gray-600 transition-colors flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Limpiar filtros
                </a>
                @endif
            </div>

            @if(count($contratos) === 0)
            <div class="py-16 text-center">
                <svg class="mx-auto w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-sm font-medium text-gray-400">No se encontraron contratos</p>
                <p class="text-xs text-gray-300 mt-1">Intenta con otros criterios de búsqueda</p>
            </div>
            @else
            <div class="divide-y" style="border-color:#f8fafc">
                @foreach($contratos as $c)
                @php
                    $estado = $c['estado_contrato'] ?? 'Sin estado';
                    $colorEstado = match($estado) {
                        'Activo' => ['bg' => '#dcfce7', 'text' => '#15803d'],
                        'Borrador' => ['bg' => '#dbeafe', 'text' => '#2563eb'],
                        'Cerrado' => ['bg' => '#fef3c7', 'text' => '#d97706'],
                        'Liquidado' => ['bg' => '#f3e8ff', 'text' => '#7c3aed'],
                        default => ['bg' => '#f1f5f9', 'text' => '#64748b'],
                    };
                    $idContrato = $c['id_contrato'] ?? '';
                @endphp
                <a href="{{ route('secop.consulta.detalle', ['idContrato' => $idContrato]) }}"
                   class="block px-5 py-4 hover:bg-gray-50 transition-colors group">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-sm text-gray-900 group-hover:text-purple-700 transition-colors">
                                    {{ $c['referencia_del_contrato'] ?? $c['proceso_de_compra'] ?? 'Sin referencia' }}
                                </span>
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold shrink-0"
                                      style="background:{{ $colorEstado['bg'] }};color:{{ $colorEstado['text'] }}">
                                    {{ $estado }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 line-clamp-2 mb-2">
                                {{ \Illuminate\Support\Str::limit($c['objeto_del_contrato'] ?? $c['descripcion_del_proceso'] ?? 'Sin descripción', 150) }}
                            </p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
                                @if(!empty($c['proveedor_adjudicado']))
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $c['proveedor_adjudicado'] }}
                                    @if(!empty($c['documento_proveedor']) && $c['documento_proveedor'] !== 'No Definido')
                                        <span class="text-gray-300">&middot;</span> CC/NIT {{ $c['documento_proveedor'] }}
                                    @endif
                                </span>
                                @endif
                                @if(!empty($c['modalidad_de_contratacion']))
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    {{ $c['modalidad_de_contratacion'] }}
                                </span>
                                @endif
                                @if(!empty($c['fecha_de_firma']))
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ \Carbon\Carbon::parse($c['fecha_de_firma'])->format('d/m/Y') }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-bold text-gray-900">$ {{ number_format((float)($c['valor_del_contrato'] ?? 0), 0, ',', '.') }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5 uppercase tracking-wide">Valor contrato</p>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Nota de fuente --}}
        <div class="flex items-center justify-center gap-2 py-3">
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-xs text-gray-300">Datos obtenidos en tiempo real desde
                <a href="https://www.datos.gov.co/Gastos-Gubernamentales/SECOP-II-Contratos/jbjy-vk9h" target="_blank" class="text-purple-400 hover:text-purple-600 transition-colors">datos.gov.co — SECOP II Contratos</a>
            </p>
        </div>
    </div>
</x-app-layout>
