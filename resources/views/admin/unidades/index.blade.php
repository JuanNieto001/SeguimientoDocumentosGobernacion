<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold text-gray-900">Unidades</h1>
            <a href="{{ route('admin.unidades.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                + Nueva unidad
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">
        @if (session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
        @endif

        <form method="GET" class="bg-white rounded-2xl border p-4 flex items-end gap-4" style="border-color:#e2e8f0">
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Filtrar por secretaría</label>
                <select name="secretaria_id" class="rounded-lg border-gray-300 text-sm">
                    <option value="">Todas</option>
                    @foreach ($secretarias as $sec)
                    <option value="{{ $sec->id }}" @selected(request('secretaria_id') == $sec->id)>{{ $sec->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Filtrar</button>
        </form>

        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Secretaría</th>
                        <th class="px-4 py-3 text-center">Usuarios</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:#f1f5f9">
                    @forelse ($unidades as $unidad)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $unidad->nombre }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $unidad->secretaria->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $unidad->usuarios_count }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $unidad->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $unidad->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.unidades.edit', $unidad) }}" class="text-blue-600 hover:underline text-xs">Editar</a>
                            <form method="POST" action="{{ route('admin.unidades.destroy', $unidad) }}" class="inline"
                                  onsubmit="return confirm('¿Seguro que desea eliminar esta unidad?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">No hay unidades registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $unidades->links() }}</div>
    </div>
</x-app-layout>
