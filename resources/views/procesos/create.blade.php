<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('procesos.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Nueva solicitud</h1>
                <p class="text-xs text-gray-400 mt-1">Registrar un nuevo proceso de contratación</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <div class="max-w-2xl mx-auto">

            @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl border text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c">
                <p class="font-semibold mb-1">Por favor corrige los siguientes errores:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white rounded-2xl p-8" style="border:1px solid #e2e8f0">
                <form method="POST" action="{{ route('procesos.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipo de proceso (Workflow)</label>
                        <select id="workflow-select" name="workflow_id" required
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all"
                            style="border:1px solid #e2e8f0;background:#fff">
                            <option value="">— Selecciona un workflow —</option>
                            @foreach($workflows as $w)
                                <option value="{{ $w->id }}" data-codigo="{{ $w->codigo }}" @selected(old('workflow_id') == $w->id)>
                                    {{ $w->nombre }} ({{ $w->codigo }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Código <span class="font-normal text-gray-400">(autogenerado)</span></label>
                        <input id="codigo-input" name="codigo" value="{{ old('codigo') }}" placeholder="CD-2026-0001" readonly
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-500 outline-none"
                            style="border:1px solid #e2e8f0;background:#f8fafc">
                        <p class="text-xs text-gray-400 mt-1">El código se asigna automáticamente según el workflow seleccionado.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Objeto del contrato</label>
                        <input name="objeto" value="{{ old('objeto') }}" required placeholder="Ej: Adquisición de equipos de cómputo..."
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all"
                            style="border:1px solid #e2e8f0">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción <span class="font-normal text-gray-400">(opcional)</span></label>
                        <textarea name="descripcion" rows="4" placeholder="Descripción detallada del proceso..."
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all resize-none"
                            style="border:1px solid #e2e8f0">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-95 transition-all"
                            style="background:linear-gradient(135deg,#15803d,#14532d)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Crear proceso
                        </button>
                        <a href="{{ route('procesos.index') }}"
                            class="inline-flex items-center px-6 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition-all"
                            style="border:1px solid #e2e8f0;background:#fff">
                            Cancelar
                        </a>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const select = document.getElementById('workflow-select');
                        const codigoInput = document.getElementById('codigo-input');
                        const sugerirCodigo = () => {
                            const opt = select.options[select.selectedIndex];
                            const prefijo = opt?.dataset?.codigo || 'PR';
                            const year = new Date().getFullYear();
                            codigoInput.value = `${prefijo}-${year}-0001`;
                        };
                        select.addEventListener('change', sugerirCodigo);
                        if (!codigoInput.value) sugerirCodigo();
                    });
                </script>
            </div>

        </div>
    </div>
</x-app-layout>
