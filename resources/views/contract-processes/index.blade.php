<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Procesos Contractuales
            </h2>
            @can('create', App\Models\ContractProcess::class)
                <a href="{{ route('contract-processes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    + Nuevo Proceso
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Filtros --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <form method="GET" action="{{ route('contract-processes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Número, contratista..." 
                            class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select name="status" class="w-full rounded-lg border-gray-300">
                            <option value="">Todos</option>
                            @foreach(App\Enums\ProcessStatus::activeStatuses() as $status)
                                <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                    {{ $status->getLabel() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Etapa</label>
                        <select name="step" class="w-full rounded-lg border-gray-300">
                            <option value="">Todas</option>
                            @for($i = 0; $i <= 9; $i++)
                                <option value="{{ $i }}" {{ request('step') == $i ? 'selected' : '' }}>
                                    Etapa {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Lista de procesos --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Objeto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contratista</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Etapa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progreso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($processes as $process)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $process->process_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs truncate" title="{{ $process->object }}">
                                        {{ $process->object }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $process->contractor_name ?? 'No asignado' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($process->estimated_value, 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $process->status->getLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Etapa {{ $process->current_step }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $process->getProgressPercentage() }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600">{{ $process->getProgressPercentage() }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('contract-processes.show', $process) }}" 
                                        class="text-blue-600 hover:text-blue-800 mr-3">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                    No hay procesos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Paginación --}}
                <div class="px-6 py-4 border-t">
                    {{ $processes->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
