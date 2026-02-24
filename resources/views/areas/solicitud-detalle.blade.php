<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-0.5">
                    <a href="{{ route('solicitudes.index') }}" class="hover:text-green-700 transition-colors">Mis Solicitudes</a>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">{{ $proceso->codigo }}</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Subir Documentos — {{ $areaName }}</h1>
            </div>
            <a href="{{ route('solicitudes.index') }}"
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
                      style="background:#dbeafe;color:#2563eb">
                    Etapa actual: {{ $proceso->area_actual_role }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm border-t pt-4" style="border-color:#f1f5f9">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Solicitante</p>
                    <p class="font-medium text-gray-700">{{ optional($creador)->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Valor estimado</p>
                    <p class="font-medium text-gray-700">$ {{ number_format($proceso->valor_estimado ?? 0, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Tu área</p>
                    <p class="font-medium text-gray-700">{{ $areaName }}</p>
                </div>
            </div>
        </div>

        {{-- Estudios Previos --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <p class="text-sm font-semibold text-gray-700 mb-3">📄 Estudios Previos del proceso</p>
            @if($estudiosPrevios)
            <div class="flex items-center justify-between p-3 rounded-xl"
                 style="background:#f0fdf4;border:1px solid #bbf7d0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:#dcfce7">
                        <svg class="w-4 h-4" style="color:#15803d" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate" style="color:#15803d">{{ $estudiosPrevios->nombre_original }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ round(($estudiosPrevios->tamanio ?? 0) / 1024) }} KB ·
                            Subido {{ \Carbon\Carbon::parse($estudiosPrevios->uploaded_at ?? $estudiosPrevios->created_at)->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-3 shrink-0">
                    <a href="{{ route('workflow.files.download', $estudiosPrevios->id) }}?inline=1"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition hover:opacity-90"
                       style="background:#dbeafe;color:#1d4ed8">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Ver
                    </a>
                    <a href="{{ route('workflow.files.download', $estudiosPrevios->id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition hover:opacity-90"
                       style="background:#15803d;color:#fff">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Descargar
                    </a>
                </div>
            </div>
            @else
            <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#fefce8;border:1px solid #fde68a">
                <svg class="w-4 h-4 shrink-0" style="color:#ca8a04" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm text-yellow-700">No se han adjuntado estudios previos a este proceso todavía.</p>
            </div>
            @endif
        </div>

        {{-- Para Presupuesto: estado de Compatibilidad del Gasto (Regalías) --}}
        @if(isset($compatibilidadDoc) && $compatibilidadDoc)
        @php $compSubida = $compatibilidadDoc->estado === 'subido'; @endphp
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <p class="text-sm font-semibold text-gray-700 mb-3">📊 Compatibilidad del Gasto — Regalías e Inversiones</p>
            @if($compSubida)
            <div class="flex items-center justify-between p-3 rounded-xl"
                 style="background:#f0fdf4;border:1px solid #bbf7d0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:#dcfce7">
                        <svg class="w-4 h-4" style="color:#15803d" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate" style="color:#15803d">{{ $compatibilidadDoc->archivo_nombre ?? $compatibilidadDoc->nombre_documento }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Subido {{ $compatibilidadDoc->subido_at ? \Carbon\Carbon::parse($compatibilidadDoc->subido_at)->format('d/m/Y H:i') : '' }}
                        </p>
                    </div>
                </div>
                @if($compatibilidadDoc->archivo_id)
                <div class="flex items-center gap-2 ml-3 shrink-0">
                    <a href="{{ route('workflow.files.download', $compatibilidadDoc->archivo_id) }}?inline=1"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition hover:opacity-90"
                       style="background:#dbeafe;color:#1d4ed8">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Ver
                    </a>
                    <a href="{{ route('workflow.files.download', $compatibilidadDoc->archivo_id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition hover:opacity-90"
                       style="background:#15803d;color:#fff">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Descargar
                    </a>
                </div>
                @endif
            </div>
            @else
            <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#fefce8;border:1px solid #fde68a">
                <svg class="w-4 h-4 shrink-0" style="color:#ca8a04" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm text-yellow-700">⏳ Pendiente — Regalías aún no ha entregado la Compatibilidad del Gasto. El CDP estará bloqueado hasta que se reciba.</p>
            </div>
            @endif
        </div>
        @endif

        {{-- Documentos a subir --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">📋 Documentos que debes entregar</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Sube cada documento solicitado para este proceso</p>
                </div>
                @php $subidos = $solicitudes->where('estado','subido')->count(); $total = $solicitudes->count(); @endphp
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold"
                      style="background:{{ $subidos===$total && $total>0 ? '#dcfce7' : '#fef3c7' }};color:{{ $subidos===$total && $total>0 ? '#15803d' : '#92400e' }}">
                    {{ $subidos }}/{{ $total }} entregados
                </span>
            </div>

            {{-- Lista de documentos --}}
            @if($solicitudes->isEmpty())
            <div class="flex flex-col items-center gap-2 py-10">
                <p class="text-sm text-gray-400">No hay documentos solicitados para tu área en este proceso.</p>
            </div>
            @else
            <div class="divide-y" style="border-color:#f1f5f9">
                @foreach($solicitudes as $sol)
                <div class="p-4 hover:bg-gray-50/50 transition-colors">
                    <div class="flex flex-col lg:flex-row lg:items-start gap-4">

                        {{-- Icono estado --}}
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-base mt-0.5"
                             style="background:{{ $sol->estado === 'subido' ? '#dcfce7' : ($sol->puede_subir ? '#fff7ed' : '#f1f5f9') }}">
                            @if($sol->estado === 'subido') ✅
                            @elseif($sol->puede_subir) ⏳
                            @else 🔒
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between flex-wrap gap-2 mb-1">
                                <p class="text-sm font-semibold text-gray-800">{{ $sol->nombre_documento }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                      style="background:{{ $sol->estado === 'subido' ? '#dcfce7' : ($sol->puede_subir ? '#fef3c7' : '#f1f5f9') }};
                                             color:{{ $sol->estado === 'subido' ? '#15803d' : ($sol->puede_subir ? '#92400e' : '#64748b') }}">
                                    {{ $sol->estado === 'subido' ? 'Entregado' : ($sol->puede_subir ? 'Pendiente' : 'Bloqueado') }}
                                </span>
                            </div>

                            @if($sol->observaciones)
                            <p class="text-xs text-amber-700 mb-2 px-2 py-1 rounded-lg" style="background:#fffbeb">
                                📝 {{ $sol->observaciones }}
                            </p>
                            @endif

                            @if($sol->estado === 'subido')
                                <div class="flex items-center gap-2 mt-2 p-2 rounded-lg" style="background:#f0fdf4;border:1px solid #bbf7d0">
                                    <svg class="w-4 h-4 shrink-0" style="color:#15803d" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-xs font-medium truncate" style="color:#15803d">
                                        {{ $sol->archivo_nombre ?? 'Documento subido' }}
                                    </span>
                                    @if($sol->subido_at)
                                    <span class="text-xs text-gray-400 ml-auto shrink-0">
                                        {{ \Carbon\Carbon::parse($sol->subido_at)->format('d/m/Y H:i') }}
                                    </span>
                                    @endif
                                </div>

                            @elseif($sol->puede_subir && !($sol->tipo_documento === 'cdp' && isset($compatibilidadDoc) && $compatibilidadDoc && $compatibilidadDoc->estado !== 'subido'))
                                <form method="POST"
                                      action="{{ route('workflow.files.store', $proceso->id) }}"
                                      enctype="multipart/form-data"
                                      class="mt-2">
                                    @csrf
                                    <input type="hidden" name="tipo_archivo" value="{{ $sol->tipo_documento }}">
                                    <input type="hidden" name="solicitud_id" value="{{ $sol->id }}">
                                    <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#f8fafc;border:1px dashed #cbd5e1">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="file"
                                                   id="file_{{ $sol->id }}"
                                                   name="archivo"
                                                   required
                                                   class="hidden"
                                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                                   onchange="document.getElementById('fname_{{ $sol->id }}').textContent = this.files[0]?.name ?? 'Ningún archivo'">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                                                      style="background:#2563eb;color:#fff">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                    </svg>
                                                    Elegir archivo
                                                </span>
                                                <span id="fname_{{ $sol->id }}" class="text-xs text-gray-500 truncate">Ningún archivo seleccionado</span>
                                            </div>
                                        </label>
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold shrink-0 transition hover:opacity-90"
                                                style="background:#15803d;color:#fff">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Subir
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1 ml-1">PDF, Word, Excel, imagen · Máx. 10 MB</p>
                                </form>

                            @elseif($sol->tipo_documento === 'cdp' && isset($compatibilidadDoc) && $compatibilidadDoc && $compatibilidadDoc->estado !== 'subido')
                                <p class="text-xs text-amber-700 mt-1 italic">
                                    🔒 Bloqueado — debes esperar que Regalías suba la Compatibilidad del Gasto.
                                </p>
                            @else
                                <p class="text-xs text-gray-400 mt-1 italic">
                                    🔒 Bloqueado — requiere que se suba un documento previo.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Archivos ya subidos por este usuario --}}
        @if($archivosSubidos->isNotEmpty())
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📎 Archivos que has subido</h3>
            </div>
            <div class="divide-y" style="border-color:#f1f5f9">
                @foreach($archivosSubidos as $archivo)
                <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:#eff6ff">
                            <svg class="w-4 h-4" style="color:#2563eb" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $archivo->nombre_original }}</p>
                            <p class="text-xs text-gray-400">{{ $archivo->tipo_archivo }} · {{ $archivo->uploaded_at ? \Carbon\Carbon::parse($archivo->uploaded_at)->format('d/m/Y H:i') : '' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('workflow.files.download', $archivo->id) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium shrink-0 transition hover:bg-gray-100"
                       style="color:#374151;border:1px solid #e5e7eb">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Descargar
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
