{{-- Archivo: backend/resources/views/backend/supervision/crear-pago.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
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
                <h1 class="text-lg font-bold text-gray-900 leading-none">Registrar pago</h1>
                <p class="text-xs text-gray-400 mt-1">{{ $proceso->codigo }} — Pago #{{ $ultimoNumero + 1 }}</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 flex justify-center" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <div class="w-full max-w-lg">
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-6 py-5 border-b" style="border-color:#f1f5f9">
                    <h2 class="text-sm font-bold text-gray-800">Datos del pago</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Proceso: {{ Str::limit($proceso->objeto, 70) }}</p>
                </div>
                <form method="POST" action="{{ route('supervision.guardar-pago', $proceso->id) }}"
                      enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf

                    {{-- Valor --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Valor del pago ($) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="valor" value="{{ old('valor') }}"
                               min="1" step="0.01" placeholder="0.00" required
                               class="w-full rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('valor') ring-2 ring-red-400 @enderror"
                               style="border:1px solid #e2e8f0">
                        @error('valor')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Fechas --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Fecha de solicitud <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_solicitud"
                                   value="{{ old('fecha_solicitud', now()->format('Y-m-d')) }}" required
                                   class="w-full rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border:1px solid #e2e8f0">
                            @error('fecha_solicitud')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Fecha estimada de pago
                                <span class="font-normal text-gray-400">(para alerta)</span>
                            </label>
                            <input type="date" name="fecha_estimada_pago"
                                   value="{{ old('fecha_estimada_pago') }}"
                                   class="w-full rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border:1px solid #e2e8f0">
                            @error('fecha_estimada_pago')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Informe vinculado --}}
                    @if($informes->isNotEmpty())
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Informe de supervisión vinculado
                            <span class="font-normal text-gray-400">(opcional)</span>
                        </label>
                        <select name="informe_id"
                                class="w-full rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500"
                                style="border:1px solid #e2e8f0">
                            <option value="">— Sin vincular —</option>
                            @foreach($informes as $inf)
                            <option value="{{ $inf->id }}" {{ old('informe_id')==$inf->id ? 'selected' : '' }}>
                                Informe #{{ $inf->numero_informe }} — {{ $inf->periodo_inicio }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Observaciones --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Observaciones <span class="font-normal text-gray-400">(opcional)</span>
                        </label>
                        <textarea name="observaciones" rows="3"
                                  placeholder="Notas adicionales sobre este pago..."
                                  class="w-full rounded-xl px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-green-500"
                                  style="border:1px solid #e2e8f0">{{ old('observaciones') }}</textarea>
                    </div>

                    {{-- Comprobante --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Comprobante / Soporte <span class="font-normal text-gray-400">(PDF — opcional)</span>
                        </label>
                        <input type="file" name="archivo_soporte" accept=".pdf"
                               class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-95 transition-all"
                                style="background:linear-gradient(135deg,#15803d,#14532d)">
                            Guardar pago
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

