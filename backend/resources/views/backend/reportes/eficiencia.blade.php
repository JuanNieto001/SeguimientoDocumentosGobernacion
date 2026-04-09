<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                    <a href="{{ route('reportes.index') }}" class="hover:text-gray-700">Reportes</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">Eficiencia y tiempos</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Eficiencia y tiempos de proceso</h1>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                <p class="text-xs text-gray-400 mb-1">Procesos finalizados</p>
                <p class="text-2xl font-bold text-gray-900">{{ $estadisticas['total_finalizados'] }}</p>
                <p class="text-xs text-gray-300 mt-1">en el período seleccionado</p>
            </div>
            <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                <p class="text-xs text-gray-400 mb-1">Promedio general</p>
                <p class="text-2xl font-bold text-orange-600">{{ $estadisticas['promedio_general'] }} días</p>
                <p class="text-xs text-gray-300 mt-1">desde inicio hasta cierre</p>
            </div>
            <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                <p class="text-xs text-gray-400 mb-1">Modalidades analizadas</p>
                <p class="text-2xl font-bold text-blue-600">{{ $estadisticas['por_modalidad']->count() }}</p>
                <p class="text-xs text-gray-300 mt-1">tipos de contratación</p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio', now()->subMonths(3)->format('Y-m-d')) }}"
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

        {{-- Por modalidad --}}
        @if($estadisticas['por_modalidad']->isNotEmpty())
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="px-5 py-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-gray-800">Tiempos por modalidad de contratación</h2>
            </div>
            <table class="w-full text-xs">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Modalidad</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Procesos</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Promedio días</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Mín. días</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Máx. días</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxProm = $estadisticas['por_modalidad']->max('promedio_dias') ?: 1; @endphp
                    @foreach($estadisticas['por_modalidad'] as $modalidad => $datos)
                    @php $pct = round(($datos['promedio_dias'] / $maxProm) * 100); @endphp
                    <tr style="border-bottom:1px solid #f1f5f9" class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-gray-700">{{ $modalidad ?: 'Sin modalidad' }}</td>
                        <td class="px-5 py-3 text-right text-gray-600">{{ $datos['cantidad'] }}</td>
                        <td class="px-5 py-3 text-right font-bold text-orange-600">{{ $datos['promedio_dias'] }}</td>
                        <td class="px-5 py-3 text-right text-green-600">{{ $datos['min_dias'] }}</td>
                        <td class="px-5 py-3 text-right text-red-500">{{ $datos['max_dias'] }}</td>
                        <td class="px-5 py-3 w-32">
                            <div class="w-full h-1.5 rounded-full bg-gray-100">
                                <div class="h-1.5 rounded-full bg-orange-400 transition-all" style="width:{{ $pct }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Detalle procesos finalizados --}}
        @if($procesosFinalizados->isNotEmpty())
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="px-5 py-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-gray-800">Detalle de procesos finalizados</h2>
            </div>
            <table class="w-full text-xs">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Código</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Objeto</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Modalidad</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Días</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Finalizado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($procesosFinalizados->sortByDesc(function($p){ return $p->created_at->diffInDays($p->updated_at); }) as $proceso)
                    @php $dias = $proceso->created_at->diffInDays($proceso->updated_at); @endphp
                    <tr style="border-bottom:1px solid #f1f5f9" class="hover:bg-slate-50">
                        <td class="px-5 py-2 font-mono text-gray-700">{{ $proceso->codigo ?? ('P-'.str_pad($proceso->id,4,'0',STR_PAD_LEFT)) }}</td>
                        <td class="px-5 py-2 text-gray-600">{{ Str::limit($proceso->objeto_contratacion ?? $proceso->objeto ?? '-', 55) }}</td>
                        <td class="px-5 py-2 text-gray-400">{{ $proceso->workflow->nombre ?? '-' }}</td>
                        <td class="px-5 py-2 text-right font-bold {{ $dias > 60 ? 'text-red-500' : ($dias > 30 ? 'text-orange-500' : 'text-green-600') }}">{{ $dias }}</td>
                        <td class="px-5 py-2 text-gray-400">{{ $proceso->updated_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white rounded-2xl p-12 text-center text-gray-400 text-sm" style="border:1px solid #e2e8f0">
            No hay procesos finalizados en el período seleccionado.
        </div>
        @endif

    </div>
</x-app-layout>
