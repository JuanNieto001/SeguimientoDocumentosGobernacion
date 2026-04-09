<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold text-gray-900">Historial de Auditoría</h1>
            <a href="{{ route('contract-processes.show', $contractProcess) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al proceso
            </a>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="mb-4 bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
            <p class="text-sm text-gray-500">Proceso: <span class="font-medium text-gray-800">{{ $contractProcess->process_number ?? $contractProcess->id }}</span></p>
            <p class="text-sm text-gray-500">Objeto: <span class="text-gray-700">{{ Str::limit($contractProcess->object, 100) }}</span></p>
        </div>

        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            @forelse ($contractProcess->auditLogs as $log)
            <div class="flex gap-4 p-4 border-b last:border-b-0" style="border-color:#f1f5f9">
                <div class="flex-shrink-0 mt-0.5">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $log->action ?? $log->accion ?? 'Acción' }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $log->description ?? $log->descripcion ?? '' }}</p>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                        <span>{{ optional($log->user ?? $log->usuario)->name ?? 'Sistema' }}</span>
                        <span>{{ $log->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400">
                <p>No hay registros de auditoría.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
