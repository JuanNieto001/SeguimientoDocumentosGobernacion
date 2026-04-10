<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="breadcrumb-row text-xs text-gray-400 mb-0.5 leading-none">
                    <a href="{{ route('unidad.index') }}" class="hover:text-green-700 transition-colors">Unidad Solicitante</a>
                    <span class="mx-1">/</span>
                    <span class="text-gray-600 font-medium">Nuevo proceso</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Crear nuevo proceso de contratación</h1>
            </div>
            <a href="{{ route('unidad.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50 transition-all"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6 max-w-3xl mx-auto">
        @if(session('error'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium mb-6" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('unidad.crear') }}" class="space-y-8">
            @csrf

            {{-- ═══════════════════ SECCIÓN 1: Tipo de contratación ═══════════════════ --}}
            <div class="bg-white rounded-2xl border p-6" style="border-color:#e2e8f0">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#dcfce7">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Tipo de flujo</h2>
                        <p class="text-xs text-gray-400">Selecciona la modalidad de contratación</p>
                    </div>
                </div>

                <select name="workflow_id" id="workflow_id" required
                        class="w-full rounded-xl border-gray-300 text-sm focus:ring-green-500 focus:border-green-500 py-3">
                    <option value="">— Seleccionar tipo de contratación —</option>
                    @foreach($workflows as $wf)
                    <option value="{{ $wf->id }}" {{ old('workflow_id') == $wf->id ? 'selected' : '' }}>
                        {{ $wf->nombre }} ({{ $wf->codigo }})
                    </option>
                    @endforeach
                </select>
                @error('workflow_id')
                <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- ═══════════════════ SECCIÓN 2: Información del contrato ═══════════════════ --}}
            <div class="bg-white rounded-2xl border p-6" style="border-color:#e2e8f0">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#dbeafe">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Información del contrato</h2>
                        <p class="text-xs text-gray-400">Describe el objeto y valor estimado</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="objeto" class="block text-sm font-medium text-gray-700 mb-1.5">Objeto del contrato <span class="text-red-400">*</span></label>
                        <textarea name="objeto" id="objeto" rows="3" required
                                  class="w-full rounded-xl border-gray-300 text-sm focus:ring-green-500 focus:border-green-500"
                                  placeholder="Ej: Prestación de servicios profesionales para...">{{ old('objeto') }}</textarea>
                        @error('objeto')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="valor_estimado" class="block text-sm font-medium text-gray-700 mb-1.5">Valor estimado (COP) <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 text-sm font-medium">$</span>
                            <input type="number" name="valor_estimado" id="valor_estimado" step="0.01" min="0" required
                                   value="{{ old('valor_estimado') }}"
                                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-green-500 focus:border-green-500 pl-8 py-3"
                                   placeholder="0.00">
                        </div>
                        @error('valor_estimado')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ SECCIÓN 3: Observaciones ═══════════════════ --}}
            <div class="bg-white rounded-2xl border p-6" style="border-color:#e2e8f0">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Observaciones</h2>
                        <p class="text-xs text-gray-400">Información adicional (opcional)</p>
                    </div>
                </div>

                <textarea name="observaciones" id="observaciones" rows="2"
                          class="w-full rounded-xl border-gray-300 text-sm focus:ring-green-500 focus:border-green-500"
                          placeholder="Notas o información adicional para el trámite...">{{ old('observaciones') }}</textarea>
                @error('observaciones')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- ═══════════════════ ACCIONES ═══════════════════ --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('unidad.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm text-gray-500 hover:text-gray-700 rounded-xl hover:bg-gray-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-all shadow-sm"
                        style="background:#14532d">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Crear proceso
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
