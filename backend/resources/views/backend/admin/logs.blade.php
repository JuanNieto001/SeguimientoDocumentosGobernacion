<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Logs de Auditoría</h1>
                <p class="text-xs text-gray-400 mt-0.5">Registro completo de todas las acciones del sistema</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Total registros</p>
            </div>
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-2xl font-bold" style="color:#15803d">{{ $stats['hoy'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Hoy</p>
            </div>
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-2xl font-bold" style="color:#2563eb">{{ $stats['usuarios'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Usuarios activos</p>
            </div>
            <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                <p class="text-2xl font-bold" style="color:#7c3aed">{{ $stats['procesos'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Procesos con actividad</p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
            <form method="GET" action="{{ route('admin.logs') }}" class="grid grid-cols-2 sm:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Proceso</label>
                    <select name="proceso_id" class="w-full px-3 py-2 rounded-xl border text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                        <option value="">Todos</option>
                        @foreach($procesos as $p)
                        <option value="{{ $p->id }}" {{ request('proceso_id') == $p->id ? 'selected' : '' }}>{{ $p->codigo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Acción</label>
                    <select name="accion" class="w-full px-3 py-2 rounded-xl border text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                        <option value="">Todas</option>
                        @foreach($acciones as $a)
                        <option value="{{ $a }}" {{ request('accion') == $a ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$a)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Usuario</label>
                    <select name="user_id" class="w-full px-3 py-2 rounded-xl border text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                        <option value="">Todos</option>
                        @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}"
                           class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}"
                           class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
                            style="background:#14532d">
                        Filtrar
                    </button>
                    <a href="{{ route('admin.logs') }}"
                       class="px-3 py-2 rounded-xl border text-sm text-gray-500 hover:bg-gray-50 transition" style="border-color:#e2e8f0">
                        ✕
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabla de logs --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha / Hora</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acción</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Proceso</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color:#f1f5f9">
                        @php
                            $iconMap = [
                                'proceso_creado' => '🆕',
                                'documento_recibido' => '📥',
                                'proceso_enviado' => '📤',
                                'check_marcado' => '✅',
                                'check_desmarcado' => '☐',
                                'proceso_rechazado' => '🔴',
                                'archivo_subido' => '📎',
                                'archivo_eliminado' => '🗑️',
                                'ajustado_derecho_emitido' => '⚖️',
                                'contratista_verificado' => '🛡️',
                                'polizas_aprobadas' => '📄',
                                'cdp_emitido' => '💰',
                                'rp_emitido' => '💵',
                            ];
                        @endphp
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-xs font-medium text-gray-700">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold">
                                    <span>{{ $iconMap[$log->accion] ?? '📝' }}</span>
                                    <span class="capitalize">{{ str_replace('_',' ',$log->accion) }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($log->proceso)
                                <a href="{{ route('admin.logs.proceso', $log->proceso_id) }}"
                                   class="text-xs font-semibold hover:underline" style="color:#15803d">
                                    {{ $log->proceso->codigo }}
                                </a>
                                @else
                                <span class="text-xs text-gray-400">#{{ $log->proceso_id }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-xs font-medium text-gray-700">{{ optional($log->user)->name ?? 'Sistema' }}</p>
                                <p class="text-xs text-gray-400">{{ optional($log->user)->email ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs text-gray-500 max-w-xs truncate">{{ $log->descripcion ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs text-gray-400 font-mono">{{ $log->ip_address ?? '—' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <svg class="w-10 h-10 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm text-gray-400">No hay registros de auditoría.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="px-4 py-3 border-t" style="border-color:#f1f5f9">
                {{ $logs->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
