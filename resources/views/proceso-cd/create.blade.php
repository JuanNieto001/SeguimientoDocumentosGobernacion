<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva Solicitud – Contratación Directa PN
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('proceso-cd.store') }}" enctype="multipart/form-data"
                  class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                @csrf

                {{-- Sección 1: Datos básicos --}}
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">
                        1. Datos del Contrato
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label for="objeto" class="block text-sm font-medium text-gray-700 mb-1">Objeto del contrato <span class="text-red-500">*</span></label>
                            <textarea id="objeto" name="objeto" rows="3" required
                                      class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500"
                                      placeholder="Describa el objeto del contrato…">{{ old('objeto') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="valor" class="block text-sm font-medium text-gray-700 mb-1">Valor ($) <span class="text-red-500">*</span></label>
                                <input type="number" id="valor" name="valor" value="{{ old('valor') }}" required min="0" step="0.01"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label for="plazo_meses" class="block text-sm font-medium text-gray-700 mb-1">
                                    Plazo (meses) <span class="text-red-500">*</span>
                                    <span class="text-xs text-gray-400 ml-1">Solo número entero</span>
                                </label>
                                <input type="number" id="plazo_meses" name="plazo_meses" value="{{ old('plazo_meses') }}" required min="1" step="1"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500"
                                       placeholder="Ej: 6">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección 2: Estudio Previo (Obligatorio) --}}
                <div class="px-6 py-5 border-b border-gray-100 bg-yellow-50">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-2">
                        2. Estudio Previo
                        <span class="text-red-600 text-xs ml-1">(Obligatorio antes de crear)</span>
                    </h3>
                    <p class="text-xs text-gray-500 mb-3">
                        Debe subir el documento de Estudios Previos para poder crear la solicitud.
                        Se enviará automáticamente a Planeación (Descentralización).
                    </p>
                    <input type="file" id="estudio_previo" name="estudio_previo" required accept=".pdf,.doc,.docx"
                           class="block w-full rounded-lg border border-gray-300 text-sm cursor-pointer bg-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                </div>

                {{-- Sección 3: Origen --}}
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">
                        3. Origen de la Solicitud
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="secretaria_id" class="block text-sm font-medium text-gray-700 mb-1">Secretaría <span class="text-red-500">*</span></label>
                            <select id="secretaria_id" name="secretaria_id" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">Seleccione…</option>
                                @foreach($secretarias as $sec)
                                    <option value="{{ $sec->id }}" {{ old('secretaria_id') == $sec->id ? 'selected' : '' }}>
                                        {{ $sec->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-1">Unidad <span class="text-red-500">*</span></label>
                            <select id="unidad_id" name="unidad_id" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">Seleccione…</option>
                                @foreach($unidades as $uni)
                                    <option value="{{ $uni->id }}" {{ old('unidad_id') == $uni->id ? 'selected' : '' }}>
                                        {{ $uni->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Sección 4: Contratista (Opcional al crear) --}}
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">
                        4. Datos del Contratista <span class="text-xs text-gray-400 font-normal">(Opcional al crear)</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="contratista_nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                            <input type="text" id="contratista_nombre" name="contratista_nombre" value="{{ old('contratista_nombre') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label for="contratista_tipo_documento" class="block text-sm font-medium text-gray-700 mb-1">Tipo documento</label>
                            <select id="contratista_tipo_documento" name="contratista_tipo_documento"
                                    class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Seleccione…</option>
                                <option value="CC" {{ old('contratista_tipo_documento') == 'CC' ? 'selected' : '' }}>C.C.</option>
                                <option value="CE" {{ old('contratista_tipo_documento') == 'CE' ? 'selected' : '' }}>C.E.</option>
                                <option value="PEP" {{ old('contratista_tipo_documento') == 'PEP' ? 'selected' : '' }}>PEP</option>
                            </select>
                        </div>
                        <div>
                            <label for="contratista_documento" class="block text-sm font-medium text-gray-700 mb-1">Número de documento</label>
                            <input type="text" id="contratista_documento" name="contratista_documento" value="{{ old('contratista_documento') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label for="contratista_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="contratista_email" name="contratista_email" value="{{ old('contratista_email') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label for="contratista_telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" id="contratista_telefono" name="contratista_telefono" value="{{ old('contratista_telefono') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                    <a href="{{ route('proceso-cd.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
                        ← Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Crear Solicitud y Enviar a Planeación
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
