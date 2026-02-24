<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-0.5">
                    <a href="{{ route('unidad.index') }}" class="hover:text-green-700 transition-colors">Unidad Solicitante</a>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">{{ $proceso->codigo }}</span>
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
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">📋 Documentos del Contratista</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Recibe los documentos físicos y sube la versión digital de cada uno</p>
                </div>
            </div>

            <div class="divide-y" style="border-color:#f1f5f9">
                @foreach($documentos as $i => $doc)
                <div class="p-4 hover:bg-gray-50/50 transition-colors">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-3">

                        {{-- Info del documento --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-2">
                                <span class="text-xs font-mono text-gray-400 mt-0.5 shrink-0">{{ $i + 1 }}.</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $doc->label }}</p>
                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                        @if($doc->requerido)
                                        <span class="text-xs px-1.5 py-0.5 rounded" style="background:#fef2f2;color:#dc2626">Requerido</span>
                                        @else
                                        <span class="text-xs px-1.5 py-0.5 rounded" style="background:#f0fdf4;color:#15803d">Opcional</span>
                                        @endif
                                        @if($doc->responsable_unidad)
                                        <span class="text-xs text-gray-400">{{ $doc->responsable_unidad }}</span>
                                        @endif
                                        @if($doc->notas)
                                        <span class="text-xs text-gray-400 italic">{{ Str::limit($doc->notas, 60) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex items-center gap-2 shrink-0">

                            {{-- Botón Recibido Físico --}}
                            @if($recibido)
                            <form method="POST" action="{{ route('unidad.recibido.fisico', [$proceso->id, $doc->check_id]) }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                    style="background:{{ $doc->recibido_fisico ? '#f0fdf4' : '#f8fafc' }};
                                           color:{{ $doc->recibido_fisico ? '#15803d' : '#6b7280' }};
                                           border:1px solid {{ $doc->recibido_fisico ? '#bbf7d0' : '#e5e7eb' }}"
                                    title="{{ $doc->recibido_fisico ? 'Clic para desmarcar' : 'Marcar como recibido en físico' }}">
                                    @if($doc->recibido_fisico)
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                        Recibido
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                        Recibir físico
                                    @endif
                                </button>
                            </form>
                            @else
                                <span class="text-xs text-gray-400 italic">Recibe el proceso primero</span>
                            @endif

                            {{-- Botón Subir Archivo --}}
                            @if($doc->recibido_fisico)
                                @if($doc->archivo_path)
                                    {{-- Ya tiene archivo subido --}}
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                                         style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        {{ Str::limit($doc->archivo_nombre, 20) }}
                                    </div>
                                    {{-- Botón para reemplazar --}}
                                    <form method="POST" action="{{ route('unidad.subir.archivo', [$proceso->id, $doc->check_id]) }}" enctype="multipart/form-data" class="inline-flex items-center">
                                        @csrf
                                        <label class="inline-flex items-center gap-1 px-2 py-1.5 rounded-lg text-xs cursor-pointer transition hover:bg-gray-100"
                                               style="color:#6b7280;border:1px solid #e5e7eb">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            Reemplazar
                                            <input type="file" name="archivo" class="hidden" onchange="this.form.submit()" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                        </label>
                                    </form>
                                @else
                                    {{-- Subir archivo --}}
                                    <form method="POST" action="{{ route('unidad.subir.archivo', [$proceso->id, $doc->check_id]) }}" enctype="multipart/form-data" class="inline-flex items-center">
                                        @csrf
                                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition hover:opacity-90"
                                               style="background:#2563eb;color:#fff">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            Subir digital
                                            <input type="file" name="archivo" class="hidden" onchange="this.form.submit()" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                        </label>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Info adicional cuando tiene archivo --}}
                    @if($doc->recibido_fisico && $doc->recibido_fisico_at)
                    <p class="text-xs text-gray-400 mt-2 ml-6">
                        📎 Recibido físicamente: {{ \Carbon\Carbon::parse($doc->recibido_fisico_at)->format('d/m/Y H:i') }}
                        @if($doc->archivo_subido_at)
                        &nbsp;·&nbsp; 📄 Digitalizado: {{ \Carbon\Carbon::parse($doc->archivo_subido_at)->format('d/m/Y H:i') }}
                        @endif
                    </p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Botón aprobar y enviar --}}
        @if($recibido)
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <form method="POST" action="{{ route('unidad.aprobar.etapa2', $proceso->id) }}">
                @csrf
                @php
                    $puedeEnviar = $todosCompletos;
                @endphp
                <button type="submit"
                    @if(!$puedeEnviar) disabled title="Debes recibir y digitalizar todos los documentos requeridos" @endif
                    @if($puedeEnviar) onclick="return confirm('¿Confirmar que todos los documentos del contratista están completos y enviar a la siguiente etapa?')" @endif
                    class="w-full px-4 py-3 rounded-xl text-sm font-semibold transition"
                    style="background:{{ $puedeEnviar ? '#15803d' : '#9ca3af' }};color:#fff;cursor:{{ $puedeEnviar ? 'pointer' : 'not-allowed' }};opacity:{{ $puedeEnviar ? '1' : '0.6' }}">
                    @if($puedeEnviar)
                        ✅ Documentos verificados — Enviar a siguiente etapa
                    @else
                        🔒 Completa todos los documentos requeridos para poder enviar
                    @endif
                </button>
            </form>
        </div>
        @endif

    </div>
</x-app-layout>
