{{-- Archivo: backend/resources/views/backend/proceso-cd/show.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $procesoCD->codigo }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Contratación Directa – Persona Natural</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $procesoCD->estado->badgeClass() }}">
                    {{ $procesoCD->estado->label() }}
                </span>
                <a href="{{ route('proceso-cd.auditoria', $procesoCD) }}"
                   class="text-xs text-gray-500 hover:text-gray-700 underline">Historial</a>
                <a href="{{ route('proceso-cd.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Mensajes flash --}}
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- BARRA DE PROGRESO POR ETAPAS --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Progreso del Proceso</h3>
                    <span class="text-xs text-gray-500">{{ $procesoCD->porcentajeAvance() }}% completado</span>
                </div>
                <div class="flex items-center gap-1">
                    @php
                        $etapasNombres = [
                            1 => 'Estudios Previos',
                            2 => 'Validaciones Presupuestales',
                            3 => 'Hoja de Vida',
                            4 => 'Revisión Jurídica',
                            5 => 'Contrato',
                            6 => 'RPC',
                            7 => 'Ejecución',
                        ];
                    @endphp
                    @for($i = 1; $i <= 7; $i++)
                        <div class="flex-1">
                            <div class="h-2 rounded-full {{ $procesoCD->etapa_actual > $i ? 'bg-green-500' : ($procesoCD->etapa_actual == $i ? 'bg-green-400 animate-pulse' : 'bg-gray-200') }}"></div>
                            <p class="text-center text-xs mt-1 {{ $procesoCD->etapa_actual == $i ? 'font-bold text-green-700' : 'text-gray-400' }}">
                                {{ $i }}. {{ $etapasNombres[$i] }}
                            </p>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- ═══════════════════════════════════════════════════ --}}
                {{-- COLUMNA IZQUIERDA: Información principal --}}
                {{-- ═══════════════════════════════════════════════════ --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Datos básicos --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Datos del Proceso</h3>
                        </div>
                        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-xs text-gray-500 uppercase">Objeto</span>
                                <p class="text-gray-800">{{ $procesoCD->objeto }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase">Valor</span>
                                <p class="text-gray-800 font-mono font-semibold">${{ number_format($procesoCD->valor, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase">Plazo</span>
                                <p class="text-gray-800">{{ $procesoCD->plazo_meses }} mes(es)</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase">Secretaría / Unidad</span>
                                <p class="text-gray-800">{{ $procesoCD->secretaria?->nombre }} / {{ $procesoCD->unidad?->nombre }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase">Creado por</span>
                                <p class="text-gray-800">{{ $procesoCD->creadoPor?->name }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase">Fecha de creación</span>
                                <p class="text-gray-800">{{ $procesoCD->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Contratista --}}
                    @if($procesoCD->contratista_nombre)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Contratista</h3>
                        </div>
                        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-xs text-gray-500">Nombre</span>
                                <p class="text-gray-800 font-medium">{{ $procesoCD->contratista_nombre }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Documento</span>
                                <p class="text-gray-800">{{ $procesoCD->contratista_tipo_documento }} {{ $procesoCD->contratista_documento }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Email / Tel</span>
                                <p class="text-gray-800">{{ $procesoCD->contratista_email }} {{ $procesoCD->contratista_telefono ? '/ ' . $procesoCD->contratista_telefono : '' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ═══════════════════════════════════════════════════ --}}
                    {{-- PANEL DE ETAPA ACTUAL --}}
                    {{-- ═══════════════════════════════════════════════════ --}}

                    {{-- ETAPA 2: Validaciones Paralelas --}}
                    @if($procesoCD->etapa_actual == 2)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-yellow-50 border-b border-yellow-200">
                            <h3 class="text-sm font-bold text-yellow-800 uppercase tracking-wide">Etapa 2: Validaciones Presupuestales (Paralelas)</h3>
                        </div>
                        <div class="px-5 py-4 space-y-3">
                            @php
                                $validaciones = [
                                    'paa_solicitado'         => 'PAA Solicitado',
                                    'certificado_no_planta'  => 'Certificado No Planta',
                                    'paz_salvo_rentas'       => 'Paz y Salvo Rentas',
                                    'paz_salvo_contabilidad' => 'Paz y Salvo Contabilidad',
                                    'compatibilidad_gasto'   => 'Compatibilidad del Gasto',
                                ];
                            @endphp
                            @foreach($validaciones as $campo => $label)
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                    <div class="flex items-center gap-2">
                                        @if($procesoCD->$campo)
                                            <span class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                        @else
                                            <span class="w-5 h-5 rounded-full bg-gray-200"></span>
                                        @endif
                                        <span class="text-sm text-gray-700">{{ $label }}</span>
                                    </div>
                                    @if(!$procesoCD->$campo && $procesoCD->usuarioPuedeOperar(auth()->user()))
                                        <form method="POST" action="{{ route('proceso-cd.validacion', $procesoCD) }}">
                                            @csrf
                                            <input type="hidden" name="campo" value="{{ $campo }}">
                                            <button type="submit" class="text-xs bg-green-100 text-green-800 px-3 py-1 rounded-lg hover:bg-green-200 font-medium transition">
                                                Completar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach

                            {{-- Compatibilidad → CDP --}}
                            @if($procesoCD->compatibilidad_aprobada)
                                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200 text-sm text-green-800">
                                    ✓ Compatibilidad aprobada — CDP habilitado.
                                    @if($procesoCD->cdp_aprobado)
                                        <br>✓ CDP Aprobado: <strong>{{ $procesoCD->numero_cdp }}</strong>
                                    @endif
                                </div>
                            @elseif($procesoCD->estado->value === 'cdp_bloqueado')
                                <div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-200 text-sm text-red-800">
                                    ✗ CDP bloqueado — La compatibilidad del gasto no ha sido aprobada.
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- ETAPA 3: Documentación contratista --}}
                    @if($procesoCD->etapa_actual == 3)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-blue-50 border-b border-blue-200">
                            <h3 class="text-sm font-bold text-blue-800 uppercase tracking-wide">Etapa 3: Documentación del Contratista</h3>
                        </div>
                        <div class="px-5 py-4 text-sm space-y-2">
                            <div class="flex items-center gap-2">
                                @if($procesoCD->hoja_vida_cargada)
                                    <span class="text-green-600 font-semibold">✓ Hoja de Vida SIGEP cargada</span>
                                @else
                                    <span class="text-red-600 font-semibold">✗ Hoja de Vida SIGEP pendiente</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if($procesoCD->checklist_validado)
                                    <span class="text-green-600 font-semibold">✓ Checklist validado por Abogado de Unidad</span>
                                @else
                                    <span class="text-orange-600 font-semibold">⏳ Checklist pendiente de validación</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ETAPA 4: Revisión Jurídica --}}
                    @if($procesoCD->etapa_actual == 4)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-indigo-50 border-b border-indigo-200">
                            <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wide">Etapa 4: Revisión Jurídica</h3>
                        </div>
                        <div class="px-5 py-4 text-sm space-y-3">
                            @if($procesoCD->numero_proceso_juridica)
                                <p class="text-green-700 font-semibold">Nº de Proceso: {{ $procesoCD->numero_proceso_juridica }}</p>
                            @endif
                            @if($procesoCD->observaciones_juridica)
                                <div class="p-3 bg-red-50 rounded-lg border border-red-200 text-red-800">
                                    <strong>Observaciones de devolución:</strong> {{ $procesoCD->observaciones_juridica }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- ETAPA 5: Contrato --}}
                    @if($procesoCD->etapa_actual == 5)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-purple-50 border-b border-purple-200">
                            <h3 class="text-sm font-bold text-purple-800 uppercase tracking-wide">Etapa 5: Generación y Firma de Contrato</h3>
                        </div>
                        <div class="px-5 py-4 text-sm space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex items-center gap-2">
                                    @if($procesoCD->firma_contratista)
                                        <span class="text-green-600 font-semibold">✓ Firma Contratista</span>
                                    @else
                                        <span class="text-gray-500">○ Firma Contratista pendiente</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($procesoCD->firma_ordenador_gasto)
                                        <span class="text-green-600 font-semibold">✓ Firma Ordenador del Gasto</span>
                                    @else
                                        <span class="text-gray-500">○ Firma Ordenador del Gasto pendiente</span>
                                    @endif
                                </div>
                            </div>

                            @if($procesoCD->observaciones_devolucion)
                                <div class="p-3 bg-red-50 rounded-lg border border-red-200 text-red-800">
                                    <strong>Devuelto:</strong> {{ $procesoCD->observaciones_devolucion }}
                                </div>
                            @endif

                            {{-- Botones de firma --}}
                            @if($procesoCD->estado->value === 'contrato_generado' || $procesoCD->estado->value === 'contrato_firmado_parcial')
                                <div class="flex gap-3 mt-2">
                                    @if(!$procesoCD->firma_contratista)
                                        <form method="POST" action="{{ route('proceso-cd.firma', $procesoCD) }}">
                                            @csrf
                                            <input type="hidden" name="tipo_firma" value="contratista">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition">
                                                Registrar Firma Contratista
                                            </button>
                                        </form>
                                    @endif
                                    @if(!$procesoCD->firma_ordenador_gasto)
                                        <form method="POST" action="{{ route('proceso-cd.firma', $procesoCD) }}">
                                            @csrf
                                            <input type="hidden" name="tipo_firma" value="ordenador_gasto">
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition">
                                                Registrar Firma Ordenador
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- ETAPA 6: RPC --}}
                    @if($procesoCD->etapa_actual == 6)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-teal-50 border-b border-teal-200">
                            <h3 class="text-sm font-bold text-teal-800 uppercase tracking-wide">Etapa 6: RPC</h3>
                        </div>
                        <div class="px-5 py-4 text-sm space-y-2">
                            @if($procesoCD->numero_rpc)
                                <p class="text-green-700 font-semibold">Nº RPC: {{ $procesoCD->numero_rpc }}</p>
                            @endif
                            <div class="flex items-center gap-2">
                                @if($procesoCD->rpc_firmado_flag)
                                    <span class="text-green-600">✓ RPC firmado</span>
                                @else
                                    <span class="text-gray-500">○ RPC pendiente de firma</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if($procesoCD->expediente_radicado_flag)
                                    <span class="text-green-600">✓ Expediente final radicado</span>
                                @else
                                    <span class="text-gray-500">○ Expediente pendiente de radicación</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ETAPA 7: Ejecución --}}
                    @if($procesoCD->etapa_actual == 7 || $procesoCD->estado->value === 'en_ejecucion')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-emerald-50 border-b border-emerald-200">
                            <h3 class="text-sm font-bold text-emerald-800 uppercase tracking-wide">Etapa 7: Inicio de Ejecución</h3>
                        </div>
                        <div class="px-5 py-4 text-sm space-y-2">
                            @if($procesoCD->numero_contrato)
                                <p>Nº Contrato: <strong>{{ $procesoCD->numero_contrato }}</strong></p>
                            @endif
                            <div class="flex items-center gap-2">
                                {!! $procesoCD->arl_solicitada ? '<span class="text-green-600">✓ ARL solicitada</span>' : '<span class="text-gray-500">○ ARL pendiente</span>' !!}
                            </div>
                            <div class="flex items-center gap-2">
                                {!! $procesoCD->acta_inicio_firmada ? '<span class="text-green-600">✓ Acta de inicio firmada</span>' : '<span class="text-gray-500">○ Acta de inicio pendiente</span>' !!}
                            </div>
                            @if($procesoCD->fecha_inicio_ejecucion)
                                <p class="text-green-700 font-semibold mt-2">Inicio de ejecución: {{ $procesoCD->fecha_inicio_ejecucion->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- ═══════════════════════════════════════════════════ --}}
                    {{-- DOCUMENTOS --}}
                    {{-- ═══════════════════════════════════════════════════ --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Documentos</h3>
                        </div>

                        {{-- Subir documento --}}
                        @if(!$procesoCD->estado->esFinal())
                        <div class="px-5 py-4 border-b border-gray-100">
                            <form method="POST" action="{{ route('proceso-cd.documentos.subir', $procesoCD) }}" enctype="multipart/form-data"
                                  class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                    <select name="tipo_documento" required class="w-full rounded-lg border-gray-300 text-xs">
                                        <option value="">Seleccione…</option>
                                        @php
                                            $tiposDoc = [
                                                'estudios_previos' => 'Estudios Previos',
                                                'paa' => 'PAA',
                                                'no_planta' => 'Certificado No Planta',
                                                'paz_salvo_rentas' => 'Paz y Salvo Rentas',
                                                'paz_salvo_contabilidad' => 'Paz y Salvo Contabilidad',
                                                'compatibilidad_gasto' => 'Compatibilidad del Gasto',
                                                'cdp' => 'CDP',
                                                'hoja_vida_sigep' => 'Hoja de Vida SIGEP',
                                                'cedula' => 'Cédula',
                                                'rut' => 'RUT',
                                                'antecedentes_disciplinarios' => 'Antecedentes Disciplinarios',
                                                'antecedentes_fiscales' => 'Antecedentes Fiscales',
                                                'antecedentes_judiciales' => 'Antecedentes Judiciales',
                                                'seguridad_social_salud' => 'Seguridad Social Salud',
                                                'seguridad_social_pension' => 'Seguridad Social Pensión',
                                                'certificado_cuenta_bancaria' => 'Certificado Cuenta Bancaria',
                                                'checklist_juridica' => 'Checklist Jurídica',
                                                'contrato_electronico' => 'Contrato Electrónico',
                                                'contrato_firmado' => 'Contrato Firmado',
                                                'solicitud_rpc' => 'Solicitud RPC',
                                                'rpc' => 'RPC',
                                                'expediente_fisico_final' => 'Expediente Físico Final',
                                                'solicitud_arl' => 'Solicitud ARL',
                                                'acta_inicio' => 'Acta de Inicio',
                                            ];
                                        @endphp
                                        @foreach($tiposDoc as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Archivo</label>
                                    <input type="file" name="archivo" required class="w-full text-xs border border-gray-300 rounded-lg file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-green-50 file:text-green-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Observaciones</label>
                                    <input type="text" name="observaciones" class="w-full rounded-lg border-gray-300 text-xs" placeholder="Opcional">
                                </div>
                                <div>
                                    <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white py-2 px-4 rounded-lg text-xs font-medium transition">
                                        Subir
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        {{-- Lista de documentos --}}
                        <div class="divide-y divide-gray-100">
                            @forelse($procesoCD->documentos as $doc)
                            <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold
                                        {{ $doc->estado_aprobacion === 'aprobado' ? 'bg-green-100 text-green-700' : ($doc->estado_aprobacion === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ strtoupper(substr($doc->tipo_documento, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-700 truncate">{{ $doc->nombre_archivo }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $tiposDoc[$doc->tipo_documento] ?? $doc->tipo_documento }} ·
                                            Etapa {{ $doc->etapa }} ·
                                            {{ $doc->subidoPor?->name }} ·
                                            {{ $doc->created_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                        {{ $doc->estado_aprobacion === 'aprobado' ? 'bg-green-100 text-green-700' : ($doc->estado_aprobacion === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                        {{ ucfirst($doc->estado_aprobacion) }}
                                    </span>
                                    <a href="{{ route('proceso-cd.documentos.descargar', [$procesoCD, $doc]) }}"
                                       class="text-xs text-blue-600 hover:underline">Descargar</a>

                                    @if($doc->estado_aprobacion === 'pendiente' && $procesoCD->usuarioPuedeOperar(auth()->user()))
                                        <form method="POST" action="{{ route('proceso-cd.documentos.aprobar', [$procesoCD, $doc]) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="accion" value="aprobar">
                                            <button type="submit" class="text-xs text-green-700 hover:underline">Aprobar</button>
                                        </form>
                                        <form method="POST" action="{{ route('proceso-cd.documentos.aprobar', [$procesoCD, $doc]) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="accion" value="rechazar">
                                            <button type="submit" class="text-xs text-red-700 hover:underline">Rechazar</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                                No hay documentos cargados aún.
                            </div>
                            @endforelse
                        </div>

                        {{-- Documentos faltantes --}}
                        @if(!empty($documentosFaltantes))
                        <div class="px-5 py-3 bg-orange-50 border-t border-orange-200">
                            <p class="text-xs font-semibold text-orange-700 uppercase mb-1">Documentos faltantes para avanzar:</p>
                            <ul class="list-disc list-inside text-xs text-orange-600 space-y-0.5">
                                @foreach($documentosFaltantes as $faltante)
                                    <li>{{ $tiposDoc[$faltante] ?? $faltante }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════════ --}}
                {{-- COLUMNA DERECHA: Acciones y auditoría --}}
                {{-- ═══════════════════════════════════════════════════ --}}
                <div class="space-y-6">

                    {{-- Acciones / Transiciones --}}
                    @if(!$procesoCD->estado->esFinal())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-green-50 border-b border-green-200">
                            <h3 class="text-sm font-bold text-green-800 uppercase tracking-wide">Acciones Disponibles</h3>
                        </div>
                        <div class="px-5 py-4 space-y-3">
                            @foreach($transiciones as $transicion)
                                <form method="POST" action="{{ route('proceso-cd.transicionar', $procesoCD) }}">
                                    @csrf
                                    <input type="hidden" name="estado_destino" value="{{ $transicion->value }}">

                                    {{-- Campos extra según destino --}}
                                    @if($transicion->value === 'cdp_aprobado')
                                        <div class="mb-2 space-y-2">
                                            <input type="text" name="numero_cdp" placeholder="Nº CDP" class="w-full rounded-lg border-gray-300 text-xs" required>
                                            <input type="number" name="valor_cdp" placeholder="Valor CDP" class="w-full rounded-lg border-gray-300 text-xs" step="0.01">
                                        </div>
                                    @endif
                                    @if($transicion->value === 'proceso_numero_generado')
                                        <div class="mb-2">
                                            <input type="text" name="numero_proceso" placeholder="Nº Proceso (CD-PS-XX-2026)" class="w-full rounded-lg border-gray-300 text-xs" required>
                                        </div>
                                    @endif
                                    @if($transicion->value === 'rpc_firmado')
                                        <div class="mb-2">
                                            <input type="text" name="numero_rpc" placeholder="Nº RPC" class="w-full rounded-lg border-gray-300 text-xs" required>
                                        </div>
                                    @endif
                                    @if($transicion->value === 'en_ejecucion')
                                        <div class="mb-2 space-y-2">
                                            <input type="text" name="numero_contrato" placeholder="Nº Contrato" class="w-full rounded-lg border-gray-300 text-xs">
                                            <input type="date" name="fecha_inicio" class="w-full rounded-lg border-gray-300 text-xs">
                                        </div>
                                    @endif

                                    <div class="mb-2">
                                        <textarea name="comentario" rows="1" class="w-full rounded-lg border-gray-300 text-xs" placeholder="Comentario (opcional)"></textarea>
                                    </div>

                                    <button type="submit"
                                            class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition
                                                   bg-green-100 text-green-800 hover:bg-green-200">
                                        → {{ $transicion->label() }}
                                    </button>
                                </form>
                            @endforeach

                            {{-- Devolver --}}
                            @if(in_array($procesoCD->estado->value, ['en_revision_juridica', 'contrato_generado', 'contrato_firmado_parcial']))
                                <hr class="border-gray-200">
                                <form method="POST" action="{{ route('proceso-cd.devolver', $procesoCD) }}">
                                    @csrf
                                    <input type="hidden" name="tipo_devolucion"
                                           value="{{ $procesoCD->estado->value === 'en_revision_juridica' ? 'juridica' : 'contrato' }}">
                                    <div class="mb-2">
                                        <textarea name="observaciones" rows="2" required class="w-full rounded-lg border-gray-300 text-xs"
                                                  placeholder="Observaciones de devolución (obligatorio)"></textarea>
                                    </div>
                                    <button type="submit"
                                            class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium bg-red-100 text-red-800 hover:bg-red-200 transition">
                                        ↩ Devolver con observaciones
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Errores para avanzar --}}
                    @if(!empty($erroresAvance))
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-red-50 border-b border-red-200">
                            <h3 class="text-xs font-bold text-red-700 uppercase tracking-wide">Pendientes para avanzar</h3>
                        </div>
                        <ul class="px-5 py-3 text-xs text-red-600 space-y-1 list-disc list-inside">
                            @foreach($erroresAvance as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Admin: Cancelar --}}
                    @if(auth()->user()->hasRole('admin') && !$procesoCD->estado->esFinal())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-red-50 border-b border-red-200">
                            <h3 class="text-xs font-bold text-red-700 uppercase tracking-wide">Administración</h3>
                        </div>
                        <div class="px-5 py-4">
                            <form method="POST" action="{{ route('proceso-cd.cancelar', $procesoCD) }}"
                                  onsubmit="return confirm('¿Está seguro de cancelar este proceso?')">
                                @csrf
                                <textarea name="motivo" rows="2" required class="w-full rounded-lg border-gray-300 text-xs mb-2"
                                          placeholder="Motivo de cancelación"></textarea>
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg text-xs font-medium transition">
                                    Cancelar Proceso
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- Últimas acciones de auditoría --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wide">Últimas Acciones</h3>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                            @forelse($auditoria as $log)
                            <div class="px-5 py-2.5">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-xs font-semibold text-gray-700">{{ $log->usuario?->name ?? 'Sistema' }}</span>
                                    <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-gray-500">{{ $log->descripcion }}</p>
                                @if($log->estado_anterior && $log->estado_nuevo)
                                    <p class="text-xs mt-0.5">
                                        <span class="text-gray-400">{{ $log->estado_anterior }}</span>
                                        →
                                        <span class="font-medium text-green-700">{{ $log->estado_nuevo }}</span>
                                    </p>
                                @endif
                            </div>
                            @empty
                            <div class="px-5 py-6 text-center text-gray-400 text-xs">
                                Sin registros aún.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

