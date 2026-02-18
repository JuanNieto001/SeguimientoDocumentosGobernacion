<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Iniciar sesión</h2>
        <p class="text-gray-500 text-sm mt-1">Ingresa tus credenciales institucionales para continuar</p>
    </div>

    <x-auth-session-status class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm" :status="session('status')" />

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                Correo electrónico
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full px-4 py-3 rounded-xl border text-sm transition bg-white focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent {{ $errors->get('email') ? 'border-red-300' : 'border-gray-200' }}"
                   placeholder="usuario@gobernacion.gov.co" required autofocus autocomplete="username">
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-medium hover:underline" style="color:#166534">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password"
                   class="w-full px-4 py-3 rounded-xl border text-sm transition bg-white focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent {{ $errors->get('password') ? 'border-red-300' : 'border-gray-200' }}"
                   placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required autocomplete="current-password">
        </div>

        <div class="flex items-center gap-2.5">
            <input id="remember_me" type="checkbox" name="remember"
                   class="w-4 h-4 rounded border-gray-300 text-green-700 focus:ring-green-600">
            <label for="remember_me" class="text-sm text-gray-600">Mantener sesión iniciada</label>
        </div>

        <button type="submit"
                class="w-full py-3 px-4 text-white font-semibold rounded-xl text-sm transition-all duration-150 hover:opacity-90 active:scale-[.98]"
                style="background:#14532d">
            Ingresar al sistema
        </button>
    </form>

    <p class="mt-8 text-center text-xs text-gray-400">
        Gobernación de Manizales &mdash; Acceso restringido a usuarios autorizados
    </p>
</x-guest-layout>
