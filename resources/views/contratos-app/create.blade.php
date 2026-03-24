<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Nuevo Contrato de Aplicación</h1>
                <p class="text-xs text-gray-400 mt-1">Registra un contrato de software o servicio tecnológico</p>
            </div>
            <a href="{{ route('contratos-app.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-xl border transition-colors"
               style="border-color:#e2e8f0;color:#374151">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <div class="max-w-3xl mx-auto">
            <form method="POST" action="{{ route('contratos-app.store') }}"
                  class="rounded-2xl shadow-sm border p-6 space-y-5"
                  style="background:#fff;border-color:#e2e8f0">
                @csrf

                @if($errors->any())
                <div class="px-4 py-3 rounded-xl border text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Información de la aplicación --}}
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b" style="border-color:#f1f5f9">
                        Información de la aplicación
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Nombre de la aplicación <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre_aplicacion" value="{{ old('nombre_aplicacion') }}"
                                   required
                                   class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border-color:#e2e8f0"
                                   placeholder="Ej: Sistema de Gestión Documental, Módulo de Nómina…">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Proveedor</label>
                            <input type="text" name="proveedor" value="{{ old('proveedor') }}"
                                   class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border-color:#e2e8f0"
                                   placeholder="Nombre de la empresa proveedora">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Modalidad de contratación</label>
                            <select name="modalidad_contratacion"
                                    class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                    style="border-color:#e2e8f0">
                                <option value="">Seleccione…</option>
                                @foreach($modalidades as $m)
                                <option value="{{ $m }}" {{ old('modalidad_contratacion') === $m ? 'selected' : '' }}>
                                    {{ $m }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="3"
                                      class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                      style="border-color:#e2e8f0"
                                      placeholder="Descripción del servicio o sistema…">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Datos del contrato --}}
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b" style="border-color:#f1f5f9">
                        Datos del contrato
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Número de contrato</label>
                            <input type="text" name="numero_contrato" value="{{ old('numero_contrato') }}"
                                   class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border-color:#e2e8f0"
                                   placeholder="Ej: GC-2025-001">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Valor del contrato (COP)</label>
                            <input type="number" name="valor_contrato" value="{{ old('valor_contrato') }}"
                                   min="0" step="0.01"
                                   class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border-color:#e2e8f0"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Fecha de inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}"
                                   required
                                   class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border-color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Fecha de finalización <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}"
                                   required
                                   class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border-color:#e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                            <select name="estado" required
                                    class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                    style="border-color:#e2e8f0">
                                <option value="activo"    {{ old('estado', 'activo') === 'activo'    ? 'selected' : '' }}>Activo</option>
                                <option value="vencido"   {{ old('estado') === 'vencido'   ? 'selected' : '' }}>Vencido</option>
                                <option value="cancelado" {{ old('estado') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Referencia SECOP --}}
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b" style="border-color:#f1f5f9">
                        Referencia SECOP II
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                ID en SECOP
                                <span class="ml-1 text-gray-400" title="Identificador del proceso en el Sistema Electrónico de Contratación Pública">ⓘ</span>
                            </label>
                            <input type="text" name="secop_id" value="{{ old('secop_id') }}"
                                   class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border-color:#e2e8f0"
                                   placeholder="Ej: CO1.BDOS.123456">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">URL en SECOP</label>
                            <input type="url" name="secop_url" value="{{ old('secop_url') }}"
                                   class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border-color:#e2e8f0"
                                   placeholder="https://www.secop.gov.co/…">
                        </div>
                    </div>
                </div>

                {{-- Dependencia responsable --}}
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b" style="border-color:#f1f5f9">
                        Dependencia responsable
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Secretaría</label>
                            <select name="secretaria_id"
                                    class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                    style="border-color:#e2e8f0">
                                <option value="">— Sin secretaría —</option>
                                @foreach($secretarias as $sec)
                                <option value="{{ $sec->id }}" {{ old('secretaria_id') == $sec->id ? 'selected' : '' }}>
                                    {{ $sec->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unidad</label>
                            <select name="unidad_id"
                                    class="w-full text-sm rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                    style="border-color:#e2e8f0">
                                <option value="">— Sin unidad —</option>
                                @foreach($unidades as $u)
                                <option value="{{ $u->id }}" {{ old('unidad_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('contratos-app.index') }}"
                       class="px-4 py-2 text-sm rounded-xl border"
                       style="border-color:#e2e8f0;color:#64748b">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2 text-sm font-semibold rounded-xl text-white transition-colors"
                            style="background:#166534">
                        Guardar contrato
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
