<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Unidad Solicitante</h1>
                <p class="text-xs text-gray-400 mt-0.5">Documentos radicados y en trámite</p>
            </div>
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('unidad_solicitante'))
            <a href="{{ route('procesos.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition-all"
               style="background:#14532d">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva solicitud
            </a>
            @endif
        </div>
    </x-slot>

    <div class="p-6">

        @if(session('success'))
        <div class="mb-4 flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-4 p-3.5 rounded-xl text-sm" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        <div class="flex gap-5">

            {{-- LISTA DE PROCESOS --}}
            <div class="w-72 shrink-0 space-y-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Procesos en bandeja ({{ count($procesos) }})</p>
                @forelse($procesos as $p)
                @php $sel = $proceso && $proceso->id == $p->id; @endphp
                <a href="{{ url('/unidad?proceso_id='.$p->id) }}"
                   class="flex flex-col p-3.5 rounded-xl border transition-all"
                   style="background:{{ $sel?'#f0fdf4':'#fff' }};border-color:{{ $sel?'#86efac':'#e2e8f0' }};box-shadow:{{ $sel?'0 0 0 1px #86efac':'none' }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold" style="color:{{ $sel?'#15803d':'#374151' }}">{{ $p->codigo }}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                              style="background:{{ in_array($p->estado,['completado','cerrado'])?'#dcfce7':($p->estado=='rechazado'?'#fee2e2':'#dbeafe') }};color:{{ in_array($p->estado,['completado','cerrado'])?'#15803d':($p->estado=='rechazado'?'#dc2626':'#2563eb') }}">
                            {{ ucfirst(str_replace('_',' ',$p->estado)) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ $p->objeto }}</p>
                </a>
                @empty
                <div class="text-sm text-gray-400 text-center py-8">No hay procesos en tu bandeja.</div>
                @endforelse
            </div>

            {{-- DETALLE DEL PROCESO --}}
            <div class="flex-1 min-w-0 space-y-4">
            @if($proceso)

                {{-- Info del proceso --}}
                <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">{{ $proceso->codigo }}</h2>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $proceso->objeto }}</p>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full font-semibold shrink-0"
                              style="background:#dbeafe;color:#1d4ed8">{{ ucfirst(str_replace('_',' ',$proceso->estado)) }}</span>
                    </div>
                </div>

                {{-- Recibir --}}
                <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Recepción del expediente</h3>
                    <form method="POST" action="{{ route('workflow.recibir',$proceso->id) }}">
                        @csrf
                        <input type="hidden" name="area_role" value="{{ $areaRole }}">
                        @php $rx = $procesoEtapa && $procesoEtapa->recibido; @endphp
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                                style="background:{{ $rx?'#f0fdf4':'#14532d' }};color:{{ $rx?'#15803d':'#fff' }};border:{{ $rx?'1px solid #bbf7d0':'none' }}"
                                {{ $rx?'disabled':'' }}>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($rx)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                @endif
                            </svg>
                            {{ $rx?'Recibido ✓':'Marcar como recibido' }}
                        </button>
                    </form>
                </div>

                {{-- Archivos --}}
                <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                    <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                        <h3 class="text-sm font-semibold text-gray-700">Documentos requeridos</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        @if($puedeEditar)
                        {{-- Solo mostrar formulario si puede editar (no ha enviado) --}}
                        <form method="POST" action="{{ route('workflow.files.store',$proceso->id) }}" enctype="multipart/form-data"
                              class="grid sm:grid-cols-3 gap-3 p-4 rounded-xl" style="background:#f8fafc;border:1px dashed #cbd5e1">
                            @csrf
                            <input type="hidden" name="area_role" value="{{ $areaRole }}">
                            @php
                                // Obtener número de etapa actual
                                $etapaActual = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
                                $ordenEtapa = $etapaActual ? $etapaActual->orden : 0;
                            @endphp
                            <select name="tipo_archivo" class="px-3 py-2.5 rounded-xl border text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0" required>
                                <option value="">Tipo de documento</option>
                                @if($ordenEtapa == 0)
                                    {{-- ETAPA 0: Solo Estudios Previos --}}
                                    <option value="estudios_previos">Estudios Previos</option>
                                @elseif($ordenEtapa == 2)
                                    {{-- ETAPA 2: Documentos del contratista --}}
                                    <option value="hoja_vida_sigep">Hoja de Vida SIGEP</option>
                                    <option value="certificado_estudio">Certificado de Estudio</option>
                                    <option value="certificado_experiencia">Certificado de Experiencia</option>
                                    <option value="rut">RUT</option>
                                    <option value="cedula_contratista">Cédula</option>
                                    <option value="cuenta_bancaria">Cuenta Bancaria</option>
                                    <option value="antecedentes">Certificados de Antecedentes</option>
                                    <option value="seguridad_social">Seguridad Social (Salud/Pensión)</option>
                                    <option value="certificado_medico">Certificado Médico</option>
                                    <option value="tarjeta_profesional">Tarjeta Profesional</option>
                                    <option value="redam">REDAM</option>
                                @elseif($ordenEtapa == 3)
                                    {{-- ETAPA 3: Documentos contractuales --}}
                                    <option value="invitacion_oferta">Invitación a Presentar Oferta</option>
                                    <option value="solicitud_contratacion">Solicitud de Contratación y Supervisión</option>
                                    <option value="certificado_idoneidad">Certificado de Idoneidad</option>
                                    <option value="estudios_previos_finales">Estudios Previos Finales</option>
                                    <option value="analisis_sector">Análisis del Sector</option>
                                    <option value="aceptacion_oferta">Aceptación de Oferta</option>
                                    <option value="ficha_bpin">Ficha BPIN (opcional)</option>
                                    <option value="excepcion_fiscal">Excepción Regla Fiscal (opcional)</option>
                                @elseif($ordenEtapa == 4)
                                    {{-- ETAPA 4: Carpeta precontractual --}}
                                    <option value="carpeta_precontractual">Carpeta Precontractual Completa</option>
                                    <option value="anexo">Anexo</option>
                                @elseif($ordenEtapa == 9)
                                    {{-- ETAPA 9: ARL y Acta de Inicio --}}
                                    <option value="solicitud_arl">Solicitud ARL</option>
                                    <option value="acta_inicio">Acta de Inicio</option>
                                    <option value="registro_secop">Registro Inicio SECOP II</option>
                                @else
                                    {{-- Otras etapas --}}
                                    <option value="anexo">Anexo</option>
                                    <option value="otro">Otro</option>
                                @endif
                            </select>
                            <input type="file" name="archivo" required
                                   class="px-3 py-2 rounded-xl border text-sm bg-white" style="border-color:#e2e8f0">
                            <button type="submit"
                                    class="px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                                    style="background:#14532d">
                                Subir documento
                            </button>
                        </form>
                        @else
                        {{-- Mensaje si ya envió --}}
                        <div class="p-4 rounded-xl flex items-center gap-3" style="background:#fef3c7;border:1px solid #fcd34d">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-sm font-medium text-yellow-800">Esta etapa ya fue enviada. No puedes agregar más documentos.</p>
                        </div>
                        @endif

                        <div class="space-y-2">
                            @forelse($archivos as $ar)
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
                                    <a href="{{ route('workflow.files.download',$ar->id) }}"
                                       class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                    @if($puedeEditar && (auth()->user()->hasRole('admin') || auth()->user()->hasRole($areaRole)))
                                    {{-- Solo mostrar botón eliminar si puede editar --}}
                                    <form method="POST" action="{{ route('workflow.files.destroy',$ar->id) }}" onsubmit="return confirm('\u00bfEliminar?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <p class="text-sm text-gray-400 text-center py-4">Aún no hay documentos cargados en esta etapa.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Checklist --}}
                @if($checks->isNotEmpty())
                <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                    <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                        <h3 class="text-sm font-semibold text-gray-700">Lista de verificación</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        @foreach($checks as $c)
                        <form method="POST" action="{{ route('workflow.checks.toggle',[$proceso->id,$c->check_id]) }}">
                            @csrf
                            <input type="hidden" name="area_role" value="{{ $areaRole }}">
                            <button type="submit"
                                    class="w-full flex items-center gap-3 p-3 rounded-xl text-left text-sm transition-all"
                                    style="background:{{ $c->checked?'#f0fdf4':'#f8fafc' }};border:1px solid {{ $c->checked?'#bbf7d0':'#e2e8f0' }}"
                                    {{ (!$procesoEtapa||!$procesoEtapa->recibido||!$puedeEditar)?'disabled':'' }}>
                                <span class="text-base">{{ $c->checked?'\u2705':'\u2610' }}</span>
                                <span class="font-medium text-gray-700">{{ $c->label }}</span>
                                @if($c->requerido)<span class="ml-auto text-xs text-gray-400">(requerido)</span>@endif
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Enviar --}}
                @if($puedeEditar)
                {{-- Solo mostrar botón enviar si puede editar --}}
                <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                    <form method="POST" action="{{ route('workflow.enviar',$proceso->id) }}">
                        @csrf
                        <input type="hidden" name="area_role" value="{{ $areaRole }}">
                        <div class="flex items-center justify-between">
                            <div>
                                @php
                                    $etapa = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
                                    $orden = $etapa ? $etapa->orden : 0;
                                @endphp
                                <h3 class="text-sm font-semibold text-gray-700">Enviar a siguiente etapa</h3>
                                @if(!$enviarHabilitado)
                                    @if($orden == 0)
                                        <p class="text-xs mt-0.5" style="color:#dc2626">Debes subir los Estudios Previos para enviar a Descentralización.</p>
                                    @else
                                        <p class="text-xs mt-0.5" style="color:#dc2626">Debes subir al menos un documento para avanzar.</p>
                                    @endif
                                @endif
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all"
                                    style="background:{{ $enviarHabilitado?'#14532d':'#e5e7eb' }};color:{{ $enviarHabilitado?'#fff':'#9ca3af' }}"
                                    {{ $enviarHabilitado?'':'disabled' }}
                                    {{ $enviarHabilitado?'onclick="return confirm(\u00bfEnviar el proceso a Planeaci\u00f3n?\u00bb)"':'' }}>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                Enviar a siguiente secretaría
                            </button>
                        </div>
                    </form>
                </div>
                @else
                {{-- Mensaje si ya envió --}}
                <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
                    <div class="flex items-center gap-3 p-4 rounded-xl" style="background:#dcfce7;border:1px solid #86efac">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-green-800">Este proceso ya fue enviado a la siguiente etapa.</p>
                    </div>
                </div>
                @endif

            @else
                <div class="bg-white rounded-2xl border flex flex-col items-center justify-center py-20" style="border-color:#e2e8f0">
                    <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-gray-400 text-sm">Selecciona un proceso de la lista para ver el detalle</p>
                </div>
            @endif
            </div>
        </div>
    </div>
</x-app-layout>
