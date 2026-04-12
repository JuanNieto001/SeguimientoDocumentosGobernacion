{{-- Archivo: backend/resources/views/frontend/auth/login.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-guest-layout>
    {{-- Card contenedor del formulario --}}
    <div class="bg-white rounded-2xl p-8" style="box-shadow:0 4px 24px rgba(0,0,0,0.08),0 1px 4px rgba(0,0,0,0.04);border:1px solid #e2e8f0">

        <div class="mb-7">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" style="background:linear-gradient(135deg,#052e16,#166534)">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 leading-tight">Iniciar sesión</h2>
            <p class="text-gray-500 text-sm mt-1.5">Ingresa tus credenciales institucionales para continuar</p>
        </div>

        <x-auth-session-status class="mb-4 flex items-center gap-2 p-3 rounded-xl border text-sm font-medium" :status="session('status')" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d" />

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

            {{-- Email con icono --}}
            <div>
                <label for="email" class="block text-sm font-medium mb-1.5" style="color:#374151">
                    Correo electrónico
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4" style="color:#9ca3af" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="w-full pl-10 pr-4 py-3 rounded-xl border text-sm transition-all duration-150 bg-white focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent {{ $errors->has('email') ? 'border-red-300 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}"
                           placeholder="usuario@gobernacion.gov.co" required autofocus autocomplete="username">
                </div>
            </div>

            {{-- Password con icono y toggle --}}
            <div>
                <label for="password" class="block text-sm font-medium mb-1.5" style="color:#374151">Contraseña</label>
                <div class="relative" x-data="{ showPwd: false }">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4" style="color:#9ca3af" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <input id="password" :type="showPwd ? 'text' : 'password'" name="password"
                           class="w-full pl-10 pr-11 py-3 rounded-xl border text-sm transition-all duration-150 bg-white focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent {{ $errors->has('password') ? 'border-red-300 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}"
                           placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" @click="showPwd=!showPwd"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center transition-colors" style="color:#9ca3af" :style="showPwd ? 'color:#166534' : ''">
                        <svg x-show="!showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="rounded border-gray-300 text-green-700 focus:ring-1 focus:ring-green-600 focus:ring-offset-0">
                    <label for="remember_me" class="text-sm text-gray-600 select-none cursor-pointer">Mantener sesión</label>
                </div>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') . '?usuario_email=' . urlencode(old('email', '')) }}"
                   class="text-sm font-medium transition-colors hover:underline" style="color:#166534">
                    ¿Olvidaste tu contraseña?
                </a>
                @endif
            </div>

            <button type="submit"
                    class="w-full py-3 px-4 text-white font-semibold rounded-xl text-sm transition-all duration-200 active:scale-[.98] flex items-center justify-center gap-2"
                    style="background:linear-gradient(135deg,#14532d,#166534);box-shadow:0 4px 14px rgba(20,83,45,0.35)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Ingresar al sistema
            </button>
        </form>
    </div>

    <p class="mt-6 text-center text-xs text-gray-400">
        Gobernación de Caldas &mdash; Acceso restringido a usuarios autorizados
    </p>
</x-guest-layout>
