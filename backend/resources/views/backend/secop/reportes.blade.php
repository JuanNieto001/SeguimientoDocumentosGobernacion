{{-- Archivo: backend/resources/views/backend/secop/reportes.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold text-gray-900">Reportes — SECOP</h1>
            <a href="{{ route('secop.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">
        <form method="GET" class="bg-white rounded-2xl border p-5 flex items-end gap-4" style="border-color:#e2e8f0">
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Desde</label>
                <input type="date" name="desde" value="{{ is_string($desde) ? $desde : $desde->format('Y-m-d') }}"
                       class="rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ is_string($hasta) ? $hasta : $hasta->format('Y-m-d') }}"
                       class="rounded-lg border-gray-300 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
        </form>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach([
                ['Publicaciones SECOP', $stats['publicaciones_secop'] ?? 0, '#6366f1'],
                ['Contratos registrados', $stats['contratos_registrados'] ?? 0, '#10b981'],
                ['Actas de inicio', $stats['actas_inicio'] ?? 0, '#8b5cf6'],
                ['Procesos cerrados', $stats['procesos_cerrados'] ?? 0, '#f59e0b'],
                ['Valor total contratos', '$' . number_format($stats['valor_total_contratos'] ?? 0, 0, ',', '.'), '#0ea5e9'],
            ] as [$label, $value, $color])
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ $label }}</p>
                <p class="text-xl font-bold" style="color:{{ $color }}">{{ $value }}</p>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

