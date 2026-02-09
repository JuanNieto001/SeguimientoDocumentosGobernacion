<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Usuarios
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-3 border rounded bg-green-100 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">

                {{-- BOTÓN CREAR USUARIO --}}
                <div class="mb-4 flex justify-end">
                    <a href="{{ route('admin.usuarios.create') }}"
                    class="px-4 py-2 bg-gray-900 text-white font-medium rounded
                            hover:bg-gray-800 transition shadow">
                        + Nuevo usuario
                    </a>
                </div>


                <table class="w-full border">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-2 border text-left">ID</th>
                            <th class="p-2 border text-left">Nombre</th>
                            <th class="p-2 border text-left">Email</th>
                            <th class="p-2 border text-left">Rol</th>
                            <th class="p-2 border text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td class="p-2 border">{{ $u->id }}</td>
                                <td class="p-2 border">{{ $u->name }}</td>
                                <td class="p-2 border">{{ $u->email }}</td>
                                <td class="p-2 border">
                                    {{ $u->roles->pluck('name')->first() ?? '-' }}
                                </td>
                                <td class="p-2 border">
                                    <a class="underline mr-3 text-blue-600"
                                       href="{{ route('admin.usuarios.edit', $u) }}">
                                        Editar
                                    </a>

                                    <form class="inline"
                                          method="POST"
                                          action="{{ route('admin.usuarios.destroy', $u) }}"
                                          onsubmit="return confirm('¿Eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="underline text-red-600" type="submit">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-2 border" colspan="5">
                                    No hay usuarios aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

