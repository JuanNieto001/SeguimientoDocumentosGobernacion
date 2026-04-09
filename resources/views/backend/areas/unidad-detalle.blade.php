@php use Illuminate\Support\Facades\DB; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-0.5">
                    <a href="{{ route('unidad.index') }}" class="hover:text-green-700 transition-colors">Unidad Solicitante</a>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">{{ $proceso->codigo }}</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Detalle del proceso</h1>
            </div>
            <a href="{{ route('unidad.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50 transition-all"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">

        {{-- Alertas --}}
        @if(session('success'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('error') }}
        </div>
        @endif
        @if(session('info'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('info') }}
        </div>
        @endif

        {{-- Info del proceso --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ $proceso->codigo }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $proceso->objeto }}</p>
                </div>
                @php
                    $estadoConfig = [
                        'EN_CURSO'   => ['bg'=>'#dbeafe','text'=>'#1d4ed8','label'=>'En curso'],
                        'FINALIZADO' => ['bg'=>'#dcfce7','text'=>'#15803d','label'=>'Finalizado'],
                        'RECHAZADO'  => ['bg'=>'#fee2e2','text'=>'#b91c1c','label'=>'Rechazado'],
                    ];
                    $ec = $estadoConfig[$proceso->estado] ?? ['bg'=>'#f1f5f9','text'=>'#475569','label'=>$proceso->estado];
                @endphp
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold shrink-0"
                      style="background:{{ $ec['bg'] }};color:{{ $ec['text'] }}">
                    {{ $ec['label'] }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-t pt-4" style="border-color:#f1f5f9">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Flujo</p>
                    <p class="font-medium text-gray-700">{{ $proceso->flujo_id ? optional(DB::table('flujos')->where('id', $proceso->flujo_id)->first())->nombre : (optional($proceso->workflow)->nombre ?? 'N/D') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Etapa actual</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->etapaActual)->nombre ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Valor estimado</p>
                    <p class="font-medium text-gray-700">$ {{ number_format($proceso->valor_estimado ?? 0, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Creado por</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->creador)->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Documento de Estudios Previos --}}
        @php
            $documentoEstudiosPrevios = DB::table('proceso_etapa_archivos')
                ->where('proceso_id', $proceso->id)
                ->where('tipo_archivo', 'estudios_previos')
                ->first();
        @endphp
        @if($documentoEstudiosPrevios)
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📄 Documento de Estudios Previos</h3>
            </div>
            <div class="p-5">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">{{ $documentoEstudiosPrevios->nombre_original }}</p>
                            <p class="text-xs text-gray-600 mt-1">
                                Subido el {{ \Carbon\Carbon::parse($documentoEstudiosPrevios->uploaded_at)->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('workflow.files.download', ['archivo' => $documentoEstudiosPrevios->id, 'inline' => 1]) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-all"
                               target="_blank">Ver</a>
                            <a href="{{ route('workflow.files.download', $documentoEstudiosPrevios->id) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-all"
                               target="_blank">Descargar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Timeline de etapas --}}
        @if($etapas->count() > 0)
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📋 Etapas del flujo ({{ $etapas->count() }})</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($etapas as $etapa)
                    @php
                        $currentOrden = optional($proceso->etapaActual)->orden ?? -1;
                        if ($etapa->orden < $currentOrden) {
                            $estilo = ['bg' => '#dcfce7', 'text' => '#15803d', 'icon' => '✓'];
                        } elseif ($etapa->id == $proceso->etapa_actual_id) {
                            $estilo = ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'icon' => '●'];
                        } else {
                            $estilo = ['bg' => '#f1f5f9', 'text' => '#94a3b8', 'icon' => ($etapa->orden + 1)];
                        }
                    @endphp
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold"
                             style="background:{{ $estilo['bg'] }};color:{{ $estilo['text'] }}">
                            <span>{{ $estilo['icon'] }}</span>
                            <span>{{ $etapa->nombre }}</span>
                        </div>
                        @if(!$loop->last)
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Recepción --}}
        @if($procesoEtapaActual && $proceso->estado === 'EN_CURSO')
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Recepción</h3>
            <form method="POST" action="{{ route('workflow.recibir', $proceso->id) }}">
                @csrf
                <input type="hidden" name="area_role" value="unidad_solicitante">
                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                        style="background:{{ $recibido ? '#f0fdf4' : '#14532d' }};color:{{ $recibido ? '#15803d' : '#fff' }};border:{{ $recibido ? '1px solid #bbf7d0' : 'none' }}"
                        {{ $recibido ? 'disabled' : '' }}>
                        {{ $recibido ? '✓ Recibido' : 'Marcar como recibido' }}
                    </button>
                    @if($recibido && $procesoEtapaActual->recibido_at)
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($procesoEtapaActual->recibido_at)->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
            </form>
        </div>
        @endif

        {{-- Checks / documentos de la etapa --}}
        @if($checks->count() > 0)
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📝 Lista de verificación</h3>
                @php
                    $totalChecks = $checks->count();
                    $checkedCount = $checks->where('checked', true)->count();
                @endphp
                <span class="text-xs px-2 py-1 rounded-full font-semibold"
                      style="background:{{ $checkedCount === $totalChecks ? '#dcfce7' : '#fef3c7' }}; color:{{ $checkedCount === $totalChecks ? '#15803d' : '#92400e' }}">
                    {{ $checkedCount }}/{{ $totalChecks }} completados
                </span>
            </div>
            <div class="p-4 space-y-2">
                @foreach($checks as $check)
                <form method="POST" action="{{ route('workflow.checks.toggle', [$proceso->id, $check->id]) }}">
                    @csrf
                    <input type="hidden" name="area_role" value="unidad_solicitante">
                    <button type="submit"
                            class="w-full flex items-center gap-3 p-3 rounded-xl text-left text-sm transition-all"
                            style="background:{{ $check->checked ? '#f0fdf4' : '#f8fafc' }};border:1px solid {{ $check->checked ? '#bbf7d0' : '#e2e8f0' }}"
                            {{ !$recibido ? 'disabled' : '' }}>
                        <span class="text-base">{{ $check->checked ? '✅' : '☐' }}</span>
                        <span class="font-medium text-gray-700">{{ $check->label }}</span>
                        @if($check->requerido)
                        <span class="ml-auto text-xs text-gray-400">(requerido)</span>
                        @endif
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Enviar a siguiente etapa --}}
        @if($procesoEtapaActual && $proceso->estado === 'EN_CURSO')
        @php
            $allRequired = $checks->where('requerido', true);
            $allRequiredDone = $allRequired->count() > 0 ? $allRequired->every(fn($c) => $c->checked) : true;
            $canSend = $recibido && $allRequiredDone;
            $yaEnviado = $procesoEtapaActual->enviado ?? false;
        @endphp
        @if($yaEnviado)
        <div class="p-3.5 rounded-xl flex items-center gap-3 text-sm font-semibold"
             style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
            ✅ Etapa enviada correctamente
            @if($procesoEtapaActual->enviado_at)
            — {{ \Carbon\Carbon::parse($procesoEtapaActual->enviado_at)->format('d/m/Y H:i') }}
            @endif
        </div>
        @else
        <form method="POST" action="{{ route('workflow.enviar', $proceso->id) }}">
            @csrf
            <input type="hidden" name="area_role" value="unidad_solicitante">
            <button type="submit"
                @if($canSend)
                    onclick="return confirm('¿Confirmar envío a la siguiente etapa?')"
                @else
                    disabled
                    title="Complete todos los checks obligatorios y marque como recibido primero"
                @endif
                class="w-full px-4 py-3 rounded-xl text-white text-sm font-bold transition"
                style="background:{{ $canSend ? '#14532d' : '#9ca3af' }};
                       cursor:{{ $canSend ? 'pointer' : 'not-allowed' }};
                       opacity:{{ $canSend ? '1' : '0.6' }}">
                @if($canSend)
                    📤 Enviar a la siguiente etapa
                @else
                    🔒 Complete los checks obligatorios para poder enviar
                @endif
            </button>
        </form>
        @endif
        @endif

        {{-- Archivos de etapas anteriores --}}
        @php $archivosAnteriores = $proceso->archivos->where('etapa_id', '!=', $proceso->etapa_actual_id); @endphp
        @if($archivosAnteriores->isNotEmpty())
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <details class="rounded-xl overflow-hidden">
                <summary class="px-5 py-4 text-sm font-semibold text-gray-700 cursor-pointer" style="background:#f8fafc">
                    📎 Archivos de etapas anteriores ({{ $archivosAnteriores->count() }})
                </summary>
                <div class="p-4 space-y-2">
                    @foreach($archivosAnteriores as $archivo)
                    <div class="flex items-center justify-between p-3 rounded-xl" style="background:#f8fafc;border:1px solid #e2e8f0">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $archivo->nombre_original }}</p>
                            <p class="text-xs text-gray-400">{{ str_replace('_',' ',$archivo->tipo_archivo) }}</p>
                        </div>
                        <a href="{{ route('workflow.files.download', $archivo->id) }}"
                           class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">Descargar</a>
                    </div>
                    @endforeach
                </div>
            </details>
        </div>
        @endif

    </div>
</x-app-layout>
