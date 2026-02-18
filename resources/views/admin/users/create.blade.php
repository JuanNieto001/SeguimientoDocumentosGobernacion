<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Nuevo usuario</h1>
                <p class="text-xs text-gray-400 mt-1">Crear una cuenta con acceso al sistema</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <div class="max-w-xl mx-auto">
            <div class="bg-white rounded-2xl p-8" style="border:1px solid #e2e8f0">
                <form method="POST" action="{{ route('admin.usuarios.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre completo</label>
                        <input name="name" value="{{ old('name') }}" placeholder="Ej: Juan García"
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all"
                            style="border:1px solid #e2e8f0">
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Correo electrónico</label>
                        <input name="email" type="email" value="{{ old('email') }}" placeholder="usuario@caldas.gov.co"
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all"
                            style="border:1px solid #e2e8f0">
                        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Contraseña</label>
                        <input name="password" type="password" placeholder="Mínimo 8 caracteres"
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all"
                            style="border:1px solid #e2e8f0">
                        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Rol</label>
                        <select name="role"
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all"
                            style="border:1px solid #e2e8f0">
                            <option value="">— Selecciona un rol —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-95 transition-all"
                            style="background:linear-gradient(135deg,#15803d,#14532d)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Guardar usuario
                        </button>
                        <a href="{{ route('admin.usuarios.index') }}"
                            class="inline-flex items-center px-6 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition-all"
                            style="border:1px solid #e2e8f0;background:#fff">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
