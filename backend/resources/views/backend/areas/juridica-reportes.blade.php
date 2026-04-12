{{-- Archivo: backend/resources/views/backend/areas/juridica-reportes.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Reportes — Jurídica</h1>
                <p class="text-sm text-gray-500">Estadísticas del área Jurídica</p>
            </div>
            <a href="{{ route('juridica.index') }}"
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
                    ['label' => 'Total procesos',           'value' => $estadisticas['total_procesos'] ?? 0,           'color' => '#3b82f6'],
                    ['label' => 'En Jurídica',               'value' => $estadisticas['en_juridica'] ?? 0,               'color' => '#f59e0b'],
                    ['label' => 'Ajustados emitidos',        'value' => $estadisticas['ajustados_emitidos'] ?? 0,        'color' => '#10b981'],
                    ['label' => 'Contratistas verificados',  'value' => $estadisticas['contratistas_verificados'] ?? 0,  'color' => '#6366f1'],
                    ['label' => 'Pólizas aprobadas',         'value' => $estadisticas['polizas_aprobadas'] ?? 0,         'color' => '#8b5cf6'],
                    ['label' => 'Aprobados',                 'value' => $estadisticas['aprobados'] ?? 0,                 'color' => '#22c55e'],
                    ['label' => 'Rechazados',                'value' => $estadisticas['rechazados'] ?? 0,                'color' => '#ef4444'],
                ];
            @endphp
            @foreach($indicadores as $ind)
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ $ind['label'] }}</p>
                <p class="text-xl font-bold" style="color:{{ $ind['color'] }}">{{ $ind['value'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

