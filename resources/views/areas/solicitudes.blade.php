<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📋 Solicitudes de Documentos - {{ $areaName }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if($totalSolicitudes === 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-center text-gray-600">
                        <p class="text-lg">✅ No tienes solicitudes pendientes</p>
                        <p class="text-sm mt-2">Cuando otra área solicite documentos de {{ $areaName }}, aparecerán aquí.</p>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-800">
                            📥 Tienes {{ $totalSolicitudes }} solicitud(es) pendiente(s)
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Otras áreas están esperando que subas los siguientes documentos:
                        </p>
                    </div>

                    @foreach($solicitudesPorProceso as $procesoId => $solicitudesProceso)
                        @php
                            $primeraSolicitud = $solicitudesProceso->first();
                        @endphp
                        
                        <div class="border border-gray-300 rounded-lg p-4 mb-4 bg-gray-50">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-bold text-gray-800">
                                        Proceso #{{ $primeraSolicitud->proceso_numero }}
                                    </h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $primeraSolicitud->proceso_descripcion }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Etapa: {{ $primeraSolicitud->etapa_nombre }} ({{ $primeraSolicitud->proceso_area_actual }})
                                    </p>
                                </div>
                                <a href="{{ route('solicitudes.detalle', $procesoId) }}" 
                                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    Ver Detalle
                                </a>
                            </div>

                            <div class="border-t border-gray-300 pt-3 mt-3">
                                <p class="text-sm font-semibold text-gray-700 mb-2">
                                    Documentos solicitados:
                                </p>
                                <div class="space-y-2">
                                    @foreach($solicitudesProceso as $sol)
                                        <div class="flex items-center gap-2 text-sm">
                                            @if($sol->estado === 'subido')
                                                <span class="text-green-600">✅</span>
                                                <span class="text-gray-600">{{ $sol->nombre_documento }}</span>
                                                <span class="text-xs text-green-700 italic">(completado)</span>
                                            @elseif($sol->puede_subir)
                                                <span class="text-yellow-600">⏳</span>
                                                <span class="text-gray-800 font-medium">{{ $sol->nombre_documento }}</span>
                                                <span class="text-xs text-yellow-700 italic">(pendiente)</span>
                                            @else
                                                <span class="text-red-600">🔒</span>
                                                <span class="text-gray-500">{{ $sol->nombre_documento }}</span>
                                                <span class="text-xs text-red-700 italic">(bloqueado)</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
