<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Mi Bandeja — Descentralización</h1>
                <p class="text-xs text-gray-400 mt-1">Procesos pendientes en tu área</p>
            </div>
            <div class="ml-8">
                <a href="{{ route('procesos.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold shadow-sm transition-all hover:shadow-md hover:opacity-95"
                   style="background:linear-gradient(135deg,#15803d,#14532d)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Nueva solicitud
                </a>
            </div>
        </div>
    </x-slot>

    @php
    $areaColors = [
        'unidad_solicitante' => ['bg'=>'#eff6ff','text'=>'#1d4ed8','label'=>'Unidad Solicitante'],
        'planeacion'         => ['bg'=>'#f0fdf4','text'=>'#15803d','label'=>'Descentralización'],
        'hacienda'           => ['bg'=>'#fefce8','text'=>'#a16207','label'=>'Hacienda'],
        'juridica'           => ['bg'=>'#fff7ed','text'=>'#c2410c','label'=>'Jurídica'],
        'secop'              => ['bg'=>'#fdf4ff','text'=>'#7e22ce','label'=>'SECOP'],
    ];
    $estadoConfig = [
        'EN_CURSO'   => ['bg'=>'#dbeafe','text'=>'#1d4ed8','dot'=>'#3b82f6','label'=>'En curso'],
        'FINALIZADO' => ['bg'=>'#dcfce7','text'=>'#15803d','dot'=>'#22c55e','label'=>'Finalizado'],
        'RECHAZADO'  => ['bg'=>'#fee2e2','text'=>'#b91c1c','dot'=>'#ef4444','label'=>'Rechazado'],
    ];
    @endphp

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium"
             style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Filtros --}}
        <form method="GET" action="{{ route('planeacion.index') }}"
              class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Buscar</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           placeholder="Buscar por código..."
                           class="w-full border rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500"
                           style="border-color:#e2e8f0">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Estado</label>
                    <select name="estado"
                            class="w-full border rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500"
                            style="border-color:#e2e8f0">
                        <option value="">Todos</option>
                        <option value="EN_CURSO"  @selected(request('estado')=='EN_CURSO')>En curso</option>
                        <option value="RECHAZADO" @selected(request('estado')=='RECHAZADO')>Rechazado</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#15803d,#14532d)">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['buscar','estado']))
                    <a href="{{ route('planeacion.index') }}"
                       class="px-3 py-2 rounded-xl text-sm font-medium border hover:bg-gray-50 transition"
                       style="border-color:#e2e8f0;color:#6b7280">✕</a>
                    @endif
                </div>
            </div>
        </form>



        {{-- Tabla --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Objeto / Etapa</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Área actual</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Creado por</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($procesos as $p)
                    @php
                        $ac = $areaColors[$p->area_actual_role] ?? ['bg'=>'#f1f5f9','text'=>'#475569','label'=>$p->area_actual_role];
                        $ec = $estadoConfig[$p->estado] ?? ['bg'=>'#f1f5f9','text'=>'#475569','dot'=>'#94a3b8','label'=>$p->estado];
                        $esMiArea = $p->area_actual_role === 'planeacion';
                    @endphp
                    <tr style="border-bottom:1px solid #f1f5f9{{ $esMiArea ? ';background:#f0fdf4' : '' }}"
                        class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5">
                                @if($esMiArea)
                                <span class="w-2 h-2 rounded-full shrink-0" style="background:#15803d"></span>
                                @endif
                                <span class="font-mono font-semibold text-gray-900 text-xs">{{ $p->codigo }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-700 max-w-xs">
                            <span class="block truncate text-sm">{{ $p->objeto }}</span>
                            @if(isset($p->etapa_nombre))
                            <span class="text-xs text-gray-400">Etapa {{ $p->etapa_orden }}: {{ Str::limit($p->etapa_nombre, 30) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                  style="background:{{ $ec['bg'] }};color:{{ $ec['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background:{{ $ec['dot'] }}"></span>
                                {{ $ec['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium"
                                  style="background:{{ $ac['bg'] }};color:{{ $ac['text'] }}">
                                {{ $ac['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500 hidden md:table-cell">
                            {{ $p->creado_por_nombre ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('procesos.show', $p->id) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors hover:bg-blue-100"
                                   style="background:#eff6ff;color:#2563eb" title="Ver expediente">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Exp.
                                </a>
                                <a href="{{ route('planeacion.show', $p->id) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-white text-xs font-semibold transition-all hover:opacity-90"
                                   style="background:linear-gradient(135deg,#15803d,#14532d)">
                                    Abrir
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm text-gray-400 font-medium">No se encontraron procesos.</p>
                                @if(request()->hasAny(['buscar','estado']))
                                <a href="{{ route('planeacion.index') }}" class="text-xs text-green-700 hover:underline">Limpiar filtros</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
