{{-- Archivo: backend/resources/views/backend/tracking/index.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-gray-900 leading-none">Rastreo por código único</h1>
            <p class="text-xs text-gray-400 mt-1">Consulta o registra entregas y recepciones ingresando el código del proceso</p>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success_tracking'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border text-sm font-medium"
             style="background:#dcfce7;border-color:#bbf7d0;color:#14532d">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success_tracking') }}
        </div>
        @endif

        @if(session('error_busqueda'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border text-sm font-medium"
             style="background:#fee2e2;border-color:#fecaca;color:#b91c1c">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ session('error_busqueda') }}
        </div>
        @endif

        <div class="grid lg:grid-cols-2 gap-5">

            {{-- Panel Consultar --}}
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color:#f1f5f9;background:#f8fafc">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#dbeafe">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800">Consultar proceso</h2>
                </div>
                <div class="p-5">
                    <form method="GET" action="{{ route('tracking.buscar') }}" class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                Código del proceso <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="codigo"
                                   value="{{ session('codigo_buscado') ?? old('codigo') }}"
                                   placeholder="Ej: CD_PN-2026-0001"
                                   class="w-full rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase"
                                   style="border:1px solid #e2e8f0"
                                   autocomplete="off">
                            @error('codigo')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors hover:opacity-95"
                                style="background:#2563eb">
                            Buscar proceso
                        </button>
                    </form>
                </div>
            </div>

            {{-- Panel Registrar entrega/recepción --}}
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color:#f1f5f9;background:#f8fafc">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#dcfce7">
                        <svg class="w-4 h-4 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800">Registrar entrega / recepción</h2>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('tracking.registrar') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                Código del proceso <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="codigo_proceso"
                                   value="{{ old('codigo_proceso') }}"
                                   placeholder="Ej: CD_PN-2026-0001"
                                   class="w-full rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 font-mono uppercase"
                                   style="border:1px solid #e2e8f0"
                                   autocomplete="off">
                            @error('codigo_proceso')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Tipo de evento <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border cursor-pointer transition-all hover:border-blue-300"
                                       style="border-color:#e2e8f0">
                                    <input type="radio" name="tipo" value="entrega" class="accent-blue-600" {{ old('tipo')=='entrega' ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-gray-700">📤 Entrega</span>
                                </label>
                                <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border cursor-pointer transition-all hover:border-green-300"
                                       style="border-color:#e2e8f0">
                                    <input type="radio" name="tipo" value="recepcion" class="accent-green-600" {{ old('tipo')=='recepcion' ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-gray-700">📥 Recepción</span>
                                </label>
                            </div>
                            @error('tipo')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Área de origen</label>
                                <input type="text" name="area_origen" value="{{ old('area_origen') }}"
                                       placeholder="Ej: Unidad de Planeación"
                                       class="w-full rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500"
                                       style="border:1px solid #e2e8f0">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Área destino</label>
                                <input type="text" name="area_destino" value="{{ old('area_destino') }}"
                                       placeholder="Ej: Secretaría Jurídica"
                                       class="w-full rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500"
                                       style="border:1px solid #e2e8f0">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Responsable físico</label>
                            <input type="text" name="responsable_nombre" value="{{ old('responsable_nombre') }}"
                                   placeholder="Nombre de quien entrega o recibe"
                                   class="w-full rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border:1px solid #e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Observaciones</label>
                            <textarea name="observaciones" rows="2"
                                      placeholder="Notas adicionales (opcional)"
                                      class="w-full rounded-xl px-3 py-2 text-xs resize-none focus:outline-none focus:ring-2 focus:ring-green-500"
                                      style="border:1px solid #e2e8f0">{{ old('observaciones') }}</textarea>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors hover:opacity-95"
                                style="background:linear-gradient(135deg,#15803d,#14532d)">
                            Registrar evento
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Actividad reciente del usuario --}}
        @if($ultimosEventos->isNotEmpty())
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Mis registros recientes</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($ultimosEventos as $ev)
                <div class="px-5 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                              style="background:{{ $ev->bg_tipo }};color:{{ $ev->color_tipo }}">
                            {{ $ev->label_tipo }}
                        </span>
                        <span class="text-sm font-mono font-semibold text-gray-700">{{ $ev->codigo_proceso }}</span>
                        @if($ev->observaciones)
                        <span class="text-xs text-gray-400 truncate max-w-xs">{{ $ev->observaciones }}</span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 shrink-0">{{ $ev->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-app-layout>

