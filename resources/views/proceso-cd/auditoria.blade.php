<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Historial de Auditoría – {{ $procesoCD->codigo }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Registro completo de todas las acciones sobre el proceso</p>
            </div>
            <a href="{{ route('proceso-cd.show', $procesoCD) }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver al proceso</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Acción</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado Anterior</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado Nuevo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Descripción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($registros as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $log->usuario?->name ?? 'Sistema' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $log->accion }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $log->estado_anterior ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs font-medium text-green-700">
                                {{ $log->estado_nuevo ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 max-w-xs truncate">
                                {{ $log->descripcion }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                                No hay registros de auditoría.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($registros->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $registros->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
