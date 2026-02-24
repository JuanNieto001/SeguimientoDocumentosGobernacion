<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tracking.index') }}"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none font-mono">{{ $codigo }}</h1>
                <p class="text-xs text-gray-400 mt-1">Resultado de búsqueda</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- Info del proceso --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <h2 class="text-sm font-bold text-gray-800">Información del proceso</h2>
                @php
                $estadoBadge = match($proceso->estado) {
                    'EN_CURSO'   => ['bg'=>'#dcfce7','c'=>'#15803d'],
                    'FINALIZADO' => ['bg'=>'#dbeafe','c'=>'#1d4ed8'],
                    'EN_ESPERA'  => ['bg'=>'#fef9c3','c'=>'#92400e'],
                    default      => ['bg'=>'#f1f5f9','c'=>'#64748b'],
                };
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold"
                      style="background:{{ $estadoBadge['bg'] }};color:{{ $estadoBadge['c'] }}">
                    {{ $proceso->estado }}
                </span>
            </div>
            <div class="p-5 grid sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Código</p>
                    <p class="text-sm font-bold font-mono text-gray-900">{{ $proceso->codigo }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400 mb-0.5">Objeto</p>
                    <p class="text-sm text-gray-700">{{ $proceso->objeto }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Etapa actual</p>
                    <p class="text-sm font-semibold text-gray-800">{{ optional($proceso->etapaActual)->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Área responsable</p>
                    <p class="text-sm text-gray-700">{{ $proceso->area_actual_role ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Valor estimado</p>
                    <p class="text-sm font-semibold text-gray-900">
                        ${{ number_format($proceso->valor_estimado ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                @if($proceso->contratista_nombre)
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400 mb-0.5">Contratista</p>
                    <p class="text-sm text-gray-700">{{ $proceso->contratista_nombre }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Creado</p>
                    <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($proceso->created_at)->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Formulario rápido para registrar evento --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-bold text-gray-800">Registrar entrega o recepción de este proceso</h3>
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('tracking.registrar') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="codigo_proceso" value="{{ $codigo }}">
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Tipo de evento <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <label class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-xl border cursor-pointer transition-all hover:border-blue-300"
                                       style="border-color:#e2e8f0">
                                    <input type="radio" name="tipo" value="entrega" class="accent-blue-600">
                                    <span class="text-xs font-medium">📤 Entrega</span>
                                </label>
                                <label class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-xl border cursor-pointer transition-all hover:border-green-300"
                                       style="border-color:#e2e8f0">
                                    <input type="radio" name="tipo" value="recepcion" class="accent-green-600">
                                    <span class="text-xs font-medium">📥 Recepción</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Área origen</label>
                            <input type="text" name="area_origen" placeholder="Ej: Unidad de Planeación"
                                   class="w-full rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border:1px solid #e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Área destino</label>
                            <input type="text" name="area_destino" placeholder="Ej: Secretaría Jurídica"
                                   class="w-full rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border:1px solid #e2e8f0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Responsable físico</label>
                            <input type="text" name="responsable_nombre" value="{{ auth()->user()->name }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border:1px solid #e2e8f0">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Observaciones</label>
                            <input type="text" name="observaciones" placeholder="Notas adicionales (opcional)"
                                   class="w-full rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-green-500"
                                   style="border:1px solid #e2e8f0">
                        </div>
                    </div>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-95 transition-colors"
                            style="background:linear-gradient(135deg,#15803d,#14532d)">
                        Registrar evento
                    </button>
                </form>
            </div>
        </div>

        {{-- Historial de tracking --}}
        @if($historialTracking->isNotEmpty())
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Historial de movimientos físicos</h3>
                <span class="text-xs text-gray-400">{{ $historialTracking->count() }} registros</span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($historialTracking as $ev)
                <div class="px-5 py-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  style="background:{{ $ev->bg_tipo }};color:{{ $ev->color_tipo }}">
                                {{ $ev->label_tipo }}
                            </span>
                            @if($ev->area_origen || $ev->area_destino)
                            <span class="text-xs text-gray-500">
                                @if($ev->area_origen) {{ $ev->area_origen }} @endif
                                @if($ev->area_origen && $ev->area_destino) → @endif
                                @if($ev->area_destino) {{ $ev->area_destino }} @endif
                            </span>
                            @endif
                            @if($ev->responsable_nombre)
                            <span class="text-xs text-gray-400">por {{ $ev->responsable_nombre }}</span>
                            @endif
                        </div>
                        <span class="text-xs text-gray-400 shrink-0">{{ $ev->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($ev->observaciones)
                    <p class="text-xs text-gray-400 mt-1 pl-1">{{ $ev->observaciones }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
