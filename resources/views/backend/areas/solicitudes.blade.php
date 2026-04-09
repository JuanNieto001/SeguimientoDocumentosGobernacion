<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Mis Solicitudes de Documentos</h1>
                <p class="text-xs text-gray-400 mt-0.5">Área: {{ $areaName }}</p>
            </div>
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50 transition-all"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">

        {{-- KPIs --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border p-4 text-center" style="border-color:#e2e8f0">
                <p class="text-2xl font-bold text-gray-900">{{ $solicitudesPorProceso->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">Procesos asignados</p>
            </div>
            <div class="bg-white rounded-2xl border p-4 text-center" style="border-color:#e2e8f0">
                <p class="text-2xl font-bold" style="color:#c2410c">{{ $totalSolicitudes }}</p>
                <p class="text-xs text-gray-500 mt-1">Documentos pendientes</p>
            </div>
            <div class="bg-white rounded-2xl border p-4 text-center col-span-2 sm:col-span-1" style="border-color:#e2e8f0">
                <p class="text-2xl font-bold" style="color:#15803d">{{ $totalCompletadas }}</p>
                <p class="text-xs text-gray-500 mt-1">Documentos entregados</p>
            </div>
        </div>

        {{-- Lista de procesos --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📋 Procesos con documentos solicitados</h3>
                <p class="text-xs text-gray-400 mt-0.5">Solo se muestran procesos donde tu área tiene documentos asignados</p>
            </div>

            @if($solicitudesPorProceso->isEmpty())
            <div class="flex flex-col items-center gap-3 py-14">
                <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">No tienes solicitudes asignadas aún</p>
                    <p class="text-xs text-gray-400 mt-1">Cuando Planeación apruebe un proceso, aparecerá aquí con los documentos que debes subir.</p>
                </div>
            </div>
            @else
            <div class="divide-y" style="border-color:#f1f5f9">
                @foreach($solicitudesPorProceso as $procesoId => $solicitudesProceso)
                @php
                    $primera = $solicitudesProceso->first();
                    $totalDocs = $solicitudesProceso->count();
                    $subidosDocs = $solicitudesProceso->where('estado','subido')->count();
                    $pendientesDocs = $solicitudesProceso->where('estado','!=','subido')->where('puede_subir',1)->count();
                    $allDone = $subidosDocs === $totalDocs;
                @endphp
                <div class="p-5 hover:bg-gray-50/50 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4 min-w-0">
                            {{-- Indicador estado --}}
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg"
                                 style="background:{{ $allDone ? '#dcfce7' : ($pendientesDocs > 0 ? '#fff7ed' : '#f1f5f9') }}">
                                {{ $allDone ? '✅' : ($pendientesDocs > 0 ? '⏳' : '🔒') }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 font-mono">{{ $primera->proceso_numero ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $primera->proceso_descripcion }}</p>
                                <p class="text-xs text-gray-400 mt-1">Etapa: {{ $primera->etapa_nombre }}</p>

                                {{-- Documentos del proceso --}}
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach($solicitudesProceso as $doc)
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                          style="background:{{ $doc->estado === 'subido' ? '#dcfce7' : ($doc->puede_subir ? '#fef3c7' : '#f1f5f9') }};
                                                 color:{{ $doc->estado === 'subido' ? '#15803d' : ($doc->puede_subir ? '#92400e' : '#64748b') }}">
                                        @if($doc->estado === 'subido') ✅ @elseif($doc->puede_subir) ⏳ @else 🔒 @endif
                                        {{ $doc->nombre_documento }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-2 shrink-0">
                            {{-- Barra progreso --}}
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold" style="color:{{ $allDone ? '#15803d' : '#92400e' }}">
                                    {{ $subidosDocs }}/{{ $totalDocs }}
                                </span>
                                <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full"
                                         style="width:{{ $totalDocs > 0 ? round(($subidosDocs/$totalDocs)*100) : 0 }}%;
                                                background:{{ $allDone ? '#16a34a' : '#f59e0b' }}"></div>
                                </div>
                            </div>
                            <a href="{{ route('solicitudes.detalle', $procesoId) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition hover:opacity-90"
                               style="background:{{ $allDone ? '#f0fdf4' : '#2563eb' }};
                                      color:{{ $allDone ? '#15803d' : '#fff' }};
                                      border:{{ $allDone ? '1px solid #bbf7d0' : 'none' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($allDone)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    @endif
                                </svg>
                                {{ $allDone ? 'Ver' : 'Subir docs' }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
