<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                    <a href="{{ route('reportes.index') }}" class="hover:text-gray-700">Reportes</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">Por dependencia</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Procesos por dependencia</h1>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- Filtro fechas --}}
        <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio', now()->subMonth()->format('Y-m-d')) }}"
                           class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin', now()->format('Y-m-d')) }}"
                           class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none">
                </div>
                <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-semibold text-white" style="background:#1d4ed8">Filtrar</button>
            </form>
        </div>

        @forelse($porDependencia as $dependencia => $items)
        @php
        $stats = $estadisticas[$dependencia] ?? ['total'=>0,'finalizados'=>0,'en_tramite'=>0];
        $pct = $stats['total'] > 0 ? round(($stats['finalizados'] / $stats['total']) * 100) : 0;
        @endphp
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="flex items-center justify-between px-5 py-3" style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#dbeafe">
                        <span class="text-xs font-bold text-blue-700">{{ $stats['total'] }}</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">{{ $dependencia }}</span>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <span class="text-green-600 font-semibold">✓ {{ $stats['finalizados'] }} fin.</span>
                    <span class="text-yellow-600 font-semibold">⏳ {{ $stats['en_tramite'] }} en trám.</span>
                    <div class="flex items-center gap-1.5">
                        <div class="w-24 h-1.5 rounded-full bg-gray-100">
                            <div class="h-1.5 rounded-full bg-green-500" style="width:{{ $pct }}%"></div>
                        </div>
                        <span>{{ $pct }}%</span>
                    </div>
                </div>
            </div>
            <table class="w-full text-xs">
                <thead>
                    <tr style="border-bottom:1px solid #f1f5f9">
                        <th class="text-left px-5 py-2 font-semibold text-gray-400 uppercase tracking-wide">Código</th>
                        <th class="text-left px-5 py-2 font-semibold text-gray-400 uppercase tracking-wide">Objeto</th>
                        <th class="text-left px-5 py-2 font-semibold text-gray-400 uppercase tracking-wide">Modalidad</th>
                        <th class="text-left px-5 py-2 font-semibold text-gray-400 uppercase tracking-wide">Estado</th>
                        <th class="text-left px-5 py-2 font-semibold text-gray-400 uppercase tracking-wide">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $proceso)
                    @php $estadoCfg = match($proceso->estado) { 'FINALIZADO'=>['bg'=>'#dcfce7','text'=>'#15803d','label'=>'Finalizado'], 'en_tramite'=>['bg'=>'#fef9c3','text'=>'#854d0e','label'=>'En trámite'], default=>['bg'=>'#f1f5f9','text'=>'#475569','label'=>ucfirst($proceso->estado)] }; @endphp
                    <tr style="border-bottom:1px solid #f8fafc" class="hover:bg-slate-50">
                        <td class="px-5 py-2 font-mono text-gray-700">{{ $proceso->codigo ?? ('P-'.str_pad($proceso->id,4,'0',STR_PAD_LEFT)) }}</td>
                        <td class="px-5 py-2 text-gray-600 max-w-xs truncate">{{ Str::limit($proceso->objeto_contratacion ?? $proceso->objeto ?? '-', 55) }}</td>
                        <td class="px-5 py-2 text-gray-400">{{ $proceso->workflow->nombre ?? '-' }}</td>
                        <td class="px-5 py-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold" style="background:{{ $estadoCfg['bg'] }};color:{{ $estadoCfg['text'] }}">{{ $estadoCfg['label'] }}</span>
                        </td>
                        <td class="px-5 py-2 text-gray-400">{{ $proceso->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @empty
        <div class="bg-white rounded-2xl p-12 text-center text-gray-400 text-sm" style="border:1px solid #e2e8f0">
            No hay procesos en el período seleccionado.
        </div>
        @endforelse

    </div>
</x-app-layout>
