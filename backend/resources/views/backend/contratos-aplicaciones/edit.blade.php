{{-- Archivo: backend/resources/views/backend/contratos-aplicaciones/edit.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-bold text-gray-900 leading-none">Editar contrato de aplicación</h1>
    </x-slot>

    <div class="p-6">
        <form method="POST" action="{{ route('contratos-aplicaciones.update', $contrato) }}" class="bg-white rounded-2xl p-5 space-y-4" style="border:1px solid #e2e8f0">
            @method('PUT')
            @include('contratos-aplicaciones._form')
            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 rounded-xl text-white text-sm font-semibold" style="background:linear-gradient(135deg,#15803d,#14532d)">
                        Guardar cambios
                    </button>
                    <a href="{{ route('contratos-aplicaciones.show', $contrato) }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600" style="background:#f1f5f9">
                        Cancelar
                    </a>
                </div>
                <button type="submit" form="delete-contrato" class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#dc2626"
                        onclick="return confirm('¿Eliminar este contrato de aplicación?')">
                    Eliminar
                </button>
            </div>
        </form>

        <form id="delete-contrato" method="POST" action="{{ route('contratos-aplicaciones.destroy', $contrato) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-app-layout>

