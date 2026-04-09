<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.usuarios.index') }}"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Restablecer contraseña</h1>
                <p class="text-xs text-gray-400 mt-1">{{ $usuario->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 flex justify-center" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <div class="w-full max-w-md space-y-4">

            {{-- Info del usuario --}}
            <div class="bg-white rounded-2xl border p-5 flex items-center gap-4" style="border-color:#e2e8f0">
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-base font-bold shrink-0"
                     style="background:#14532d">
                    {{ strtoupper(substr($usuario->name,0,1)) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $usuario->name }}</p>
                    <p class="text-xs text-gray-400">{{ $usuario->email }}</p>
                    <p class="text-xs mt-1">
                        @foreach($usuario->getRoleNames() as $rol)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium" style="background:#dcfce7;color:#14532d">{{ $rol }}</span>
                        @endforeach
                    </p>
                </div>
            </div>

            @if(isset($tempPassword))
            {{-- Contraseña generada --}}
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#bbf7d0">
                <div class="px-5 py-3 flex items-center gap-2" style="background:#dcfce7">
                    <svg class="w-4 h-4 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs font-semibold text-green-800">Contraseña temporal generada</p>
                </div>
                <div class="p-6 text-center">
                    <p class="text-xs text-gray-500 mb-3">Entrega esta contraseña al usuario. El sistema no la volverá a mostrar.</p>
                    <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl font-mono text-2xl font-bold tracking-widest select-all"
                         style="background:#f0fdf4;border:2px dashed #86efac;color:#14532d"
                         id="tempPwd">
                        {{ $tempPassword }}
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Haz clic para seleccionar y copiar</p>

                    <div class="mt-5 p-3 rounded-xl text-xs text-left space-y-1" style="background:#fef9c3;border:1px solid #fde68a;color:#92400e">
                        <p class="font-semibold">Instrucciones para el usuario:</p>
                        <p>1. Ingresar con esta contraseña temporal.</p>
                        <p>2. Ir a Perfil → Cambiar contraseña.</p>
                        <p>3. Establecer una contraseña personal segura.</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('admin.usuarios.index') }}"
                   class="flex-1 text-center px-4 py-2.5 rounded-xl text-sm font-semibold border"
                   style="border-color:#e2e8f0;color:#374151">
                    Volver al listado
                </a>
                <form method="POST" action="{{ route('admin.reset-password.generate', $usuario) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
                            style="background:#2563eb"
                            onclick="return confirm('¿Generar nueva contraseña? La anterior dejará de funcionar.')">
                        Generar otra
                    </button>
                </form>
            </div>

            @else
            {{-- Formulario de confirmación --}}
            <div class="bg-white rounded-2xl border p-6 space-y-4" style="border-color:#e2e8f0">
                <div class="p-4 rounded-xl" style="background:#fef9c3;border:1px solid #fde68a">
                    <p class="text-xs text-yellow-800">
                        <strong>Atención:</strong> Se generará una contraseña temporal aleatoria y se guardará en el sistema.
                        La contraseña anterior del usuario quedará inactiva. El sistema te mostrará la nueva contraseña para que se la entregues al usuario.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.reset-password.generate', $usuario) }}">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-3 rounded-xl text-sm font-semibold text-white transition-colors hover:opacity-95"
                            style="background:linear-gradient(135deg,#15803d,#14532d)"
                            onclick="return confirm('¿Confirmas el restablecimiento de contraseña para {{ $usuario->name }}?')">
                        Generar contraseña temporal
                    </button>
                </form>

                <a href="{{ route('admin.usuarios.index') }}"
                   class="block text-center text-xs text-gray-400 hover:text-gray-600 mt-2">
                    Cancelar
                </a>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
