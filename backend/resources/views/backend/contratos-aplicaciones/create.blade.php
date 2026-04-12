{{-- Archivo: backend/resources/views/backend/contratos-aplicaciones/create.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-bold text-gray-900 leading-none">Nuevo contrato de aplicación</h1>
    </x-slot>

    <div class="p-6">
        <form method="POST" action="{{ route('contratos-aplicaciones.store') }}" class="bg-white rounded-2xl p-5 space-y-4" style="border:1px solid #e2e8f0">
            @include('contratos-aplicaciones._form')
            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="px-4 py-2 rounded-xl text-white text-sm font-semibold" style="background:linear-gradient(135deg,#15803d,#14532d)">
                    Guardar
                </button>
                <a href="{{ route('contratos-aplicaciones.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600" style="background:#f1f5f9">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>

