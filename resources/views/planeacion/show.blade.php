<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-0.5">
                    <a href="{{ route('planeacion.index') }}" class="hover:text-green-700 transition-colors">Planeación</a>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">{{ $proceso->codigo }}</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Detalle del proceso</h1>
            </div>
            <a href="{{ route('planeacion.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50 transition-all"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">

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
        @if($errors->any())
        <div class="p-3.5 rounded-xl text-sm" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        {{-- Info del proceso --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ $proceso->codigo }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $proceso->objeto }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold shrink-0"
                      style="background:{{ in_array($proceso->estado,['completado','cerrado'])?'#dcfce7':($proceso->estado=='rechazado'?'#fee2e2':'#dbeafe') }};
                             color:{{ in_array($proceso->estado,['completado','cerrado'])?'#15803d':($proceso->estado=='rechazado'?'#dc2626':'#2563eb') }}">
                    {{ ucfirst(str_replace('_',' ',$proceso->estado)) }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-t pt-4" style="border-color:#f1f5f9">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Tipo de contratación</p>
                    <p class="font-medium text-gray-700">{{ $proceso->flujo_id ? optional(DB::table('flujos')->where('id', $proceso->flujo_id)->first())->nombre : ($proceso->workflow->nombre ?? 'N/D') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Etapa actual</p>
                    <p class="font-medium text-gray-700">{{ $proceso->etapaActual->nombre ?? 'N/D' }}</p>
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
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📄 Documento de Estudios Previos</h3>
            </div>
            <div class="p-5">
                @if($documentoEstudiosPrevios)
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">{{ $documentoEstudiosPrevios->nombre_original }}</p>
                                <p class="text-xs text-gray-600 mt-1">
                                    Subido el {{ \Carbon\Carbon::parse($documentoEstudiosPrevios->uploaded_at)->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="window.dispatchEvent(new CustomEvent('abrir-preview', { detail: {{ $documentoEstudiosPrevios->id }} }))"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Ver
                                </button>
                                <a href="{{ route('workflow.files.download', $documentoEstudiosPrevios->id) }}" 
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-all"
                                   target="_blank">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Descargar
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-900">
                        <p class="font-semibold">⚠️ No se encontró el documento de Estudios Previos</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recepción --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Recepción del documento</h3>
            <form method="POST" action="{{ route('workflow.recibir', $proceso->id) }}">
                @csrf
                <input type="hidden" name="area_role" value="planeacion">
                @php $recibido = $procesoEtapaActual && $procesoEtapaActual->recibido; @endphp
                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                        style="background:{{ $recibido?'#f0fdf4':'#14532d' }};color:{{ $recibido?'#15803d':'#fff' }};border:{{ $recibido?'1px solid #bbf7d0':'none' }}"
                        {{ $recibido?'disabled':'' }}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($recibido)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            @endif
                        </svg>
                        {{ $recibido?'Documento recibido ✓':'Marcar como recibido' }}
                    </button>
                    @if($recibido && $procesoEtapaActual->recibido_at)
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($procesoEtapaActual->recibido_at)->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
            </form>
        </div>

        {{-- Decisión de Planeación --}}
        @php
            // Verificar si ya se aprobó y se enviaron solicitudes de documentos
            $solicitudesDocumentos = DB::table('proceso_documentos_solicitados')
                ->where('proceso_id', $proceso->id)
                ->orderBy('id')
                ->get();
            $yaSeAprobaron = $solicitudesDocumentos->count() > 0;
            $totalSolicitudes = $solicitudesDocumentos->count();
            $completadas = $solicitudesDocumentos->where('estado', 'subido')->count();
            $pendientes = $totalSolicitudes - $completadas;
            $todasCompletas = $pendientes === 0 && $totalSolicitudes > 0;
        @endphp

        @if(!$yaSeAprobaron)
        {{-- Solo mostrar si NO se ha aprobado aún --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Decisión de Planeación</h3>
            </div>
            <div class="p-5 grid sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl space-y-3" style="background:#f0fdf4;border:1px solid #bbf7d0">
                    <p class="text-sm font-semibold" style="color:#15803d">✅ Aprobar proceso</p>
                    <form method="POST" action="{{ route('planeacion.aprobar', $proceso->id) }}">
                        @csrf
                        <div class="space-y-3">
                            <textarea name="observaciones" rows="2"
                                class="w-full border rounded-xl px-3 py-2 text-sm bg-white resize-none focus:outline-none focus:ring-2 focus:ring-green-500"
                                style="border-color:#d1fae5"
                                placeholder="Observaciones (opcional)..."></textarea>
                            @php
                                $aprobBg     = $recibido ? '#15803d' : '#9ca3af';
                                $aprobCursor = $recibido ? 'pointer' : 'not-allowed';
                            @endphp
                            <button type="submit"
                                @if(!$recibido) disabled title="Debes marcar el documento como recibido antes de aprobar" @endif
                                @if($recibido) onclick="return confirm('{{ $proceso->flujo_id ? '¿Confirmar aprobación y enviar a la siguiente etapa?' : '¿Confirmar aprobación y solicitar documentos a las áreas?' }}')" @else onclick="return false" @endif
                                class="w-full px-4 py-2 rounded-xl text-sm font-semibold transition"
                                style="background:{{ $aprobBg }};color:#fff;cursor:{{ $aprobCursor }};opacity:{{ $recibido ? '1' : '0.6' }}">
                                @if($recibido)
                                    {{ $proceso->flujo_id ? 'Aprobar y enviar a siguiente etapa' : 'Aprobar y solicitar documentos a áreas' }}
                                @else
                                    🔒 Primero marca el documento como recibido
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
                <div class="p-4 rounded-xl space-y-3" style="background:#fef2f2;border:1px solid #fecaca">
                    <p class="text-sm font-semibold" style="color:#dc2626">❌ Rechazar proceso</p>
                    <form method="POST" action="{{ route('planeacion.rechazar', $proceso->id) }}">
                        @csrf
                        <div class="space-y-3">
                            <textarea name="motivo_rechazo" rows="2"
                                class="w-full border rounded-xl px-3 py-2 text-sm bg-white resize-none focus:outline-none focus:ring-2 focus:ring-red-500"
                                style="border-color:#fecaca"
                                placeholder="Motivo del rechazo (obligatorio)..." required
                                @if(!$recibido) disabled @endif></textarea>
                            <button type="submit"
                                @if(!$recibido) disabled title="Debes marcar el documento como recibido antes de rechazar" @endif
                                onclick="@if($recibido) return confirm('¿Rechazar y devolver el proceso a la etapa anterior?') @else return false @endif"
                                class="w-full px-4 py-2 rounded-xl text-white text-sm font-semibold transition"
                                style="background:{{ $recibido ? '#dc2626' : '#9ca3af' }};
                                       cursor:{{ $recibido ? 'pointer' : 'not-allowed' }};
                                       opacity:{{ $recibido ? '1' : '0.6' }}">
                                Rechazar y devolver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @else
        {{-- Ya aprobado: mostrar estado --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
                <span>✅</span>
                <span>Proceso aprobado — Documentos solicitados a las áreas. Esperando recepción completa para enviar al abogado.</span>
            </div>
        </div>
        @endif

        {{-- ========== SOLICITUDES DE DOCUMENTOS PARALELOS (Etapa 1 - Descentralización) ========== --}}
        @if($yaSeAprobaron && $totalSolicitudes > 0)
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📋 Documentos Solicitados a Áreas (Envío Simultáneo)</h3>
                <div class="flex items-center gap-2">
                    <span class="text-xs px-2 py-1 rounded-full font-semibold"
                          style="background:{{ $todasCompletas ? '#dcfce7' : '#fef3c7' }}; color:{{ $todasCompletas ? '#15803d' : '#92400e' }}">
                        {{ $completadas }}/{{ $totalSolicitudes }} completados
                    </span>
                </div>
            </div>
            <div class="p-5">
                {{-- Barra de progreso --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span>Progreso de documentos</span>
                        <span>{{ $totalSolicitudes > 0 ? round(($completadas / $totalSolicitudes) * 100) : 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all duration-500"
                             style="width: {{ $totalSolicitudes > 0 ? ($completadas / $totalSolicitudes) * 100 : 0 }}%; background: {{ $todasCompletas ? '#16a34a' : '#f59e0b' }}">
                        </div>
                    </div>
                </div>

                {{-- Lista de documentos --}}
                <div class="space-y-3">
                    @foreach($solicitudesDocumentos as $sol)
                    @php
                        $archivo = $sol->archivo_id ? DB::table('proceso_etapa_archivos')->where('id', $sol->archivo_id)->first() : null;
                        $subidoPor = $sol->subido_por ? DB::table('users')->where('id', $sol->subido_por)->first() : null;
                    @endphp
                    <div class="flex items-start gap-3 p-3 rounded-xl"
                         style="background:{{ $sol->estado === 'subido' ? '#f0fdf4' : ($sol->puede_subir ? '#fffbeb' : '#f8fafc') }};
                                border:1px solid {{ $sol->estado === 'subido' ? '#bbf7d0' : ($sol->puede_subir ? '#fde68a' : '#e2e8f0') }}">
                        
                        {{-- Icono de estado --}}
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-lg"
                             style="background:{{ $sol->estado === 'subido' ? '#dcfce7' : ($sol->puede_subir ? '#fef3c7' : '#f1f5f9') }}">
                            @if($sol->estado === 'subido') ✅
                            @elseif($sol->puede_subir) ⏳
                            @else 🔒
                            @endif
                        </div>
                        
                        {{-- Info del documento --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-800">{{ $sol->nombre_documento }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium shrink-0 ml-2"
                                      style="background:{{ $sol->estado === 'subido' ? '#dcfce7' : '#fef3c7' }}; color:{{ $sol->estado === 'subido' ? '#15803d' : '#92400e' }}">
                                    {{ $sol->estado === 'subido' ? 'Recibido' : ($sol->puede_subir ? 'Pendiente' : 'Bloqueado') }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Área: <strong>{{ $sol->area_responsable_nombre }}</strong>
                            </p>
                            
                            @if($sol->estado === 'subido' && $archivo)
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-xs text-green-700">
                                        📎 {{ $archivo->nombre_original }}
                                        @if($subidoPor) · Por: {{ $subidoPor->name }} @endif
                                        @if($sol->subido_at) · {{ \Carbon\Carbon::parse($sol->subido_at)->format('d/m/Y H:i') }} @endif
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <button onclick="window.dispatchEvent(new CustomEvent('abrir-preview', { detail: {{ $archivo->id }} }))"
                                                class="text-xs font-medium text-green-600 hover:text-green-800 transition-colors">
                                            Ver
                                        </button>
                                        <a href="{{ route('workflow.files.download', $archivo->id) }}"
                                           class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                            Descargar
                                        </a>
                                    </div>
                                </div>
                            @elseif(!$sol->puede_subir)
                                <p class="text-xs text-gray-400 mt-1 italic">
                                    🔒 Requiere que se suba primero: Compatibilidad del Gasto
                                </p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Mensaje de estado --}}
                @if($todasCompletas)
                <div class="mt-4 p-3 rounded-xl text-sm font-medium" style="background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0">
                    ✅ Todos los documentos han sido recibidos. Puedes enviar el expediente al abogado de la unidad solicitante.
                </div>
                @else
                <div class="mt-4 p-3 rounded-xl text-sm font-medium" style="background:#fffbeb; color:#92400e; border:1px solid #fde68a">
                    ⏳ Faltan {{ $pendientes }} documento(s) por recibir. No podrás avanzar hasta que todas las áreas envíen sus documentos.
                </div>
                @endif

                {{-- Botón Enviar — se oculta una vez enviado --}}
                @if($procesoEtapaActual && $procesoEtapaActual->enviado)
                <div class="mt-4 p-3 rounded-xl flex items-center gap-3 text-sm font-semibold"
                     style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
                    <span>✅</span>
                    <span>Expediente enviado al Abogado de la Unidad Solicitante
                        @if($procesoEtapaActual->enviado_at)
                            — {{ \Carbon\Carbon::parse($procesoEtapaActual->enviado_at)->format('d/m/Y H:i') }}
                        @endif
                    </span>
                </div>
                @else
                <form method="POST" action="{{ route('workflow.enviar', $proceso->id) }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="area_role" value="planeacion">
                    <button type="submit"
                        @if($todasCompletas)
                            onclick="return confirm('¿Confirmar envío del expediente completo al abogado de la unidad solicitante?')"
                        @else
                            disabled
                            title="Debes esperar a que todas las áreas envíen sus documentos"
                        @endif
                        class="w-full px-4 py-3 rounded-xl text-white text-sm font-bold transition"
                        style="background:{{ $todasCompletas ? '#14532d' : '#9ca3af' }};
                               cursor:{{ $todasCompletas ? 'pointer' : 'not-allowed' }};
                               opacity:{{ $todasCompletas ? '1' : '0.6' }}">
                        @if($todasCompletas)
                            📤 Enviar a la siguiente etapa (Abogado Unidad Solicitante)
                        @else
                            🔒 Enviar a la siguiente etapa — Esperando documentos ({{ $completadas }}/{{ $totalSolicitudes }})
                        @endif
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif
        {{-- ========== FIN SOLICITUDES DE DOCUMENTOS PARALELOS ========== --}}

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
                            <p class="text-xs text-gray-400">{{ str_replace('_',' ',$archivo->tipo_archivo) }}@if($archivo->version > 1) · v{{ $archivo->version }}@endif</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="window.dispatchEvent(new CustomEvent('abrir-preview', { detail: {{ $archivo->id }} }))"
                                    class="text-xs font-medium text-green-600 hover:text-green-800 transition-colors">Ver</button>
                            <a href="{{ route('workflow.files.download', $archivo->id) }}"
                               class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">Descargar</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </details>
        </div>
        @endif

    </div>
</x-app-layout>
