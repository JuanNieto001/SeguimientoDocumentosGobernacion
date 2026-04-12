{{-- Archivo: backend/resources/views/backend/proceso-cd/index.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Contratación Directa – Persona Natural
            </h2>
            @if(auth()->user()->hasRole('unidad_solicitante') || auth()->user()->hasRole('admin'))
                <a href="{{ route('proceso-cd.create') }}"
                   class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Solicitud
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Mensajes flash --}}
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filtros --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                <form method="GET" action="{{ route('proceso-cd.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Buscar</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               placeholder="Código, objeto, contratista…"
                               class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Estado</label>
                        <select name="estado" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Todos</option>
                            @foreach($estados as $est)
                                <option value="{{ $est->value }}" {{ request('estado') == $est->value ? 'selected' : '' }}>
                                    {{ $est->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Etapa</label>
                        <select name="etapa" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Todas</option>
                            @for($i = 1; $i <= 7; $i++)
                                <option value="{{ $i }}" {{ request('etapa') == $i ? 'selected' : '' }}>
                                    Etapa {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla de procesos --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Código</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Objeto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Valor</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Etapa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Creado por</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($procesos as $proceso)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-mono font-semibold text-green-800">
                                    {{ $proceso->codigo }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">
                                    {{ \Illuminate\Support\Str::limit($proceso->objeto, 60) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                    ${{ number_format($proceso->valor, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-800 text-xs font-bold">
                                        {{ $proceso->etapa_actual }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $proceso->estado->badgeClass() }}">
                                        {{ $proceso->estado->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $proceso->creadoPor?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                    {{ $proceso->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('proceso-cd.show', $proceso) }}"
                                       class="inline-flex items-center gap-1 text-green-700 hover:text-green-900 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400 text-sm">
                                    No se encontraron procesos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($procesos->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $procesos->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

