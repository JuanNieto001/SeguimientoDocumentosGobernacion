<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar usuario
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">

                <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="block mb-1">Nombre</label>
                        <input name="name" value="{{ old('name', $usuario->name) }}" class="border rounded w-full p-2">
                        @error('name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="block mb-1">Email</label>
                        <input name="email" type="email" value="{{ old('email', $usuario->email) }}" class="border rounded w-full p-2">
                        @error('email') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="block mb-1">Nueva contraseña (opcional)</label>
                        <input name="password" type="password" class="border rounded w-full p-2">
                        @error('password') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="block mb-1">Rol</label>
                        <select name="role" class="border rounded w-full p-2">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role', $currentRole) === $role->name)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button class="px-4 py-2 bg-black text-white rounded">Actualizar</button>
                        <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 border rounded">Volver</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
