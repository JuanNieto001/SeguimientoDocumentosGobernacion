<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('supervision.index', $proceso->id) }}"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Nuevo informe de supervisión</h1>
                <p class="text-xs text-gray-400 mt-1">{{ $proceso->codigo }} — Informe #{{ $ultimoNumero + 1 }}</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 flex justify-center" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <div class="w-full max-w-2xl">
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-6 py-5 border-b" style="border-color:#f1f5f9">
                    <h2 class="text-sm font-bold text-gray-800">Informe de ejecución contractual</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Proceso: {{ Str::limit($proceso->objeto, 70) }}</p>
                </div>
                <form method="POST" action="{{ route('supervision.guardar-informe', $proceso->id) }}"
                      enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf

                    {{-- Período --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Período inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="periodo_inicio" value="{{ old('periodo_inicio') }}"
                                   placeholder="Ej: Enero 2026"
                                   class="w-full rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('periodo_inicio') ring-2 ring-red-400 @enderror"
                                   style="border:1px solid #e2e8f0">
                            @error('periodo_inicio')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Período fin <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="periodo_fin" value="{{ old('periodo_fin') }}"
                                   placeholder="Ej: Enero 2026"
                                   class="w-full rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('periodo_fin') ring-2 ring-red-400 @enderror"
                                   style="border:1px solid #e2e8f0">
                            @error('periodo_fin')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Fecha del informe --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Fecha del informe <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="fecha_informe"
                               value="{{ old('fecha_informe', now()->format('Y-m-d')) }}"
                               class="w-full rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               style="border:1px solid #e2e8f0">
                        @error('fecha_informe')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Estado de avance + porcentaje --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Estado de avance <span class="text-red-500">*</span>
                            </label>
                            <select name="estado_avance"
                                    class="w-full rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    style="border:1px solid #e2e8f0">
                                <option value="en_ejecucion" {{ old('estado_avance')=='en_ejecucion' ? 'selected' : '' }}>En ejecución</option>
                                <option value="con_retraso"  {{ old('estado_avance')=='con_retraso'  ? 'selected' : '' }}>Con retraso</option>
                                <option value="completado"   {{ old('estado_avance')=='completado'   ? 'selected' : '' }}>Completado</option>
                                <option value="suspendido"   {{ old('estado_avance')=='suspendido'   ? 'selected' : '' }}>Suspendido</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                % de avance <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="range" name="porcentaje_avance" min="0" max="100" step="5"
                                       value="{{ old('porcentaje_avance', 0) }}"
                                       class="flex-1"
                                       oninput="document.getElementById('pctLabel').textContent = this.value + '%'">
                                <span id="pctLabel" class="text-sm font-bold text-gray-700 w-10 text-right">
                                    {{ old('porcentaje_avance', 0) }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Descripción de actividades <span class="text-red-500">*</span>
                        </label>
                        <textarea name="descripcion_actividades" rows="5" required
                                  placeholder="Detalle las actividades realizadas durante el período..."
                                  class="w-full rounded-xl px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descripcion_actividades') ring-2 ring-red-400 @enderror"
                                  style="border:1px solid #e2e8f0">{{ old('descripcion_actividades') }}</textarea>
                        @error('descripcion_actividades')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Observaciones --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Observaciones <span class="font-normal text-gray-400">(opcional)</span>
                        </label>
                        <textarea name="observaciones" rows="3"
                                  placeholder="Novedades, alertas o comentarios adicionales..."
                                  class="w-full rounded-xl px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  style="border:1px solid #e2e8f0">{{ old('observaciones') }}</textarea>
                    </div>

                    {{-- Archivo --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Archivo soporte <span class="font-normal text-gray-400">(PDF, Word — opcional)</span>
                        </label>
                        <input type="file" name="archivo_soporte" accept=".pdf,.doc,.docx"
                               class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-95 transition-all"
                                style="background:linear-gradient(135deg,#1d4ed8,#1e40af)">
                            Guardar informe
                        </button>
                        <a href="{{ route('supervision.index', $proceso->id) }}"
                           class="px-4 py-2.5 rounded-xl text-sm text-gray-500 border hover:bg-gray-50 transition-colors"
                           style="border-color:#e2e8f0">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
