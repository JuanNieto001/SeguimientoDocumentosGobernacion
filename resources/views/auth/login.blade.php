<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Iniciar sesión</h2>
        <p class="text-gray-500 text-sm mt-1">Ingresa tus credenciales institucionales para continuar</p>
    </div>

    <x-auth-session-status class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm" :status="session('status')" />

    @if($errors->any())
        <div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl border text-sm font-medium"
             style="background:#fef2f2;border-color:#fecaca;color:#b91c1c">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                Correo electrónico
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full px-4 py-3 rounded-xl border text-sm transition bg-white focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent {{ $errors->has('email') ? 'border-red-300' : 'border-gray-200' }}"
                   placeholder="usuario@gobernacion.gov.co" required autofocus autocomplete="username">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña</label>
            <input id="password" type="password" name="password"
                   class="w-full px-4 py-3 rounded-xl border text-sm transition bg-white focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent {{ $errors->has('password') ? 'border-red-300' : 'border-gray-200' }}"
                   placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required autocomplete="current-password">
        </div>

        @if ($errors->any() && Route::has('password.request'))
            <div class="text-center">
                <a href="{{ route('password.request') . '?usuario_email=' . urlencode(old('email', '')) }}"
                   class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-xl border transition hover:bg-green-50"
                   style="color:#166534;border-color:#bbf7d0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        @endif

        <div class="flex items-baseline gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-gray-300 text-green-700 focus:ring-1 focus:ring-green-600 focus:ring-offset-0">
            <label for="remember_me" class="text-sm text-gray-600 select-none cursor-pointer">Mantener sesión iniciada</label>
        </div>

        <button type="submit"
                class="w-full py-3 px-4 text-white font-semibold rounded-xl text-sm transition-all duration-150 hover:opacity-90 active:scale-[.98]"
                style="background:#14532d">
            Ingresar al sistema
        </button>
    </form>

    <p class="mt-8 text-center text-xs text-gray-400">
        Gobernación de Caldas &mdash; Acceso restringido a usuarios autorizados
    </p>
</x-guest-layout>