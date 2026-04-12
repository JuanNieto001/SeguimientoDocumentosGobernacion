{{-- Archivo: backend/resources/views/frontend/auth/forgot-password.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-lg font-semibold" style="color:#1e293b">¿Olvidaste tu contraseña?</h2>
        <p class="text-sm mt-1" style="color:#64748b">Ingresa tu usuario o correo registrado en el sistema y el correo donde quieres recibir el enlace de restablecimiento.</p>
    </div>

    <!-- Mensaje de éxito -->
    @if (session('status'))
    <div class="mb-4 flex items-start gap-3 p-4 rounded-xl text-sm" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span>{{ session('status') }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        {{-- Campo 1: Usuario registrado en el sistema --}}
        <div>
            <label for="usuario_email" class="block text-sm font-medium mb-1" style="color:#374151">
                Tu usuario o correo registrado
            </label>
            <input id="usuario_email"
                   type="text"
                   name="usuario_email"
                   value="{{ old('usuario_email', request('usuario_email')) }}"
                   required autofocus
                   placeholder="usuario@gobernaciondecaldas.gov.co"
                   class="block w-full rounded-xl border px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                   style="border-color:{{ $errors->has('usuario_email') ? '#ef4444' : '#d1d5db' }};focus:ring-color:#14532d">
            @error('usuario_email')
            <p class="mt-1.5 text-xs" style="color:#dc2626">{{ $message }}</p>
            @enderror
        </div>

        {{-- Campo 2: Correo de destino --}}
        <div>
            <label for="correo_destino" class="block text-sm font-medium mb-1" style="color:#374151">
                Correo donde recibirás el enlace
            </label>
            <input id="correo_destino"
                   type="email"
                   name="correo_destino"
                   value="{{ old('correo_destino') }}"
                   required
                   placeholder="tu-correo@ejemplo.com"
                   class="block w-full rounded-xl border px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                   style="border-color:{{ $errors->has('correo_destino') ? '#ef4444' : '#d1d5db' }}">
            @error('correo_destino')
            <p class="mt-1.5 text-xs" style="color:#dc2626">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs" style="color:#94a3b8">Puede ser un correo personal o institucional diferente al registrado.</p>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('login') }}" class="text-sm" style="color:#14532d">← Volver al inicio de sesión</a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                    style="background:#14532d">
                Enviar enlace
            </button>
        </div>
    </form>
</x-guest-layout>

