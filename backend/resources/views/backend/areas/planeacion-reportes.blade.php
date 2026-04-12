{{-- Archivo: backend/resources/views/backend/areas/planeacion-reportes.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Reportes — Planeación</h1>
                <p class="text-sm text-gray-500">Estadísticas del área de Planeación</p>
            </div>
            <a href="{{ route('planeacion.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50 transition-all"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">
        {{-- Filtro de fechas --}}
        <form method="GET" class="bg-white rounded-2xl border p-5 flex items-end gap-4" style="border-color:#e2e8f0">
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Desde</label>
                <input type="date" name="fecha_inicio" value="{{ is_string($fechaInicio) ? $fechaInicio : $fechaInicio->format('Y-m-d') }}"
                       class="rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Hasta</label>
                <input type="date" name="fecha_fin" value="{{ is_string($fechaFin) ? $fechaFin : $fechaFin->format('Y-m-d') }}"
                       class="rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                Filtrar
            </button>
        </form>

        {{-- Indicadores --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $indicadores = [
                    ['label' => 'Total procesos',   'value' => $estadisticas['total_procesos'] ?? 0,   'color' => '#3b82f6'],
                    ['label' => 'En Planeación',     'value' => $estadisticas['en_planeacion'] ?? 0,     'color' => '#f59e0b'],
                    ['label' => 'Aprobados',          'value' => $estadisticas['aprobados'] ?? 0,          'color' => '#22c55e'],
                    ['label' => 'Rechazados',         'value' => $estadisticas['rechazados'] ?? 0,         'color' => '#ef4444'],
                ];
            @endphp
            @foreach($indicadores as $ind)
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ $ind['label'] }}</p>
                <p class="text-xl font-bold" style="color:{{ $ind['color'] }}">{{ $ind['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Por modalidad --}}
        @if(isset($estadisticas['por_modalidad']) && count($estadisticas['por_modalidad']) > 0)
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📊 Procesos por modalidad</h3>
            </div>
            <div class="p-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color:#f1f5f9">
                            <th class="text-left py-2 text-xs text-gray-400 uppercase">Modalidad</th>
                            <th class="text-right py-2 text-xs text-gray-400 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($estadisticas['por_modalidad'] as $modalidad)
                        <tr class="border-b last:border-0" style="border-color:#f8fafc">
                            <td class="py-2 text-gray-700 font-medium">{{ $modalidad->nombre }}</td>
                            <td class="py-2 text-right text-gray-900 font-bold">{{ $modalidad->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>

