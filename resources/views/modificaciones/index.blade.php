<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold text-gray-900">Modificaciones Contractuales</h1>
            <a href="{{ route('procesos.show', $proceso) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al proceso
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">
        {{-- Estadísticas --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total modificaciones</p>
                <p class="text-xl font-bold text-gray-800">{{ $estadisticas['total_modificaciones'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Valor acumulado</p>
                <p class="text-xl font-bold text-blue-600">${{ number_format($estadisticas['valor_acumulado'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">% Usado</p>
                <p class="text-xl font-bold text-amber-600">{{ number_format($estadisticas['porcentaje_usado'] ?? 0, 1) }}%</p>
            </div>
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">% Disponible</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($estadisticas['porcentaje_disponible'] ?? 0, 1) }}%</p>
            </div>
        </div>

        {{-- Barra de progreso --}}
        <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Uso del límite 50%</p>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                @php $pct = min($estadisticas['porcentaje_usado'] ?? 0, 50); @endphp
                <div class="h-4 rounded-full transition-all {{ $pct >= 45 ? 'bg-red-500' : ($pct >= 30 ? 'bg-amber-500' : 'bg-green-500') }}"
                     style="width:{{ ($pct / 50) * 100 }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ number_format($pct, 1) }}% de 50% permitido</p>
        </div>

        {{-- Crear nueva modificación --}}
        @can('create', [\App\Models\ModificacionContractual::class, $proceso])
        <div class="flex justify-end">
            <a href="{{ route('modificaciones.create', $proceso) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                + Nueva modificación
            </a>
        </div>
        @endcan

        {{-- Listado --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            @forelse ($proceso->modificaciones as $mod)
            <div class="flex items-center justify-between p-4 border-b last:border-b-0" style="border-color:#f1f5f9">
                <div>
                    <p class="font-medium text-gray-800">{{ ucfirst($mod->tipo) }}</p>
                    <p class="text-sm text-gray-500">{{ $mod->descripcion ?? 'Sin descripción' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ optional($mod->fecha_solicitud)->format('d/m/Y') }}</p>
                </div>
                <div class="text-right">
                    @if ($mod->valor_modificacion)
                    <p class="text-sm font-semibold text-gray-800">${{ number_format($mod->valor_modificacion, 0, ',', '.') }}</p>
                    @endif
                    <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full
                        {{ $mod->estado === 'aprobado' ? 'bg-green-100 text-green-700' :
                           ($mod->estado === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ ucfirst($mod->estado) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400">
                <p>No hay modificaciones registradas.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
