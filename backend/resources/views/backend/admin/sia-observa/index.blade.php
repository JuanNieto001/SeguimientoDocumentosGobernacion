{{-- Archivo: backend/resources/views/backend/admin/sia-observa/index.blade.php | Proposito: Gestion admin de procesos para SIA Observa. --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">SIA Observa</h1>
                <p class="text-xs text-gray-400 mt-1">Bandeja administrativa para consulta y asignacion de accesos</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        <div x-data="{
                secretariaId: '{{ request('secretaria_id') }}',
                unidades: {!! isset($unidades) ? $unidades->toJson() : '[]' !!},
                async fetchUnidades() {
                    if (!this.secretariaId) { this.unidades = []; return; }
                    try {
                        const res = await fetch('/api/secretarias/' + this.secretariaId + '/unidades');
                        this.unidades = await res.json();
                    } catch(e) {
                        this.unidades = [];
                    }
                }
            }"
            class="bg-white rounded-2xl p-4"
            style="border:1px solid #e2e8f0">

            <form method="GET" action="{{ route('admin.sia-observa.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1">Cedula contratista</label>
                        <input type="text" name="cedula" value="{{ request('cedula') }}"
                               placeholder="Ej: 12345678"
                               class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                               style="background:#f8fafc">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1">Nombre contratista</label>
                        <input type="text" name="nombre" value="{{ request('nombre') }}"
                               placeholder="Nombre completo"
                               class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                               style="background:#f8fafc">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1">Codigo proceso</label>
                        <input type="text" name="codigo_proceso" value="{{ request('codigo_proceso') }}"
                               placeholder="COD-2026-..."
                               class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                               style="background:#f8fafc">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1">Codigo contrato</label>
                        <input type="text" name="codigo_contrato" value="{{ request('codigo_contrato') }}"
                               placeholder="Numero de contrato"
                               class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                               style="background:#f8fafc">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1">Codigo SECOP</label>
                        <input type="text" name="codigo_secop" value="{{ request('codigo_secop') }}"
                               placeholder="SECOP o proceso SECOP"
                               class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                               style="background:#f8fafc">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1">Secretaria</label>
                        <select name="secretaria_id"
                                x-model="secretariaId"
                                @change="fetchUnidades(); $refs.unidadSelect.value = ''"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all appearance-none"
                                style="background:#f8fafc">
                            <option value="">Todas</option>
                            @foreach($secretarias as $sec)
                                <option value="{{ $sec->id }}" {{ request('secretaria_id') == $sec->id ? 'selected' : '' }}>
                                    {{ $sec->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1">Unidad</label>
                        <select name="unidad_id" x-ref="unidadSelect"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all appearance-none"
                                style="background:#f8fafc"
                                :disabled="!secretariaId">
                            <option value="">Todas</option>
                            <template x-for="u in unidades" :key="u.id">
                                <option :value="u.id" x-text="u.nombre" :selected="u.id == {{ request('unidad_id', 0) }}"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-semibold shadow-sm hover:shadow-md hover:opacity-95 transition-all"
                            style="background:linear-gradient(135deg,#15803d,#14532d)">
                        Filtrar
                    </button>

                    @if(request()->hasAny(['cedula', 'nombre', 'codigo_proceso', 'codigo_contrato', 'codigo_secop', 'secretaria_id', 'unidad_id']))
                    <a href="{{ route('admin.sia-observa.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-gray-600 hover:bg-gray-100 transition-all"
                       style="border:1px solid #e2e8f0">
                        Limpiar filtros
                    </a>
                    @endif

                    <span class="ml-auto text-xs text-gray-400">
                        {{ $procesos->total() }} proceso{{ $procesos->total() !== 1 ? 's' : '' }} encontrado{{ $procesos->total() !== 1 ? 's' : '' }}
                    </span>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Proceso</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Contratista</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Contrato / SECOP</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Secretaria / Unidad</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado SIA</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($procesos as $p)
                    @php
                        $etapaOrden = (int) (optional($p->etapaActual)->orden ?? 0);
                        $habilitado = strtoupper((string) $p->estado) === 'FINALIZADO' || $etapaOrden >= 8;
                    @endphp
                    <tr style="border-bottom:1px solid #f1f5f9" class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-mono text-xs font-semibold text-gray-800">{{ $p->codigo }}</div>
                            <div class="text-[11px] text-gray-400 mt-0.5">Etapa {{ $etapaOrden ?: 'N/A' }} - {{ optional($p->etapaActual)->nombre ?? 'Sin etapa' }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-sm font-medium text-gray-700">{{ $p->contratista_nombre ?: 'Sin nombre' }}</div>
                            <div class="text-xs text-gray-400">CC: {{ $p->contratista_documento ?: 'Sin cedula' }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-xs text-gray-700">
                                <span class="font-medium">Contrato:</span> {{ $p->numero_contrato ?: 'Sin registro' }}
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                <span class="font-medium">SECOP:</span> {{ $p->secop_codigo ?: ($p->numero_proceso_secop ?: 'Sin registro') }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-600">
                            <div>{{ optional($p->secretariaOrigen)->nombre ?: 'Sin secretaria' }}</div>
                            <div class="text-gray-400 mt-0.5">{{ optional($p->unidadOrigen)->nombre ?: 'Sin unidad' }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-xs text-gray-600">Accesos: {{ (int) $p->accesos_sia_count }} - Archivos: {{ (int) $p->archivos_sia_count }}</div>
                            @if($habilitado)
                                <span class="inline-flex mt-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#dcfce7;color:#15803d">Habilitado</span>
                            @else
                                <span class="inline-flex mt-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#fef9c3;color:#a16207">Pendiente de etapa final</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('admin.sia-observa.show', ['proceso' => $p->id]) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white hover:opacity-90 transition-all"
                               style="background:linear-gradient(135deg,#15803d,#14532d)">
                                Gestionar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm text-gray-400 font-medium">No se encontraron procesos con los filtros aplicados.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($procesos->hasPages())
            <div class="px-5 py-3 border-t" style="border-color:#e2e8f0">
                {{ $procesos->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
