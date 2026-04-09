<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Plan Anual de Adquisiciones</h1>
                <p class="text-xs text-gray-400 mt-1">Vigencia {{ $anio }} — Gobernación de Caldas</p>
            </div>
            <div class="flex items-center gap-2">
                {{-- Exportar --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open=!open" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-xl border transition-colors"
                            style="border-color:#e2e8f0;color:#374151;background:#fff">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Exportar
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" @click.outside="open=false" x-transition
                         class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-lg border z-50 py-1" style="border-color:#e2e8f0">
                        <a href="{{ route('paa.exportar.csv', ['anio' => $anio]) }}"
                           class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">
                            <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Exportar CSV (Excel)
                        </a>
                        <a href="{{ route('paa.exportar.pdf', ['anio' => $anio]) }}" target="_blank"
                           class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">
                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Vista imprimible (PDF)
                        </a>
                    </div>
                </div>
                <a href="{{ route('paa.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-xl text-white transition-colors"
                   style="background:#166534">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Nueva necesidad
                </a>
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

        {{-- KPIs --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            @php
            $kpis = [
                ['label'=>'Total necesidades','val'=>$resumen->total ?? 0,'bg'=>'#dbeafe','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2','text'=>'#1d4ed8'],
                ['label'=>'Vigentes','val'=>$resumen->vigentes ?? 0,'bg'=>'#dcfce7','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','text'=>'#15803d'],
                ['label'=>'Ejecutados','val'=>$resumen->ejecutados ?? 0,'bg'=>'#f0f9ff','icon'=>'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z','text'=>'#0369a1'],
                ['label'=>'Modificados','val'=>$resumen->modificados ?? 0,'bg'=>'#fefce8','icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z','text'=>'#a16207'],
                ['label'=>'Cancelados','val'=>$resumen->cancelados ?? 0,'bg'=>'#fef2f2','icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z','text'=>'#b91c1c'],
            ];
            @endphp
            @foreach($kpis as $k)
            <div class="bg-white rounded-2xl p-4 flex items-center gap-3" style="border:1px solid #e2e8f0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $k['bg'] }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:{{ $k['text'] }}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $k['icon'] }}"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 leading-none">{{ $k['val'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $k['label'] }}</p>
                </div>
            </div>
            @endforeach
            <div class="bg-white rounded-2xl p-4 flex items-center gap-3 sm:col-span-1 col-span-2" style="border:1px solid #e2e8f0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#fff7ed">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-900 leading-none">$ {{ number_format($resumen->valor_total ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Valor total estimado</p>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('paa.index') }}" class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                {{-- Año --}}
                <select name="anio" class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                    @foreach($anios as $a)
                    <option value="{{ $a }}" @selected($a == $anio)>{{ $a }}</option>
                    @endforeach
                </select>
                {{-- Modalidad --}}
                <select name="modalidad" class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                    <option value="">Todas las modalidades</option>
                    @foreach($modalidades as $cod => $label)
                    <option value="{{ $cod }}" @selected(request('modalidad') == $cod)>{{ $cod }}</option>
                    @endforeach
                </select>
                {{-- Estado --}}
                <select name="estado" class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                    <option value="">Todos los estados</option>
                    @foreach(['vigente','modificado','ejecutado','cancelado'] as $e)
                    <option value="{{ $e }}" @selected(request('estado') == $e)>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
                {{-- Búsqueda --}}
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar código, descripción…"
                       class="col-span-2 sm:col-span-1 text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                <button type="submit" class="col-span-2 sm:col-span-1 px-4 py-2 text-sm font-semibold text-white rounded-xl transition-colors" style="background:#166534">
                    Filtrar
                </button>
            </div>
        </form>

        {{-- Tabla --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Código</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Descripción</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Dependencia</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Modalidad</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Valor</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Trim.</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:#f8fafc">
                        @forelse($paas as $paa)
                        @php
                            $estadoCfg = [
                                'vigente'   => ['bg'=>'#dcfce7','text'=>'#15803d','label'=>'Vigente'],
                                'modificado'=> ['bg'=>'#fefce8','text'=>'#a16207','label'=>'Modificado'],
                                'ejecutado' => ['bg'=>'#dbeafe','text'=>'#1d4ed8','label'=>'Ejecutado'],
                                'cancelado' => ['bg'=>'#fee2e2','text'=>'#b91c1c','label'=>'Cancelado'],
                            ][$paa->estado] ?? ['bg'=>'#f1f5f9','text'=>'#475569','label'=>ucfirst($paa->estado)];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $paa->codigo_necesidad }}</td>
                            <td class="px-4 py-3 max-w-xs">
                                <a href="{{ route('paa.show', $paa->id) }}" class="text-sm font-medium text-gray-900 hover:text-green-700 line-clamp-2">{{ $paa->descripcion }}</a>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 hidden md:table-cell max-w-xs truncate">{{ $paa->dependencia_solicitante }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-md" style="background:#dbeafe;color:#1d4ed8">{{ $paa->modalidad_contratacion }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs font-mono text-gray-700 hidden lg:table-cell whitespace-nowrap">
                                $ {{ number_format($paa->valor_estimado, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center text-xs font-medium text-gray-600 hidden md:table-cell">T{{ $paa->trimestre_estimado }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full" style="background:{{ $estadoCfg['bg'] }};color:{{ $estadoCfg['text'] }}">{{ $estadoCfg['label'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('paa.show', $paa->id) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors"
                                       style="background:#f0fdf4;color:#15803d" title="Ver detalle">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Ver
                                    </a>
                                    <a href="{{ route('paa.edit', $paa->id) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors"
                                       style="background:#fefce8;color:#a16207" title="Editar">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <a href="{{ route('paa.certificado', $paa->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors"
                                       style="background:#dbeafe;color:#1d4ed8" title="Certificado">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="text-sm text-gray-400 font-medium">No hay necesidades registradas para el año {{ $anio }}</p>
                                    <a href="{{ route('paa.create') }}" class="text-xs font-semibold text-green-700 hover:text-green-900">+ Registrar primera necesidad</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paas->hasPages())
            <div class="px-4 py-3 border-t" style="border-color:#f1f5f9">
                {{ $paas->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
