<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-0.5">
                    <a href="{{ route('hacienda.index') }}" class="hover:text-green-700 transition-colors">Hacienda</a>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">{{ $proceso->codigo }}</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Detalle del proceso</h1>
            </div>
            <a href="{{ route('hacienda.index') }}"
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
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Workflow</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->workflow)->nombre ?? 'N/D' }}</p>
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
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Solicitante</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->creador)->name ?? 'N/A' }}</p>
                </div>
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
            </div>
        </div>

        {{-- Decisión --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Decisión de Hacienda</h3>
            </div>
            <div class="p-5 grid sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl space-y-3" style="background:#f0fdf4;border:1px solid #bbf7d0">
                    <p class="text-sm font-semibold" style="color:#15803d">✅ Aprobar viabilidad económica</p>
                    <form method="POST" action="{{ route('hacienda.aprobar', $proceso->id) }}">
                        @csrf
                        <div class="space-y-3">
                            <textarea name="observaciones" rows="2"
                                class="w-full border rounded-xl px-3 py-2 text-sm bg-white resize-none focus:outline-none focus:ring-2 focus:ring-green-500"
                                style="border-color:#d1fae5"
                                placeholder="Observaciones (opcional)..."></textarea>
                            <button type="submit"
                                onclick="return confirm('¿Confirmar aprobación y enviar a Jurídica?')"
                                class="w-full px-4 py-2 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
                                style="background:#15803d">
                                Aprobar y enviar a Jurídica
                            </button>
                        </div>
                    </form>
                </div>
                <div class="p-4 rounded-xl space-y-3" style="background:#fef2f2;border:1px solid #fecaca">
                    <p class="text-sm font-semibold" style="color:#dc2626">❌ Rechazar proceso</p>
                    <form method="POST" action="{{ route('hacienda.rechazar', $proceso->id) }}">
                        @csrf
                        <div class="space-y-3">
                            <textarea name="motivo" rows="2"
                                class="w-full border rounded-xl px-3 py-2 text-sm bg-white resize-none focus:outline-none focus:ring-2 focus:ring-red-500"
                                style="border-color:#fecaca"
                                placeholder="Motivo del rechazo (obligatorio)..." required minlength="10"></textarea>
                            <button type="submit"
                                onclick="return confirm('¿Rechazar y devolver el proceso?')"
                                class="w-full px-4 py-2 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
                                style="background:#dc2626">
                                Rechazar y devolver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Recepción --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Recepción del documento</h3>
            <form method="POST" action="{{ route('workflow.recibir', $proceso->id) }}">
                @csrf
                <input type="hidden" name="area_role" value="hacienda">
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

        {{-- CDP --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Certificado de Disponibilidad Presupuestal (CDP)</h3>
            </div>
            <div class="p-5">
                @if($proceso->numero_cdp)
                <div class="flex items-center gap-3 p-3 rounded-xl mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="#15803d" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-semibold" style="color:#15803d">CDP emitido: <span class="font-bold">{{ $proceso->numero_cdp }}</span> — $ {{ number_format($proceso->valor_cdp ?? 0, 0, ',', '.') }}</p>
                </div>
                @endif
                <form method="POST" action="{{ route('hacienda.cdp', $proceso->id) }}" class="grid sm:grid-cols-2 gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Número CDP <span class="text-red-500">*</span></label>
                        <input type="text" name="numero_cdp" value="{{ $proceso->numero_cdp }}"
                               class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                               style="border-color:#e2e8f0" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Valor CDP <span class="text-red-500">*</span></label>
                        <input type="number" name="valor_cdp" value="{{ $proceso->valor_cdp }}" step="0.01" min="0"
                               class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                               style="border-color:#e2e8f0" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Fecha CDP <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_cdp" value="{{ $proceso->fecha_cdp ? \Carbon\Carbon::parse($proceso->fecha_cdp)->format('Y-m-d') : '' }}"
                               class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                               style="border-color:#e2e8f0" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Observaciones</label>
                        <input type="text" name="observaciones"
                               class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                               style="border-color:#e2e8f0" placeholder="Opcional...">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                                style="background:#14532d">
                            {{ $proceso->numero_cdp ? 'Actualizar CDP' : 'Emitir CDP' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RP --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Registro Presupuestal (RP)</h3>
            </div>
            <div class="p-5">
                @if($proceso->numero_rp)
                <div class="flex items-center gap-3 p-3 rounded-xl mb-4" style="background:#eff6ff;border:1px solid #bfdbfe">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-semibold" style="color:#2563eb">RP emitido: <span class="font-bold">{{ $proceso->numero_rp }}</span> — $ {{ number_format($proceso->valor_rp ?? 0, 0, ',', '.') }}</p>
                </div>
                @endif
                <form method="POST" action="{{ route('hacienda.rp', $proceso->id) }}" class="grid sm:grid-cols-2 gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Número RP <span class="text-red-500">*</span></label>
                        <input type="text" name="numero_rp" value="{{ $proceso->numero_rp }}"
                               class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               style="border-color:#e2e8f0" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Valor RP <span class="text-red-500">*</span></label>
                        <input type="number" name="valor_rp" value="{{ $proceso->valor_rp }}" step="0.01" min="0"
                               class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               style="border-color:#e2e8f0" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Fecha RP <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_rp" value="{{ $proceso->fecha_rp ? \Carbon\Carbon::parse($proceso->fecha_rp)->format('Y-m-d') : '' }}"
                               class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               style="border-color:#e2e8f0" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Observaciones</label>
                        <input type="text" name="observaciones"
                               class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               style="border-color:#e2e8f0" placeholder="Opcional...">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                                style="background:#1d4ed8">
                            {{ $proceso->numero_rp ? 'Actualizar RP' : 'Emitir RP' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Archivos --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Documentos de la etapa</h3>
            </div>
            <div class="p-5 space-y-4">
                <form method="POST" action="{{ route('workflow.files.store', $proceso->id) }}" enctype="multipart/form-data"
                      class="grid sm:grid-cols-3 gap-3 p-4 rounded-xl" style="background:#f8fafc;border:1px dashed #cbd5e1">
                    @csrf
                    <input type="hidden" name="area_role" value="hacienda">
                    <select name="tipo_archivo" required
                            class="px-3 py-2.5 rounded-xl border text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                        <option value="">Tipo de documento</option>
                        <option value="certificado_cdp">Certificado CDP</option>
                        <option value="certificado_rp">Certificado RP</option>
                        <option value="disponibilidad_presupuestal">Disponibilidad Presupuestal</option>
                        <option value="anexo">Anexo</option>
                        <option value="otro">Otro</option>
                    </select>
                    <input type="file" name="archivo" required
                           class="px-3 py-2 rounded-xl border text-sm bg-white" style="border-color:#e2e8f0">
                    <button type="submit"
                            class="px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                            style="background:#14532d">Subir documento</button>
                </form>
                @php $archivosEtapa = $proceso->archivos->where('etapa_id', $proceso->etapa_actual_id); @endphp
                <div class="space-y-2">
                    @forelse($archivosEtapa as $ar)
                    <div class="flex items-center justify-between p-3 rounded-xl" style="background:#f8fafc;border:1px solid #e2e8f0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:#dbeafe">
                                <svg class="w-4 h-4" fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-700 truncate">{{ $ar->nombre_original }}</p>
                                <p class="text-xs text-gray-400">{{ str_replace('_',' ',$ar->tipo_archivo) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('workflow.files.download', $ar->id) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                            <form method="POST" action="{{ route('workflow.files.destroy', $ar->id) }}" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">Aún no hay documentos cargados en esta etapa.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Checklist --}}
        @if($procesoEtapaActual)
        @php $checks = $procesoEtapaActual->checks; @endphp
        @if($checks->isNotEmpty())
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Lista de verificación</h3>
            </div>
            <div class="p-4 space-y-2">
                @foreach($checks as $check)
                <form method="POST" action="{{ route('workflow.checks.toggle', [$proceso->id, $check->id]) }}">
                    @csrf
                    <input type="hidden" name="area_role" value="hacienda">
                    <button type="submit"
                            class="w-full flex items-center gap-3 p-3 rounded-xl text-left text-sm transition-all"
                            style="background:{{ $check->checked?'#f0fdf4':'#f8fafc' }};border:1px solid {{ $check->checked?'#bbf7d0':'#e2e8f0' }}"
                            {{ !$procesoEtapaActual->recibido?'disabled':'' }}>
                        <span class="text-base">{{ $check->checked?'✅':'☐' }}</span>
                        <span class="font-medium text-gray-700">{{ optional($check->item)->label ?? 'Ítem #'.$check->id }}</span>
                        @if(optional($check->item)->requerido)<span class="ml-auto text-xs text-gray-400">(requerido)</span>@endif
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif
        @endif

        {{-- Historial --}}
        @if($proceso->auditorias->isNotEmpty())
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Historial de actividad</h3>
            </div>
            <div class="p-5 space-y-3">
                @foreach($proceso->auditorias as $audit)
                <div class="flex gap-3 text-sm">
                    <div class="w-1.5 h-1.5 rounded-full mt-2 shrink-0" style="background:#14532d"></div>
                    <div>
                        <span class="font-semibold text-gray-800 capitalize">{{ str_replace('_',' ',$audit->accion) }}</span>
                        @if($audit->descripcion)
                        <span class="text-gray-500"> — {{ $audit->descripcion }}</span>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ optional($audit->user)->name ?? 'Sistema' }}
                            · {{ \Carbon\Carbon::parse($audit->created_at)->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
