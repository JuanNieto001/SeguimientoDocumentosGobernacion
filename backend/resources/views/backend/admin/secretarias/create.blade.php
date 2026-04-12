{{-- Archivo: backend/resources/views/backend/admin/secretarias/create.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold text-gray-900">Nueva Secretaría</h1>
            <a href="{{ route('admin.secretarias.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Cancelar
            </a>
        </div>
    </x-slot>

    <div class="p-6 max-w-xl">
        <form method="POST" action="{{ route('admin.secretarias.store') }}" class="bg-white rounded-2xl border p-6 space-y-4" style="border-color:#e2e8f0">
            @csrf
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="activo" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="activo" class="text-sm text-gray-700">Activa</label>
            </div>
            <div class="pt-2">
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">Guardar</button>
            </div>
        </form>
    </div>
</x-app-layout>

