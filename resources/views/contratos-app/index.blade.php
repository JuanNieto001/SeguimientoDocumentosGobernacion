<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Contratos de Aplicaciones</h1>
                <p class="text-xs text-gray-400 mt-1">Contratos de software y servicios tecnológicos — Gobernación</p>
            </div>
            @can('contratos_app.crear')
            <a href="{{ route('contratos-app.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-xl text-white transition-colors"
               style="background:#166534">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo contrato
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium"
             style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- KPIs --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-2xl p-4 shadow-sm border" style="background:#fff;border-color:#e2e8f0">
                <p class="text-xs font-medium" style="color:#64748b">Contratos activos</p>
                <p class="text-3xl font-bold mt-1" style="color:#15803d">{{ $totalActivos }}</p>
            </div>
            <div class="rounded-2xl p-4 shadow-sm border" style="background:#fff;border-color:#e2e8f0">
                <p class="text-xs font-medium" style="color:#64748b">Por vencer (30 días)</p>
                <p class="text-3xl font-bold mt-1" style="color:#d97706">{{ $proxVencer }}</p>
            </div>
            <div class="rounded-2xl p-4 shadow-sm border" style="background:#fff;border-color:#e2e8f0">
                <p class="text-xs font-medium" style="color:#64748b">Vencidos</p>
                <p class="text-3xl font-bold mt-1" style="color:#dc2626">{{ $vencidos }}</p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="rounded-2xl p-4 shadow-sm border" style="background:#fff;border-color:#e2e8f0">
            <form method="GET" action="{{ route('contratos-app.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Aplicación, proveedor, contrato, SECOP…"
                           class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2"
                           style="border-color:#e2e8f0;focus:ring-color:#166534">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                    <select name="estado" class="text-sm rounded-xl border px-3 py-2" style="border-color:#e2e8f0">
                        <option value="">Todos</option>
                        <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="por_vencer" {{ request('estado') === 'por_vencer' ? 'selected' : '' }}>Por vencer</option>
                        <option value="vencido" {{ request('estado') === 'vencido' ? 'selected' : '' }}>Vencido</option>
                        <option value="cancelado" {{ request('estado') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                @if($secretarias->isNotEmpty())
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Secretaría</label>
                    <select name="secretaria_id" class="text-sm rounded-xl border px-3 py-2" style="border-color:#e2e8f0">
                        <option value="">Todas</option>
                        @foreach($secretarias as $sec)
                        <option value="{{ $sec->id }}" {{ request('secretaria_id') == $sec->id ? 'selected' : '' }}>
                            {{ $sec->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <button type="submit"
                        class="px-4 py-2 text-xs font-semibold rounded-xl text-white"
                        style="background:#1e3a5f">
                    Filtrar
                </button>
                <a href="{{ route('contratos-app.index') }}"
                   class="px-4 py-2 text-xs font-medium rounded-xl border"
                   style="border-color:#e2e8f0;color:#64748b">
                    Limpiar
                </a>
            </form>
        </div>

        {{-- Tabla --}}
        <div class="rounded-2xl shadow-sm border overflow-hidden" style="background:#fff;border-color:#e2e8f0">
            @if($contratos->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm">No se encontraron contratos de aplicaciones.</p>
                @can('contratos_app.crear')
                <a href="{{ route('contratos-app.create') }}"
                   class="mt-3 inline-block text-xs font-semibold px-4 py-2 rounded-xl text-white"
                   style="background:#166534">
                    Registrar primer contrato
                </a>
                @endcan
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aplicación</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Proveedor</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">N.° Contrato</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Inicio</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fin</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SECOP</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:#f1f5f9">
                        @foreach($contratos as $c)
                        @php
                            $estadoEfectivo = $c->estado_efectivo;
                            $estadoColors = [
                                'activo'     => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0', 'label' => 'Activo'],
                                'por_vencer' => ['bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a', 'label' => 'Por vencer'],
                                'vencido'    => ['bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca', 'label' => 'Vencido'],
                                'cancelado'  => ['bg' => '#f8fafc', 'text' => '#64748b', 'border' => '#e2e8f0', 'label' => 'Cancelado'],
                            ];
                            $sc = $estadoColors[$estadoEfectivo] ?? $estadoColors['cancelado'];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                <a href="{{ route('contratos-app.show', $c) }}" class="hover:underline">
                                    {{ $c->nombre_aplicacion }}
                                </a>
                                @if($c->secretaria)
                                <span class="block text-[11px] text-gray-400">{{ $c->secretaria->nombre }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $c->proveedor ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $c->numero_contrato ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $c->fecha_inicio->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $c->fecha_fin->format('d/m/Y') }}
                                @if($estadoEfectivo === 'activo')
                                <span class="block text-[11px] text-gray-400">{{ $c->dias_restantes }} días</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border"
                                      style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};border-color:{{ $sc['border'] }}">
                                    {{ $sc['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($c->secop_id)
                                <a href="{{ $c->secop_url ?: route('secop.consulta', ['q' => $c->secop_id]) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-xs font-medium"
                                   style="color:#1e40af">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    {{ $c->secop_id }}
                                </a>
                                @else
                                <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('contratos-app.show', $c) }}"
                                       class="text-xs px-2 py-1 rounded-lg border hover:bg-gray-50 transition-colors"
                                       style="border-color:#e2e8f0;color:#374151">
                                        Ver
                                    </a>
                                    @can('contratos_app.editar')
                                    <a href="{{ route('contratos-app.edit', $c) }}"
                                       class="text-xs px-2 py-1 rounded-lg border hover:bg-gray-50 transition-colors"
                                       style="border-color:#e2e8f0;color:#374151">
                                        Editar
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($contratos->hasPages())
            <div class="px-4 py-3 border-t" style="border-color:#f1f5f9">
                {{ $contratos->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>
</x-app-layout>
