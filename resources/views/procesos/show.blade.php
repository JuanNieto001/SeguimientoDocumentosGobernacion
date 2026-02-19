<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('procesos.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-xs text-gray-400 mb-0.5">
                        <a href="{{ route('procesos.index') }}" class="hover:text-green-700 transition-colors">Procesos</a>
                        <span>/</span>
                        <span class="font-mono font-semibold text-gray-600">{{ $proceso->codigo }}</span>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900 leading-none">Expediente del proceso</h1>
                </div>
            </div>
            @php
                $estadoConfig = [
                    'EN_CURSO'   => ['bg'=>'#dbeafe','text'=>'#1d4ed8','label'=>'En curso'],
                    'FINALIZADO' => ['bg'=>'#dcfce7','text'=>'#15803d','label'=>'Finalizado'],
                    'RECHAZADO'  => ['bg'=>'#fee2e2','text'=>'#b91c1c','label'=>'Rechazado'],
                ];
                $ec = $estadoConfig[$proceso->estado] ?? ['bg'=>'#f1f5f9','text'=>'#475569','label'=>$proceso->estado];
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                  style="background:{{ $ec['bg'] }};color:{{ $ec['text'] }}">
                {{ $ec['label'] }}
            </span>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-5">

            {{-- Columna izquierda: Info + Timeline --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- ── Datos del proceso ── --}}
                <div class="bg-white rounded-2xl p-5 space-y-4" style="border:1px solid #e2e8f0">
                    <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Información del proceso
                    </h2>

                    <div class="p-4 rounded-xl" style="background:#f8fafc;border:1px solid #e2e8f0">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Objeto del contrato</p>
                        <p class="text-sm font-medium text-gray-800">{{ $proceso->objeto }}</p>
                        @if($proceso->descripcion)
                        <p class="text-xs text-gray-500 mt-1">{{ $proceso->descripcion }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Código</p>
                            <p class="font-mono font-bold text-gray-900">{{ $proceso->codigo }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Workflow</p>
                            <p class="font-medium text-gray-700">{{ optional($proceso->workflow)->codigo ?? 'N/D' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Creado el</p>
                            <p class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($proceso->created_at)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Secretaría solicitante</p>
                            <p class="font-medium text-gray-700">{{ optional($proceso->secretariaOrigen)->nombre ?? 'N/D' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Unidad solicitante</p>
                            <p class="font-medium text-gray-700">{{ optional($proceso->unidadOrigen)->nombre ?? 'N/D' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Creado por</p>
                            <p class="font-medium text-gray-700">{{ optional($proceso->creador)->name ?? 'N/A' }}</p>
                        </div>
                        @if($proceso->valor_estimado)
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Valor estimado</p>
                            <p class="font-bold text-gray-900">$ {{ number_format($proceso->valor_estimado, 0, ',', '.') }}</p>
                        </div>
                        @endif
                        @if($proceso->plazo_ejecucion)
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Plazo ejecución</p>
                            <p class="font-medium text-gray-700">{{ $proceso->plazo_ejecucion }}</p>
                        </div>
                        @endif
                        @if($proceso->numero_cdp)
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">CDP N°</p>
                            <p class="font-bold" style="color:#15803d">{{ $proceso->numero_cdp }}</p>
                        </div>
                        @endif
                        @if($proceso->numero_rp)
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">RP N°</p>
                            <p class="font-bold" style="color:#2563eb">{{ $proceso->numero_rp }}</p>
                        </div>
                        @endif
                        @if($proceso->numero_contrato)
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Contrato N°</p>
                            <p class="font-bold" style="color:#7c3aed">{{ $proceso->numero_contrato }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Contratista --}}
                    @if($proceso->contratista_nombre)
                    <div class="border-t pt-4" style="border-color:#f1f5f9">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Contratista</p>
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div class="col-span-2">
                                <p class="text-xs text-gray-400 mb-0.5">Nombre</p>
                                <p class="font-medium text-gray-800">{{ $proceso->contratista_nombre }}</p>
                            </div>
                            @if($proceso->contratista_documento)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">{{ $proceso->contratista_tipo_documento ?? 'Documento' }}</p>
                                <p class="font-mono font-medium text-gray-800">{{ $proceso->contratista_documento }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- ── Timeline de etapas ── --}}
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                        <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            Timeline de etapas ({{ count($etapas) }})
                        </h2>
                        <span class="text-xs text-gray-400">Etapa actual: <strong class="text-gray-700">{{ optional($proceso->etapaActual)->nombre ?? 'N/D' }}</strong></span>
                    </div>

                    <div class="divide-y" style="divide-color:#f8fafc">
                        @foreach($etapas as $etapa)
                        @php
                            $pe = $peMap->get($etapa->id);
                            if ($etapa->orden < $currentOrden) {
                                $estado = 'completada';
                            } elseif ($etapa->id == $proceso->etapa_actual_id) {
                                $estado = 'activa';
                            } else {
                                $estado = 'pendiente';
                            }

                            $areaColors = [
                                'planeacion'         => ['dot'=>'#15803d','bg'=>'#f0fdf4','text'=>'#15803d'],
                                'hacienda'           => ['dot'=>'#ca8a04','bg'=>'#fefce8','text'=>'#a16207'],
                                'juridica'           => ['dot'=>'#c2410c','bg'=>'#fff7ed','text'=>'#c2410c'],
                                'secop'              => ['dot'=>'#7e22ce','bg'=>'#fdf4ff','text'=>'#7e22ce'],
                                'unidad_solicitante' => ['dot'=>'#2563eb','bg'=>'#eff6ff','text'=>'#1d4ed8'],
                            ];
                            $ac = $areaColors[$etapa->area_role] ?? ['dot'=>'#94a3b8','bg'=>'#f1f5f9','text'=>'#64748b'];

                            $checks = $pe ? $proceso->procesoEtapas->firstWhere('etapa_id', $etapa->id)?->checks ?? collect() : collect();
                            $archivosEtapa = $proceso->archivos->where('etapa_id', $etapa->id);
                        @endphp

                        <div class="flex gap-4 px-5 py-4 {{ $estado === 'pendiente' ? 'opacity-50' : '' }}">
                            {{-- Indicador visual --}}
                            <div class="flex flex-col items-center gap-1 shrink-0 mt-0.5">
                                @if($estado === 'completada')
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0" style="background:#dcfce7">
                                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                @elseif($estado === 'activa')
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 ring-2 ring-offset-1" style="background:#dbeafe;ring-color:#3b82f6">
                                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></div>
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0" style="background:#f1f5f9">
                                        <span class="text-xs text-gray-400 font-bold">{{ $etapa->orden }}</span>
                                    </div>
                                @endif
                                @if(!$loop->last)
                                    <div class="w-px flex-1 mt-1" style="background:{{ $estado === 'completada' ? '#bbf7d0' : '#e2e8f0' }};min-height:16px"></div>
                                @endif
                            </div>

                            {{-- Contenido de la etapa --}}
                            <div class="flex-1 pb-2">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">{{ $etapa->nombre }}</p>
                                        @if($etapa->descripcion)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $etapa->descripcion }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:{{ $ac['bg'] }};color:{{ $ac['text'] }}">
                                            {{ str_replace('_', ' ', ucfirst($etapa->area_role)) }}
                                        </span>
                                        @if($etapa->dias_estimados)
                                        <span class="text-xs text-gray-400">{{ $etapa->dias_estimados }}d</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Fechas --}}
                                @if($pe)
                                <div class="flex items-center gap-4 text-xs text-gray-400 mb-2">
                                    @if($pe->recibido_at)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        Recibido: {{ \Carbon\Carbon::parse($pe->recibido_at)->format('d/m/Y H:i') }}
                                    </span>
                                    @endif
                                    @if($pe->enviado_at)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        Enviado: {{ \Carbon\Carbon::parse($pe->enviado_at)->format('d/m/Y H:i') }}
                                    </span>
                                    @endif
                                </div>
                                @endif

                                {{-- Checks --}}
                                @if($checks->count() > 0)
                                <div class="grid grid-cols-2 gap-1 mb-2">
                                    @foreach($checks as $check)
                                    <div class="flex items-center gap-1.5 text-xs {{ $check->checked ? 'text-green-700' : 'text-gray-400' }}">
                                        @if($check->checked)
                                            <svg class="w-3 h-3 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        @else
                                            <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
                                        @endif
                                        <span class="truncate">{{ optional($check->item)->label ?? 'Ítem' }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                {{-- Archivos de esta etapa --}}
                                @if($archivosEtapa->count() > 0)
                                <div class="flex flex-wrap gap-1.5 mt-1">
                                    @foreach($archivosEtapa as $archivo)
                                    <a href="{{ route('workflow.files.download', $archivo->id) }}"
                                       class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium transition-colors hover:opacity-80"
                                       style="background:#eff6ff;color:#2563eb">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        {{ str_replace('_', ' ', $archivo->tipo_archivo ?? 'Documento') }}
                                    </a>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Columna derecha: Accesos rápidos + Auditoría --}}
            <div class="space-y-5">

                {{-- Ir a la bandeja del área actual --}}
                @php
                    $bandejaUrl = match($proceso->area_actual_role) {
                        'planeacion'         => route('planeacion.show', $proceso->id),
                        'hacienda'           => route('hacienda.show', $proceso->id),
                        'juridica'           => route('juridica.show', $proceso->id),
                        'secop'              => route('secop.show', $proceso->id),
                        'unidad_solicitante' => route('unidad.show', $proceso->id),
                        default              => null,
                    };
                    $areaLabels = [
                        'planeacion'         => 'Planeación',
                        'hacienda'           => 'Hacienda',
                        'juridica'           => 'Jurídica',
                        'secop'              => 'SECOP',
                        'unidad_solicitante' => 'Unidad solicitante',
                    ];
                    $areaLabel = $areaLabels[$proceso->area_actual_role] ?? $proceso->area_actual_role;

                    $canGoToBandeja = auth()->user()->hasRole('admin')
                        || auth()->user()->hasRole('planeacion')
                        || auth()->user()->hasRole($proceso->area_actual_role ?? '');
                @endphp

                @if($bandejaUrl && $proceso->estado === 'EN_CURSO')
                <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Área actual</p>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#f0fdf4">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">{{ $areaLabel }}</p>
                            <p class="text-xs text-gray-400">Bandeja activa</p>
                        </div>
                    </div>
                    @if($canGoToBandeja)
                    <a href="{{ $bandejaUrl }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold transition-all hover:opacity-90"
                       style="background:linear-gradient(135deg,#15803d,#14532d)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>
                        Ir a la bandeja
                    </a>
                    @else
                    <p class="text-xs text-center text-gray-400 py-2">No tienes acceso a esta bandeja</p>
                    @endif
                </div>
                @endif

                {{-- Progreso --}}
                @php
                    $totalEtapas = count($etapas);
                    $completadas = $etapas->where('orden', '<', $currentOrden)->count();
                    $progreso = $totalEtapas > 0 ? round(($completadas / $totalEtapas) * 100) : 0;
                @endphp
                <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Progreso del proceso</p>
                    <div class="flex items-end justify-between mb-2">
                        <span class="text-2xl font-bold text-gray-900">{{ $progreso }}%</span>
                        <span class="text-xs text-gray-400">{{ $completadas }} / {{ $totalEtapas }} etapas</span>
                    </div>
                    <div class="w-full h-2 rounded-full overflow-hidden" style="background:#f1f5f9">
                        <div class="h-full rounded-full transition-all" style="width:{{ $progreso }}%;background:linear-gradient(90deg,#22c55e,#15803d)"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Etapa actual: <span class="font-medium text-gray-600">{{ optional($proceso->etapaActual)->nombre ?? 'N/D' }}</span></p>
                </div>

                {{-- Archivos totales --}}
                @if($proceso->archivos->count() > 0)
                <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Documentos ({{ $proceso->archivos->count() }})</p>
                    <div class="space-y-2">
                        @foreach($proceso->archivos->take(5) as $archivo)
                        <a href="{{ route('workflow.files.download', $archivo->id) }}"
                           class="flex items-center gap-3 p-2.5 rounded-xl transition-colors hover:bg-gray-50" style="border:1px solid #f1f5f9">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:#dbeafe">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-700 truncate">{{ $archivo->nombre_original ?? 'Documento' }}</p>
                                <p class="text-xs text-gray-400">{{ str_replace('_', ' ', $archivo->tipo_archivo ?? '—') }}</p>
                            </div>
                        </a>
                        @endforeach
                        @if($proceso->archivos->count() > 5)
                        <p class="text-xs text-center text-gray-400">+{{ $proceso->archivos->count() - 5 }} más</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Auditoría --}}
                @if($auditoria->count() > 0)
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Historial de acciones</p>
                    </div>
                    <div class="divide-y max-h-96 overflow-y-auto" style="divide-color:#f8fafc">
                        @foreach($auditoria as $log)
                        <div class="px-4 py-3">
                            <div class="flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0 mt-0.5" style="background:#6b7280">
                                    {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-gray-700">{{ $log->user_name ?? 'Sistema' }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 break-words">{{ $log->descripcion ?? $log->accion }}</p>
                                    <p class="text-xs text-gray-300 mt-0.5">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
