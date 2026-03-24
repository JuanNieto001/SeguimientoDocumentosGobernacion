<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Contratos de Aplicaciones</h1>
                <p class="text-xs text-gray-400 mt-1">Inventario contractual de aplicaciones de la Gobernación con referencia SECOP.</p>
            </div>
            @if(auth()->user()->hasAnyRole(['admin', 'admin_general', 'admin_secretaria']))
                <a href="{{ route('contratos-aplicaciones.create') }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-white"
                   style="background:linear-gradient(135deg,#15803d,#14532d)">Nuevo contrato</a>
            @endif
        </div>
    </x-slot>

    <div class="p-6 space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 rounded-xl text-sm font-medium" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534">{{ session('success') }}</div>
        @endif

        <form method="GET" class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <div class="flex gap-2">
                <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por aplicación, proveedor, número o SECOP..."
                       class="flex-1 rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
                <button class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#2563eb">Buscar</button>
            </div>
        </form>

        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="background:#f8fafc">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Aplicación</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Contrato</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Vigencia</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">SECOP</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Estado</th>
                            <th class="px-4 py-3 text-right text-xs text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:#f1f5f9">
                        @forelse($contratos as $c)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-800">{{ $c->aplicacion }}</p>
                                    <p class="text-xs text-gray-400">{{ $c->proveedor }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-700">{{ $c->numero_contrato ?: 'N/A' }}</p>
                                    <p class="text-xs text-gray-400">$ {{ number_format((float) $c->valor_total, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <div>{{ optional($c->fecha_inicio)->format('Y-m-d') ?: 'N/A' }}</div>
                                    <div>{{ optional($c->fecha_fin)->format('Y-m-d') ?: 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($c->secop_url)
                                        <a href="{{ $c->secop_url }}" target="_blank" class="text-blue-600 hover:underline text-xs">Abrir SECOP</a>
                                    @elseif($c->secop_proceso_id)
                                        <span class="text-xs text-gray-500">{{ $c->secop_proceso_id }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">Sin referencia</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs px-2 py-1 rounded-full" style="background:#f1f5f9;color:#334155">{{ strtoupper($c->estado) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('contratos-aplicaciones.show', $c) }}" class="text-xs font-semibold text-green-700 hover:underline">Ver</a>
                                    @if(auth()->user()->hasAnyRole(['admin', 'admin_general', 'admin_secretaria']))
                                        <a href="{{ route('contratos-aplicaciones.edit', $c) }}" class="text-xs font-semibold text-blue-700 hover:underline">Editar</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">No hay contratos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t" style="border-color:#f1f5f9">{{ $contratos->links() }}</div>
        </div>
    </div>
</x-app-layout>
