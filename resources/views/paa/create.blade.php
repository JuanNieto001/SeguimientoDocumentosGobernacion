<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('paa.index') }}" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Registrar nueva necesidad</h1>
                <p class="text-xs text-gray-400 mt-1">Plan Anual de Adquisiciones — Gobernación de Caldas</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <form method="POST" action="{{ route('paa.store') }}" class="max-w-3xl mx-auto space-y-5">
            @csrf

            @if($errors->any())
            <div class="flex items-start gap-3 px-4 py-3 rounded-xl border text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <ul class="space-y-0.5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Sección 1: Identificación --}}
            <div class="bg-white rounded-2xl p-6 space-y-4" style="border:1px solid #e2e8f0">
                <div class="flex items-center gap-2.5 pb-3 border-b" style="border-color:#f1f5f9">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background:#166534">1</div>
                    <h2 class="text-sm font-bold text-gray-800">Identificación de la necesidad</h2>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Año --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Vigencia (año) <span class="text-red-500">*</span></label>
                        <select name="anio" required class="w-full text-sm rounded-xl px-3 py-2.5 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                            @for($y = $anioActual - 1; $y <= $anioActual + 2; $y++)
                            <option value="{{ $y }}" @selected($y == $anioActual)>{{ $y }}</option>
                            @endfor
                        </select>
                        @error('anio')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Código --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Código necesidad <span class="text-red-500">*</span></label>
                        <input type="text" name="codigo_necesidad" value="{{ old('codigo_necesidad') }}"
                               placeholder="Ej: PAA-2026-001"
                               class="w-full text-sm rounded-xl px-3 py-2.5 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                        @error('codigo_necesidad')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Descripción del objeto a contratar <span class="text-red-500">*</span></label>
                    <textarea name="descripcion" rows="3" required
                              placeholder="Describa el bien, servicio u obra que se pretende adquirir…"
                              class="w-full text-sm rounded-xl px-3 py-2.5 border focus:outline-none focus:ring-2 focus:ring-green-500 resize-none" style="border-color:#e2e8f0">{{ old('descripcion') }}</textarea>
                    @error('descripcion')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Dependencia --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dependencia solicitante <span class="text-red-500">*</span></label>
                    <input type="text" name="dependencia_solicitante" value="{{ old('dependencia_solicitante') }}"
                           placeholder="Secretaría / Dependencia que origina la necesidad"
                           class="w-full text-sm rounded-xl px-3 py-2.5 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                    @error('dependencia_solicitante')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Sección 2: Datos financieros y modalidad --}}
            <div class="bg-white rounded-2xl p-6 space-y-4" style="border:1px solid #e2e8f0">
                <div class="flex items-center gap-2.5 pb-3 border-b" style="border-color:#f1f5f9">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background:#166534">2</div>
                    <h2 class="text-sm font-bold text-gray-800">Datos financieros y contractuales</h2>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Valor estimado --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Valor estimado (COP) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium">$</span>
                            <input type="number" name="valor_estimado" value="{{ old('valor_estimado') }}"
                                   min="0" step="1" placeholder="0"
                                   class="w-full text-sm rounded-xl pl-7 pr-3 py-2.5 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                        </div>
                        @error('valor_estimado')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Modalidad --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Modalidad de contratación <span class="text-red-500">*</span></label>
                        <select name="modalidad_contratacion" required class="w-full text-sm rounded-xl px-3 py-2.5 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                            <option value="">Seleccionar modalidad…</option>
                            @foreach($modalidades as $cod => $label)
                            <option value="{{ $cod }}" @selected(old('modalidad_contratacion') == $cod)>{{ $cod }} — {{ $label }}</option>
                            @endforeach
                        </select>
                        @error('modalidad_contratacion')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Trimestre --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Trimestre estimado de inicio <span class="text-red-500">*</span></label>
                        <select name="trimestre_estimado" required class="w-full text-sm rounded-xl px-3 py-2.5 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                            <option value="">Seleccionar trimestre…</option>
                            @foreach($trimestres as $num => $label)
                            <option value="{{ $num }}" @selected(old('trimestre_estimado') == $num)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('trimestre_estimado')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Sección 3: Observaciones --}}
            <div class="bg-white rounded-2xl p-6 space-y-4" style="border:1px solid #e2e8f0">
                <div class="flex items-center gap-2.5 pb-3 border-b" style="border-color:#f1f5f9">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background:#166534">3</div>
                    <h2 class="text-sm font-bold text-gray-800">Observaciones <span class="text-gray-400 font-normal text-xs">(opcional)</span></h2>
                </div>
                <textarea name="observaciones" rows="3"
                          placeholder="Observaciones adicionales sobre la necesidad…"
                          class="w-full text-sm rounded-xl px-3 py-2.5 border focus:outline-none focus:ring-2 focus:ring-green-500 resize-none" style="border-color:#e2e8f0">{{ old('observaciones') }}</textarea>
                @error('observaciones')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-end gap-3 pt-1">
                <a href="{{ route('paa.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 bg-white rounded-xl border hover:bg-gray-50 transition-colors" style="border-color:#e2e8f0">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-semibold text-white rounded-xl transition-colors" style="background:#166534">
                    Registrar en PAA
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
