<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold text-gray-900">Auditoría del Proceso</h1>
            <a href="{{ route('procesos.show', $proceso) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al proceso
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">
        {{-- Info del proceso --}}
        <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
            <div class="flex flex-wrap gap-4 text-sm">
                <span class="text-gray-500">Proceso: <strong class="text-gray-800">{{ $proceso->numero ?? $proceso->id }}</strong></span>
                <span class="text-gray-500">Workflow: <strong class="text-gray-800">{{ optional($proceso->workflow)->nombre ?? '—' }}</strong></span>
                <span class="text-gray-500">Creador: <strong class="text-gray-800">{{ optional($proceso->creador)->name ?? '—' }}</strong></span>
            </div>
        </div>

        {{-- Estadísticas --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total eventos</p>
                <p class="text-xl font-bold text-blue-600">{{ $estadisticas['total_eventos'] }}</p>
            </div>
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Usuarios involucrados</p>
                <p class="text-xl font-bold text-purple-600">{{ $estadisticas['usuarios_involucrados'] }}</p>
            </div>
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Duración</p>
                <p class="text-xl font-bold text-gray-800">{{ $estadisticas['duracion_dias'] }} días</p>
            </div>
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Acciones distintas</p>
                <p class="text-xl font-bold text-green-600">{{ $estadisticas['por_accion']->count() }}</p>
            </div>
        </div>

        {{-- Desglose por acción --}}
        @if($estadisticas['por_accion']->count())
        <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Por tipo de acción</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($estadisticas['por_accion'] as $accion => $count)
                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">{{ ucfirst($accion) }} ({{ $count }})</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Timeline --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="p-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Cronología</h3>
            </div>
            @forelse ($auditorias as $audit)
            <div class="flex gap-4 p-4 border-b last:border-b-0" style="border-color:#f1f5f9">
                <div class="flex-shrink-0 mt-0.5">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $audit->accion }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $audit->descripcion }}</p>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                        <span>{{ optional($audit->usuario)->name ?? 'Sistema' }}</span>
                        <span>{{ $audit->created_at->format('d/m/Y H:i:s') }}</span>
                        @if($audit->etapa_id)
                        <span class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-500">Etapa {{ $audit->etapa_id }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400">
                <p>No hay registros de auditoría para este proceso.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
