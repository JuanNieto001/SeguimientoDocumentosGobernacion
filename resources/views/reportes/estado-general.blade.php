<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                    <a href="{{ route('reportes.index') }}" class="hover:text-gray-700">Reportes</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">Estado general</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Estado general de procesos</h1>
            </div>
            @if($procesos->count())
            <a href="{{ request()->fullUrlWithQuery(['formato' => 'excel']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold"
               style="background:#dcfce7;color:#15803d">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Exportar CSV
            </a>
            @endif
        </div>
    </x-slot>

    <div class="p-6 space-y-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
            $kpis = [
                ['label'=>'Total procesos','val'=>$estadisticas['total'],'bg'=>'#dbeafe','text'=>'#1d4ed8'],
                ['label'=>'En trámite','val'=>$estadisticas['en_tramite'],'bg'=>'#fef9c3','text'=>'#854d0e'],
                ['label'=>'Finalizados','val'=>$estadisticas['finalizados'],'bg'=>'#dcfce7','text'=>'#15803d'],
                ['label'=>'Rechazados','val'=>$estadisticas['rechazados'],'bg'=>'#fee2e2','text'=>'#b91c1c'],
            ];
            @endphp
            @foreach($kpis as $k)
            <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-2" style="background:{{ $k['bg'] }}">
                    <span class="text-sm font-bold" style="color:{{ $k['text'] }}">{{ $k['val'] }}</span>
                </div>
                <p class="text-xs text-gray-400">{{ $k['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio', now()->subMonth()->format('Y-m-d')) }}"
                           class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin', now()->format('Y-m-d')) }}"
                           class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estado</label>
                    <select name="estado" class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none">
                        <option value="">Todos</option>
                        <option value="en_tramite" @selected(request('estado')=='en_tramite')>En trámite</option>
                        <option value="FINALIZADO" @selected(request('estado')=='FINALIZADO')>Finalizado</option>
                        <option value="rechazado" @selected(request('estado')=='rechazado')>Rechazado</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-semibold text-white" style="background:#1d4ed8">Filtrar</button>
                <a href="{{ route('reportes.estado.general') }}" class="px-4 py-1.5 rounded-xl text-xs text-gray-500" style="background:#f1f5f9">Limpiar</a>
            </form>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            @if($procesos->isEmpty())
            <div class="p-12 text-center text-gray-400 text-sm">No hay procesos con los filtros aplicados.</div>
            @else
            <table class="w-full text-xs">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                        <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide">Código</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide">Objeto</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide">Modalidad</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide">Etapa actual</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide">Creado por</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide">Fecha</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($procesos as $proceso)
                    @php
                    $estadoCfg = match($proceso->estado) {
                        'FINALIZADO'  => ['bg'=>'#dcfce7','text'=>'#15803d','label'=>'Finalizado'],
                        'en_tramite'  => ['bg'=>'#fef9c3','text'=>'#854d0e','label'=>'En trámite'],
                        'rechazado'   => ['bg'=>'#fee2e2','text'=>'#b91c1c','label'=>'Rechazado'],
                        default       => ['bg'=>'#f1f5f9','text'=>'#475569','label'=>ucfirst($proceso->estado)],
                    };
                    @endphp
                    <tr style="border-bottom:1px solid #f1f5f9" class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono font-bold text-gray-700">{{ $proceso->codigo ?? ('P-'.str_pad($proceso->id,4,'0',STR_PAD_LEFT)) }}</td>
                        <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ Str::limit($proceso->objeto_contratacion ?? $proceso->objeto ?? 'Sin objeto', 60) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $proceso->workflow->nombre ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $proceso->etapaActual->nombre ?? 'Etapa 1' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold" style="background:{{ $estadoCfg['bg'] }};color:{{ $estadoCfg['text'] }}">
                                {{ $estadoCfg['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $proceso->creador->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $proceso->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('procesos.show', $proceso->id) }}" class="text-blue-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100 text-xs text-gray-400">
                {{ $procesos->count() }} proceso(s) — {{ now()->subMonth()->format('d/m/Y') }} al {{ now()->format('d/m/Y') }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
