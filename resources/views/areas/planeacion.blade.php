<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Secretaría de Planeación
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-3 bg-green-100 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-3 bg-red-100 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">Procesos en Planeación</div>
                    </div>

                    <div class="mt-4">
                        @forelse($procesos as $p)
                            <a class="block px-3 py-2 rounded border mb-2 {{ $proceso && $proceso->id == $p->id ? 'bg-gray-100' : '' }}"
                               href="{{ url('/planeacion?proceso_id='.$p->id) }}">
                                <div class="text-sm font-medium">{{ $p->codigo }} — {{ $p->objeto }}</div>
                                <div class="text-xs text-gray-500">Estado: {{ $p->estado }}</div>
                            </a>
                        @empty
                            <div class="text-sm text-gray-600">No hay procesos en esta secretaría.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            @if($proceso)
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6 space-y-5">

                        <div class="text-sm">
                            <div class="font-semibold">{{ $proceso->codigo }}</div>
                            <div class="text-gray-600">{{ $proceso->objeto }}</div>
                        </div>

                        {{-- Si aún no existe instancia de etapa --}}
                        @if(!$procesoEtapa)
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-900">
                                Este proceso aún no tiene etapa-instancia cargada. Marca <b>“Recibí”</b> para inicializar el checklist.
                            </div>
                        @endif

                        {{-- RECIBÍ Y VISUALIZAR DOCUMENTO --}}
                        @php
                            $documentoEstudiosPrevios = DB::table('proceso_etapa_archivos')
                                ->where('proceso_id', $proceso->id)
                                ->where('tipo_archivo', 'estudios_previos')
                                ->first();
                        @endphp
                        
                        <div class="border-t pt-4 space-y-4">
                            <div class="font-semibold text-lg">📄 Documento de Estudios Previos</div>
                            
                            @if($documentoEstudiosPrevios)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-800">{{ $documentoEstudiosPrevios->nombre_original }}</p>
                                            <p class="text-xs text-gray-600 mt-1">
                                                Subido el {{ \Carbon\Carbon::parse($documentoEstudiosPrevios->uploaded_at)->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                        <div class="flex gap-2">
                                            <a href="{{ route('workflow.files.download', ['archivo' => $documentoEstudiosPrevios->id, 'inline' => 1]) }}" 
                                               class="px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700"
                                               target="_blank">
                                                👁️ Ver
                                            </a>
                                            <a href="{{ route('workflow.files.download', $documentoEstudiosPrevios->id) }}" 
                                               class="px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
                                               target="_blank">
                                                📥 Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-900">
                                    ⚠️ No se encontró el documento de Estudios Previos
                                </div>
                            @endif
                        </div>

                        {{-- BOTÓN RECIBIR --}}
                        @if(!$procesoEtapa || !$procesoEtapa->recibido)
                            <form method="POST" action="{{ route('workflow.recibir', $proceso->id) }}">
                                @csrf
                                <input type="hidden" name="area_role" value="{{ $areaRole }}">
                                <button
                                    class="px-4 py-2 rounded text-white bg-blue-600 hover:bg-blue-700"
                                    @if(!$documentoEstudiosPrevios) disabled title="Debes visualizar/descargar el documento primero" @endif>
                                    ✓ Confirmar Recepción del Documento
                                </button>
                                @if(!$documentoEstudiosPrevios)
                                    <p class="text-xs text-red-600 mt-1">⚠️ Primero debes verificar que el documento esté disponible</p>
                                @endif
                            </form>
                        @else
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800">
                                ✅ Documento recibido el {{ \Carbon\Carbon::parse($procesoEtapa->recibido_at)->format('d/m/Y H:i') }}
                            </div>
                        @endif

                        {{-- CHECKLIST --}}
                        <div class="border-t pt-4">
                            <div class="font-semibold mb-3">Checklist</div>

                            @if($checks->count() === 0)
                                <div class="text-sm text-gray-600">
                                    No hay checklist cargado todavía. Marca <b>“Recibí”</b> para generar los ítems de esta etapa.
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($checks as $c)
                                        <form method="POST" action="{{ route('workflow.checks.toggle', [$proceso->id, $c->check_id]) }}">
                                            @csrf
                                            <input type="hidden" name="area_role" value="{{ $areaRole }}">
                                            <button type="submit"
                                                class="w-full text-left px-3 py-2 rounded border
                                                {{ $c->checked ? 'bg-green-50' : '' }}
                                                {{ !$procesoEtapa || !$procesoEtapa->recibido ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !$procesoEtapa || !$procesoEtapa->recibido ? 'disabled' : '' }}>
                                                <span class="inline-block w-5">{{ $c->checked ? '✅' : '⬜' }}</span>
                                                <span class="font-medium">{{ $c->label }}</span>
                                                @if($c->requerido)
                                                    <span class="text-xs text-gray-500"> (requerido)</span>
                                                @endif
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- SOLICITUDES DOCUMENTALES (solo Etapa 1) --}}
                        @if(isset($solicitudesPendientes) && $solicitudesPendientes->isNotEmpty())
                        <div class="mb-6 bg-white rounded-lg shadow p-4 border border-gray-300">
                            <h3 class="text-lg font-bold mb-4 text-gray-800">📋 Documentos Solicitados a Otras Áreas</h3>
                            
                            <div class="mb-4 text-sm text-gray-600">
                                <strong>{{ $solicitudesSubidas ?? 0 }}</strong> de <strong>{{ $totalSolicitudes ?? 0 }}</strong> documentos subidos
                            </div>

                            <div class="space-y-2">
                                @foreach($solicitudesPendientes as $sol)
                                    <div class="px-3 py-2 rounded border
                                        @if($sol->estado === 'subido') bg-green-50 border-green-300
                                        @elseif($sol->puede_subir) bg-gray-50 border-gray-300
                                        @else bg-yellow-50 border-yellow-300
                                        @endif">
                                        
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    @if($sol->estado === 'subido')
                                                        <span class="text-green-600 text-lg">✅</span>
                                                    @elseif($sol->puede_subir)
                                                        <span class="text-gray-500 text-lg">⏳</span>
                                                    @else
                                                        <span class="text-yellow-600 text-lg">🔒</span>
                                                    @endif
                                                    
                                                    <span class="font-medium text-gray-800">{{ $sol->nombre_documento }}</span>
                                                </div>
                                                
                                                <div class="text-xs text-gray-600 mt-1 ml-7">
                                                    Área responsable: <strong>{{ $sol->area_responsable_nombre }}</strong>
                                                </div>

                                                @if(!$sol->puede_subir && $sol->estado !== 'subido')
                                                    <div class="text-xs text-yellow-700 mt-1 ml-7 italic">
                                                        🔒 Bloqueado hasta que suban "Compatibilidad del Gasto"
                                                    </div>
                                                @endif

                                                @if($sol->estado === 'subido' && $sol->archivo_id)
                                                    <div class="text-xs text-green-700 mt-1 ml-7">
                                                        ✓ Subido correctamente
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($solicitudesSubidas === $totalSolicitudes)
                                <div class="mt-4 p-3 bg-green-100 border border-green-400 rounded text-green-800 text-sm">
                                    ✅ <strong>¡Todos los documentos están completos!</strong> Puedes enviar el proceso a la siguiente etapa.
                                </div>
                            @else
                                <div class="mt-4 p-3 bg-blue-50 border border-blue-300 rounded text-blue-800 text-sm">
                                    ℹ️ Esperando que las áreas responsables suban los documentos solicitados.
                                </div>
                            @endif
                        </div>
                        @endif

                        {{-- ENVIÉ --}}
                        <form method="POST" action="{{ route('workflow.enviar', $proceso->id) }}" class="pt-2">
                            @csrf
                            <input type="hidden" name="area_role" value="{{ $areaRole }}">
                            <button
                                class="px-4 py-2 rounded text-white {{ $enviarHabilitado ? 'bg-gray-800' : 'bg-gray-400 cursor-not-allowed' }}"
                                {{ $enviarHabilitado ? '' : 'disabled' }}>
                                Enviar a la siguiente secretaría
                            </button>
                            @if(!$procesoEtapa || !$procesoEtapa->recibido)
                                <p class="text-xs text-red-600 mt-1">⚠ Debes marcar "Recibí" antes de enviar.</p>
                            @elseif(!$enviarHabilitado)
                                <p class="text-xs text-amber-600 mt-1">⚠ Completa todos los ítems requeridos del checklist.</p>
                            @endif
                        </form>

                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
