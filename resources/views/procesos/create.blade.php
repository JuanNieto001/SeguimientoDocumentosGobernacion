<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('procesos.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Nueva solicitud CD-PN</h1>
                <p class="text-xs text-gray-400 mt-1">Registrar un nuevo proceso de Contratación Directa — Prestación de Servicios</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <div class="max-w-3xl mx-auto space-y-5">

            @if($errors->any())
            <div class="flex items-start gap-3 px-4 py-3 rounded-xl border text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <div>
                    <p class="font-semibold mb-1">Corrige los siguientes errores:</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('procesos.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Sección 1: Identificación --}}
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-6 py-4 border-b flex items-center gap-2" style="border-color:#f1f5f9;background:#f8fafc">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background:#15803d">1</div>
                        <h2 class="text-sm font-semibold text-gray-700">Identificación del proceso</h2>
                    </div>
                    <div class="p-6 space-y-4">

                        {{-- Workflow (oculto si solo hay uno) --}}
                        @if(count($workflows) === 1)
                            <input type="hidden" name="workflow_id" value="{{ $workflows->first()->id }}">
                            <div class="flex items-center gap-2 text-sm text-gray-500 px-3 py-2 rounded-lg" style="background:#f0fdf4;border:1px solid #bbf7d0">
                                <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Workflow: <strong class="text-gray-700">{{ $workflows->first()->nombre }} ({{ $workflows->first()->codigo }})</strong></span>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipo de proceso (Workflow) <span class="text-red-500">*</span></label>
                                <select name="workflow_id" required class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all" style="border:1px solid #e2e8f0">
                                    <option value="">— Selecciona un workflow —</option>
                                    @foreach($workflows as $w)
                                        <option value="{{ $w->id }}" @selected(old('workflow_id') == $w->id)>{{ $w->nombre }} ({{ $w->codigo }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Objeto del contrato <span class="text-red-500">*</span></label>
                            <textarea name="objeto" required rows="3" placeholder="Ej: Prestación de servicios profesionales para apoyar la gestión de..." class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all resize-none" style="border:1px solid #e2e8f0">{{ old('objeto') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción <span class="font-normal text-gray-400">(opcional)</span></label>
                            <textarea name="descripcion" rows="2" placeholder="Detalle adicional del proceso..." class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all resize-none" style="border:1px solid #e2e8f0">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Sección 2: Secretaría y Unidad Solicitante --}}
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-6 py-4 border-b flex items-center gap-2" style="border-color:#f1f5f9;background:#f8fafc">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background:#2563eb">2</div>
                        <h2 class="text-sm font-semibold text-gray-700">Dependencia solicitante</h2>
                        @if($userSecretaria)
                        <span class="ml-auto text-xs text-gray-400 flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Asignada a tu perfil
                        </span>
                        @endif
                    </div>
                    <div class="p-6 grid sm:grid-cols-2 gap-4">
                        @if($userSecretaria)
                        {{-- Campos bloqueados: el usuario ya está anclado a una secretaría/unidad --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Secretaría</label>
                            <div class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 flex items-center gap-2" style="border:1px solid #e2e8f0;background:#f8fafc">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $userSecretaria->nombre }}
                            </div>
                            <input type="hidden" name="secretaria_origen_id" value="{{ $userSecretaria->id }}">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Unidad</label>
                            @if($userUnidad)
                            <div class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 flex items-center gap-2" style="border:1px solid #e2e8f0;background:#f8fafc">
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $userUnidad->nombre }}
                            </div>
                            <input type="hidden" name="unidad_origen_id" value="{{ $userUnidad->id }}">
                            @else
                            <select id="unidad-select" name="unidad_origen_id" required
                                class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all" style="border:1px solid #e2e8f0">
                                <option value="">— Selecciona unidad —</option>
                                @foreach($unidadesPreload as $u)
                                    <option value="{{ $u->id }}" @selected(old('unidad_origen_id', auth()->user()->unidad_id) == $u->id)>
                                        {{ $u->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                        @else
                        {{-- Admin: puede seleccionar cualquier secretaría/unidad --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Secretaría <span class="text-red-500">*</span></label>
                            <select id="secretaria-select" name="secretaria_origen_id" required
                                class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all" style="border:1px solid #e2e8f0">
                                <option value="">— Selecciona secretaría —</option>
                                @foreach($secretarias as $s)
                                    <option value="{{ $s->id }}"
                                        @selected(old('secretaria_origen_id') == $s->id)>
                                        {{ $s->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Unidad <span class="text-red-500">*</span></label>
                            <select id="unidad-select" name="unidad_origen_id" required
                                class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all" style="border:1px solid #e2e8f0">
                                <option value="">— Selecciona secretaría primero —</option>
                            </select>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Sección 3: Datos económicos --}}
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-6 py-4 border-b flex items-center gap-2" style="border-color:#f1f5f9;background:#f8fafc">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background:#ca8a04">3</div>
                        <h2 class="text-sm font-semibold text-gray-700">Datos económicos</h2>
                    </div>
                    <div class="p-6 grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Valor estimado (COP) <span class="font-normal text-gray-400">(opcional)</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium">$</span>
                                <input type="number" name="valor_estimado" value="{{ old('valor_estimado') }}" min="0" step="1000" placeholder="0"
                                    class="w-full rounded-xl pl-7 pr-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all" style="border:1px solid #e2e8f0">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Plazo de ejecución (meses) <span class="text-red-500">*</span></label>
                            <input type="number" name="plazo_ejecucion_meses" value="{{ old('plazo_ejecucion_meses') }}" required min="1" max="60" placeholder="Ejemplo: 4"
                                class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all" style="border:1px solid #e2e8f0">
                            <p class="text-xs text-gray-500 mt-1">Ingrese solo el número de meses (1-60)</p>
                        </div>
                    </div>
                </div>

                {{-- Sección 4: Estudios Previos (OBLIGATORIO) --}}
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-6 py-4 border-b flex items-center gap-2" style="border-color:#f1f5f9;background:#f8fafc">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background:#dc2626">4</div>
                        <h2 class="text-sm font-semibold text-gray-700">Estudios Previos <span class="text-red-500">*</span></h2>
                    </div>
                    <div class="p-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Cargar documento de Estudios Previos <span class="text-red-500">*</span></label>
                            <input type="file" name="estudios_previos" required accept=".pdf,.doc,.docx"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer"
                                style="border:1px solid #e2e8f0; border-radius:12px; padding:8px">
                            <p class="text-xs text-gray-500 mt-1.5">Formatos permitidos: PDF, DOC, DOCX. Este archivo es obligatorio para crear la solicitud.</p>
                        </div>
                    </div>
                </div>

                {{-- Sección 5: Datos del contratista (opcional/colapsable) --}}
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0" x-data="{ open: {{ old('contratista_nombre') ? 'true' : 'false' }} }">
                    <button type="button" @click="open=!open"
                        class="w-full px-6 py-4 border-b flex items-center justify-between text-left transition-colors hover:bg-gray-50" style="border-color:#f1f5f9;background:#f8fafc">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background:#7c3aed">5</div>
                            <span class="text-sm font-semibold text-gray-700">Datos del contratista</span>
                            <span class="text-xs text-gray-400">(opcional — se puede completar después)</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="p-6 grid sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre / Razón social</label>
                            <input type="text" name="contratista_nombre" value="{{ old('contratista_nombre') }}" placeholder="Nombre completo o razón social..."
                                class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all" style="border:1px solid #e2e8f0">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipo de documento</label>
                            <select name="contratista_tipo_documento" class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500" style="border:1px solid #e2e8f0">
                                <option value="">— Tipo —</option>
                                <option value="CC"  @selected(old('contratista_tipo_documento')=='CC' )>CC — Cédula Ciudadanía</option>
                                <option value="CE"  @selected(old('contratista_tipo_documento')=='CE' )>CE — Cédula Extranjería</option>
                                <option value="NIT" @selected(old('contratista_tipo_documento')=='NIT')>NIT — Persona Jurídica</option>
                                <option value="PA"  @selected(old('contratista_tipo_documento')=='PA' )>PA — Pasaporte</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Número de documento</label>
                            <input type="text" name="contratista_documento" value="{{ old('contratista_documento') }}" placeholder="Ej: 12345678-9"
                                class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all" style="border:1px solid #e2e8f0">
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex items-center gap-3 pb-2">
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

        </div>
    </div>

    @unless($userSecretaria)
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const secSelect = document.getElementById('secretaria-select');
        const unidadSelect = document.getElementById('unidad-select');
        const oldUnidad = '{{ old('unidad_origen_id') }}';

        const loadUnidades = async (secId, selectVal = null) => {
            if (!secId) {
                unidadSelect.innerHTML = '<option value="">— Selecciona secretaría primero —</option>';
                return;
            }
            unidadSelect.innerHTML = '<option value="">Cargando...</option>';
            try {
                const res = await fetch(`/api/secretarias/${secId}/unidades`);
                const data = await res.json();
                unidadSelect.innerHTML = '<option value="">— Selecciona unidad —</option>';
                data.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.nombre;
                    if (String(u.id) === String(selectVal)) opt.selected = true;
                    unidadSelect.appendChild(opt);
                });
            } catch (e) {
                unidadSelect.innerHTML = '<option value="">Error al cargar</option>';
            }
        };

        secSelect.addEventListener('change', () => loadUnidades(secSelect.value));

        if (secSelect.value) {
            loadUnidades(secSelect.value, oldUnidad);
        }
    });
    </script>
    @endunless
</x-app-layout>
