{{-- Archivo: backend/resources/views/backend/areas/unidad-abogado.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="breadcrumb-row text-xs text-gray-400 mb-0.5 leading-none">
                    <a href="{{ route('unidad.index') }}" class="hover:text-green-700 transition-colors">Unidad Solicitante</a>
                    <span class="mx-1">/</span>
                    <span class="breadcrumb-code text-gray-600 font-medium">{{ $proceso->codigo }}</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Validación de Documentos del Contratista</h1>
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
                    {{ $proceso->etapaActual->nombre ?? 'N/D' }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-t pt-4" style="border-color:#f1f5f9">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Tipo</p>
                    <p class="font-medium text-gray-700">{{ $proceso->workflow->nombre ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Valor estimado</p>
                    <p class="font-medium text-gray-700">$ {{ number_format($proceso->valor_estimado ?? 0, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Contratista</p>
                    <p class="font-medium text-gray-700">{{ $proceso->contratista ?? 'Sin asignar' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Creado por</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->creador)->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- ── Documentos recibidos de Descentralización ─────────────────── --}}
        @if(isset($docsDescentralizacion) && $docsDescentralizacion->count() > 0)
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <details {{ $docsDescentralizacion->where('estado','subido')->count() < $docsDescentralizacion->count() ? 'open' : '' }}>
                <summary class="px-5 py-4 border-b flex items-center justify-between cursor-pointer select-none"
                         style="border-color:#f1f5f9;list-style:none">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-gray-700">📦 Documentos de Descentralización</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                              style="background:{{ $docsDescentralizacion->where('estado','subido')->count() === $docsDescentralizacion->count() ? '#dcfce7' : '#fef9c3' }};
                                     color:{{ $docsDescentralizacion->where('estado','subido')->count() === $docsDescentralizacion->count() ? '#15803d' : '#92400e' }}">
                            {{ $docsDescentralizacion->where('estado','subido')->count() }}/{{ $docsDescentralizacion->count() }} recibidos
                        </span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <div class="divide-y" style="divide-color:#f8fafc">
                    @foreach($docsDescentralizacion as $dDoc)
                    <div class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-sm"
                                 style="background:{{ $dDoc->estado === 'subido' ? '#dcfce7' : '#fef9c3' }}">
                                {{ $dDoc->estado === 'subido' ? '✅' : '⏳' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800">{{ $dDoc->nombre_documento }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $dDoc->area_responsable_nombre }}
                                    @if($dDoc->subido_at)
                                        · Recibido {{ \Carbon\Carbon::parse($dDoc->subido_at)->format('d/m/Y H:i') }}
                                    @endif
                                    @if($dDoc->subido_por_nombre)
                                        · por {{ $dDoc->subido_por_nombre }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 ml-4">
                            @if($dDoc->estado === 'subido' && $dDoc->archivo_id)
                                <a href="{{ route('workflow.files.download', $dDoc->archivo_id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition hover:opacity-90"
                                   style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Descargar
                                </a>
                                <a href="{{ route('workflow.files.download', ['archivo' => $dDoc->archivo_id, 'inline' => 1]) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition hover:opacity-90"
                                   style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Ver
                                </a>
                            @else
                                <span class="text-xs text-gray-400 italic">Pendiente de recibir</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </details>
        </div>
        @endif
        {{-- ── Fin documentos Descentralización ───────────────────────────── --}}

        {{-- Acciones: Recibir + Progreso --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Marcar recibido --}}
            <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <p class="text-sm font-semibold text-gray-700 mb-3">📥 Recepción del proceso</p>
                @if($recibido)
                    <div class="flex items-center gap-2 p-3 rounded-xl text-sm" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
                        <span>✅</span>
                        <span class="font-medium">Proceso recibido</span>
                    </div>
                    @if($procesoEtapaActual && $procesoEtapaActual->recibido_at)
                    <p class="text-xs text-gray-400 mt-2">{{ \Carbon\Carbon::parse($procesoEtapaActual->recibido_at)->format('d/m/Y H:i') }}</p>
                    @endif
                @else
                    <form method="POST" action="{{ route('unidad.recibir', $proceso->id) }}">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('¿Confirmar recepción del proceso?')"
                            class="w-full px-4 py-2.5 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
                            style="background:#14532d">
                            Marcar como recibido
                        </button>
                    </form>
                @endif
            </div>

            {{-- Progreso --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                <p class="text-sm font-semibold text-gray-700 mb-3">📊 Progreso de documentos</p>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-3 rounded-xl" style="background:#f8fafc">
                        <p class="text-2xl font-bold" style="color:#14532d">{{ $recibidosFisico }}/{{ $totalDocs }}</p>
                        <p class="text-xs text-gray-500 mt-1">Recibidos físicamente</p>
                    </div>
                    <div class="text-center p-3 rounded-xl" style="background:#f8fafc">
                        <p class="text-2xl font-bold" style="color:#2563eb">{{ $archivosSubidos }}/{{ $totalDocs }}</p>
                        <p class="text-xs text-gray-500 mt-1">Digitalizados</p>
                    </div>
                    <div class="text-center p-3 rounded-xl" style="background:{{ $todosCompletos ? '#f0fdf4' : '#fefce8' }}">
                        <p class="text-2xl font-bold" style="color:{{ $todosCompletos ? '#15803d' : '#ca8a04' }}">
                            {{ $todosCompletos ? '✅' : '⏳' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ $todosCompletos ? 'Listo para enviar' : 'Pendiente' }}</p>
                    </div>
                </div>
                @php
                    $porcentaje = $totalDocs > 0 ? round((($recibidosFisico + $archivosSubidos) / ($totalDocs * 2)) * 100) : 0;
                @endphp
                <div class="mt-3 w-full rounded-full h-2" style="background:#e5e7eb">
                    <div class="rounded-full h-2 transition-all duration-500" style="background:#15803d;width:{{ $porcentaje }}%"></div>
                </div>
                <p class="text-xs text-gray-400 text-right mt-1">{{ $porcentaje }}% completado</p>
            </div>
        </div>

        {{-- Lista de documentos del contratista --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">

            {{-- Cabecera de la tabla --}}
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9;background:#f8fafc">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-bold text-gray-800">📋 Documentos del Contratista</h3>
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold"
                          style="background:{{ $todosCompletos ? '#dcfce7' : '#fef9c3' }};color:{{ $todosCompletos ? '#15803d' : '#92400e' }}">
                        {{ $recibidosFisico }}/{{ $totalDocs }} físicos &nbsp;·&nbsp; {{ $archivosSubidos }}/{{ $totalDocs }} digitales
                    </span>
                </div>
                {{-- Cabeceras de columna --}}
                <div class="hidden lg:grid mt-3 gap-3 text-xs font-semibold uppercase tracking-wide text-gray-400"
                     style="grid-template-columns: 2rem 1fr 11rem 13rem">
                    <span>#</span>
                    <span>Documento</span>
                    <span class="text-center">Físico recibido</span>
                    <span class="text-center">Archivo digital</span>
                </div>
            </div>

            <div class="divide-y" style="divide-color:#f1f5f9">
                @foreach($documentos as $i => $doc)
                @php
                    $fisico = (bool)$doc->recibido_fisico;
                    $digital = !empty($doc->archivo_path);
                    $completo = $fisico && $digital;
                    $rowBg = $completo ? '#f0fdf4' : ($fisico ? '#eff6ff' : 'transparent');
                @endphp
                <div class="px-5 py-3 transition-colors hover:brightness-95"
                     style="background:{{ $rowBg }}">

                    <div class="grid items-center gap-3"
                         style="grid-template-columns: 2rem 1fr">

                        {{-- Número / estado --}}
                        <div class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold shrink-0"
                             style="background:{{ $completo ? '#15803d' : ($fisico ? '#2563eb' : '#e5e7eb') }};
                                    color:{{ $completo || $fisico ? '#fff' : '#6b7280' }}">
                            {{ $completo ? '✓' : ($i + 1) }}
                        </div>

                        {{-- Nombre + badges + columnas de acción --}}
                        <div class="flex flex-col lg:grid lg:items-center lg:gap-3"
                             style="grid-template-columns:1fr 11rem 13rem">

                            {{-- Nombre del documento --}}
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 leading-snug">
                                    {{ preg_replace('/^(\\\\u[0-9a-fA-F]{4}|\x{2610}|☐|\s)+/u', '', $doc->label) }}
                                </p>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    @if($doc->requerido)
                                    <span class="text-xs px-1.5 py-0.5 rounded font-medium" style="background:#fef2f2;color:#dc2626">Requerido</span>
                                    @else
                                    <span class="text-xs px-1.5 py-0.5 rounded font-medium" style="background:#f0fdf4;color:#15803d">Opcional</span>
                                    @endif
                                    @if($doc->notas)
                                    <span class="text-xs text-gray-400 italic">{{ Str::limit($doc->notas, 50) }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Columna FÍSICO --}}
                            <div class="flex justify-start lg:justify-center mt-2 lg:mt-0">
                                @if($recibido)
                                <form method="POST" action="{{ route('unidad.recibido.fisico', [$proceso->id, $doc->check_id]) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all w-36 justify-center"
                                        style="background:{{ $fisico ? '#dcfce7' : '#14532d' }};
                                               color:{{ $fisico ? '#15803d' : '#fff' }};
                                               border:1.5px solid {{ $fisico ? '#86efac' : '#14532d' }}"
                                        title="{{ $fisico ? 'Clic para desmarcar' : 'Marcar como recibido físicamente' }}">
                                        @if($fisico)
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                            Recibido ✓
                                        @else
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Marcar recibido
                                        @endif
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-gray-400 italic">—</span>
                                @endif
                            </div>

                            {{-- Columna DIGITAL --}}
                            <div class="flex justify-start lg:justify-center mt-2 lg:mt-0">
                                @if(!$fisico)
                                    {{-- Sin recibir físico aún --}}
                                    <span class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs w-44 justify-center"
                                          style="background:#f9fafb;color:#d1d5db;border:1.5px dashed #e5e7eb">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Recibe físico antes
                                    </span>
                                @elseif($digital)
                                    {{-- Archivo ya subido --}}
                                    <div class="flex items-center gap-1.5">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-2 rounded-xl text-xs font-semibold max-w-[8rem]"
                                             style="background:#eff6ff;color:#2563eb;border:1.5px solid #bfdbfe"
                                             title="{{ $doc->archivo_nombre }}">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="truncate">{{ Str::limit($doc->archivo_nombre, 14) }}</span>
                                        </div>
                                        <form method="POST" action="{{ route('unidad.subir.archivo', [$proceso->id, $doc->check_id]) }}" enctype="multipart/form-data">
                                            @csrf
                                            <label class="inline-flex items-center justify-center w-8 h-8 rounded-xl cursor-pointer transition hover:opacity-80"
                                                   style="background:#f1f5f9;color:#6b7280;border:1.5px solid #e2e8f0" title="Reemplazar archivo">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                <input type="file" name="archivo" class="hidden" onchange="this.form.submit()" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                            </label>
                                        </form>
                                    </div>
                                @else
                                    {{-- Pendiente de subir --}}
                                    <form method="POST" action="{{ route('unidad.subir.archivo', [$proceso->id, $doc->check_id]) }}" enctype="multipart/form-data">
                                        @csrf
                                        <label class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold cursor-pointer transition-all w-44 justify-center hover:opacity-90"
                                               style="background:#2563eb;color:#fff;border:1.5px solid #1d4ed8">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            Subir digital
                                            <input type="file" name="archivo" class="hidden" onchange="this.form.submit()" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                        </label>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Timestamps (solo si tiene algo) --}}
                    @if($fisico && $doc->recibido_fisico_at)
                    <div class="flex items-center gap-3 mt-1.5 ml-10 flex-wrap">
                        <span class="text-xs text-gray-400">
                            📎 Físico: {{ \Carbon\Carbon::parse($doc->recibido_fisico_at)->format('d/m/Y H:i') }}
                        </span>
                        @if($doc->archivo_subido_at)
                        <span class="text-xs text-gray-400">
                            📄 Digital: {{ \Carbon\Carbon::parse($doc->archivo_subido_at)->format('d/m/Y H:i') }}
                        </span>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Botón enviar a siguiente etapa --}}
        @if($recibido)
        <div class="rounded-2xl border p-5" style="border-color:{{ $todosCompletos ? '#86efac' : '#e2e8f0' }};background:{{ $todosCompletos ? '#f0fdf4' : '#fff' }}">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold" style="color:{{ $todosCompletos ? '#15803d' : '#374151' }}">
                        {{ $todosCompletos ? '✅ Todos los documentos requeridos están completos' : '🔒 Completa todos los documentos requeridos para poder enviar' }}
                    </p>
                    @if(!$todosCompletos)
                    @php
                        $faltanFisico  = $documentos->where('requerido', true)->where('recibido_fisico', false)->count();
                        $faltanDigital = $documentos->where('requerido', true)->filter(fn($d)=>empty($d->archivo_path))->count();
                    @endphp
                    <p class="text-xs text-gray-400 mt-1">
                        @if($faltanFisico > 0) {{ $faltanFisico }} pendientes de recibir físicamente &nbsp;·&nbsp; @endif
                        @if($faltanDigital > 0) {{ $faltanDigital }} sin digitalizar @endif
                    </p>
                    @endif
                </div>
                <form method="POST" action="{{ route('unidad.aprobar.etapa2', $proceso->id) }}" class="shrink-0">
                    @csrf
                    <button type="submit"
                        @if(!$todosCompletos) disabled @endif
                        @if($todosCompletos) onclick="return confirm('¿Confirmar que todos los documentos del contratista están completos y enviar a la siguiente etapa?')" @endif
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold transition-all"
                        style="background:{{ $todosCompletos ? '#15803d' : '#d1d5db' }};
                               color:{{ $todosCompletos ? '#fff' : '#9ca3af' }};
                               cursor:{{ $todosCompletos ? 'pointer' : 'not-allowed' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Enviar a siguiente etapa
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>

