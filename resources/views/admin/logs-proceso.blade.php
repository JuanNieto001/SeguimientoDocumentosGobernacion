<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-0.5">
                    <a href="{{ route('admin.logs') }}" class="hover:text-green-700 transition-colors">Logs</a>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">{{ $proceso->codigo }}</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Auditoría del proceso</h1>
            </div>
            <a href="{{ route('admin.logs') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50 transition-all"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">

        {{-- Info del proceso --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ $proceso->codigo }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $proceso->objeto }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold shrink-0"
                      style="background:{{ in_array($proceso->estado,['completado','cerrado','FINALIZADO'])?'#dcfce7':($proceso->estado=='rechazado'?'#fee2e2':'#dbeafe') }};
                             color:{{ in_array($proceso->estado,['completado','cerrado','FINALIZADO'])?'#15803d':($proceso->estado=='rechazado'?'#dc2626':'#2563eb') }}">
                    {{ $proceso->estado }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-sm border-t pt-4" style="border-color:#f1f5f9">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Workflow</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->workflow)->nombre ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Etapa actual</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->etapaActual)->nombre ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Área actual</p>
                    <p class="font-medium text-gray-700 capitalize">{{ str_replace('_',' ',$proceso->area_actual_role) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Creado por</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->creador)->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Fecha creación</p>
                    <p class="font-medium text-gray-700">{{ $proceso->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Recorrido por etapas --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Recorrido por etapas</h3>
            </div>
            <div class="p-4 space-y-3">
                @foreach($proceso->procesoEtapas as $pe)
                @php
                    $esActual = $pe->etapa_id == $proceso->etapa_actual_id;
                    $enviada  = (bool) $pe->enviado;
                @endphp
                <div class="rounded-xl border p-4"
                     style="border-color:{{ $esActual ? '#93c5fd' : ($enviada ? '#bbf7d0' : '#e2e8f0') }};
                            background:{{ $esActual ? '#eff6ff' : ($enviada ? '#f0fdf4' : '#f8fafc') }}">

                    {{-- Encabezado --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $esActual ? 'bg-blue-500 animate-pulse' : ($enviada ? 'bg-green-500' : 'bg-gray-300') }}"></span>
                            <span class="text-sm font-bold {{ $esActual ? 'text-blue-700' : ($enviada ? 'text-green-700' : 'text-gray-600') }}">
                                {{ optional($pe->etapa)->nombre ?? 'Etapa #'.$pe->etapa_id }}
                            </span>
                            @if($esActual)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:#dbeafe;color:#2563eb">ACTUAL</span>
                            @elseif($enviada)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:#dcfce7;color:#15803d">COMPLETADA</span>
                            @endif
                        </div>
                        <span class="text-xs text-gray-400 capitalize">{{ str_replace('_',' ',optional($pe->etapa)->area_role) }}</span>
                    </div>

                    {{-- Recibido / Enviado --}}
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-lg bg-white border" style="border-color:#e2e8f0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-1.5">Recibido</p>
                            @if($pe->recibido)
                            <p class="font-semibold text-gray-700">{{ optional($pe->recibidoPor)->name ?? 'Usuario #'.$pe->recibido_por }}</p>
                            <p class="text-gray-400 mt-0.5">{{ $pe->recibido_at ? \Carbon\Carbon::parse($pe->recibido_at)->format('d/m/Y H:i:s') : '—' }}</p>
                            @else
                            <p class="font-medium" style="color:#d97706">Pendiente</p>
                            @endif
                        </div>
                        <div class="p-3 rounded-lg bg-white border" style="border-color:#e2e8f0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-1.5">Enviado</p>
                            @if($pe->enviado)
                            <p class="font-semibold text-gray-700">{{ optional($pe->enviadoPor)->name ?? 'Usuario #'.$pe->enviado_por }}</p>
                            <p class="text-gray-400 mt-0.5">{{ $pe->enviado_at ? \Carbon\Carbon::parse($pe->enviado_at)->format('d/m/Y H:i:s') : '—' }}</p>
                            @else
                            <p class="text-gray-300">—</p>
                            @endif
                        </div>
                    </div>

                    {{-- Checks --}}
                    @if($pe->checks->isNotEmpty())
                    <div class="mt-3 pt-3 border-t" style="border-color:#e2e8f0">
                        @php $checked = $pe->checks->where('checked', true)->count(); $total = $pe->checks->count(); @endphp
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold">Lista de verificación</p>
                            <span class="text-[10px] font-bold {{ $checked == $total ? 'text-green-600' : 'text-gray-400' }}">{{ $checked }}/{{ $total }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($pe->checks as $ch)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-medium border"
                                  style="background:{{ $ch->checked ? '#f0fdf4' : '#fff' }};
                                         border-color:{{ $ch->checked ? '#bbf7d0' : '#e2e8f0' }};
                                         color:{{ $ch->checked ? '#15803d' : '#9ca3af' }}">
                                {{ $ch->checked ? '✓' : '○' }}
                                {{ optional($ch->item)->label ?? '#'.$ch->id }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Tabla de auditoría --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Registro de auditoría</h3>
                <span class="text-xs text-gray-400">{{ $logs->count() }} registros</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha / Hora</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acción</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color:#f1f5f9">
                        @php
                            $iconMap = [
                                'proceso_creado' => '🆕', 'documento_recibido' => '📥', 'proceso_enviado' => '📤',
                                'check_marcado' => '✅', 'check_desmarcado' => '☐', 'proceso_rechazado' => '🔴',
                                'archivo_subido' => '📎', 'archivo_eliminado' => '🗑️', 'ajustado_derecho_emitido' => '⚖️',
                                'contratista_verificado' => '🛡️', 'polizas_aprobadas' => '📄', 'cdp_emitido' => '💰', 'rp_emitido' => '💵',
                            ];
                        @endphp
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-xs font-medium text-gray-700">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                                    <span>{{ $iconMap[$log->accion] ?? '📝' }}</span>
                                    <span class="capitalize">{{ str_replace('_',' ',$log->accion) }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-xs font-medium text-gray-700">{{ optional($log->user)->name ?? 'Sistema' }}</p>
                                <p class="text-xs text-gray-400">{{ optional($log->user)->email ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs text-gray-500 max-w-sm">{{ $log->descripcion ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs text-gray-400 font-mono">{{ $log->ip_address ?? '—' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <svg class="w-10 h-10 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm text-gray-400">Sin registros de auditoría.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
