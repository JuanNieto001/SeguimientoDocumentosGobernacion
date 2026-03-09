<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold text-gray-900">Secretarías</h1>
            <a href="{{ route('admin.secretarias.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                + Nueva secretaría
            </a>
        </div>
    </x-slot>

    <div class="p-6">
        @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3 text-center">Unidades</th>
                        <th class="px-4 py-3 text-center">Usuarios</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:#f1f5f9">
                    @forelse ($secretarias as $secretaria)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $secretaria->nombre }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $secretaria->unidades_count }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $secretaria->usuarios_count }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $secretaria->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $secretaria->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.secretarias.edit', $secretaria) }}" class="text-blue-600 hover:underline text-xs">Editar</a>
                            <form method="POST" action="{{ route('admin.secretarias.destroy', $secretaria) }}" class="inline"
                                  onsubmit="return confirm('¿Seguro que desea eliminar esta secretaría?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">No hay secretarías registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $secretarias->links() }}</div>
    </div>
</x-app-layout>
