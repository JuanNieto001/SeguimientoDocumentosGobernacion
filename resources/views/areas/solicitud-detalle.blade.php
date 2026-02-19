<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📄 Subir Documentos - Proceso #{{ $proceso->numero }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Información del Proceso --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Información del Proceso</h3>
                <div class="text-sm text-gray-700 space-y-1">
                    <p><strong>Número:</strong> {{ $proceso->numero }}</p>
                    <p><strong>Descripción:</strong> {{ $proceso->descripcion }}</p>
                    <p><strong>Área actual:</strong> {{ $proceso->area_actual_role }}</p>
                </div>
            </div>

            {{-- Documentos Solicitados --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    📋 Documentos que debes subir
                </h3>

                @if($solicitudes->isEmpty())
                    <p class="text-gray-600">No hay solicitudes pendientes para este proceso.</p>
                @else
                    <div class="space-y-4">
                        @foreach($solicitudes as $sol)
                            <div class="border rounded-lg p-4
                                @if($sol->estado === 'subido') border-green-300 bg-green-50
                                @elseif($sol->puede_subir) border-blue-300 bg-blue-50
                                @else border-gray-300 bg-gray-50
                                @endif">
                                
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            @if($sol->estado === 'subido')
                                                <span class="text-green-600 text-xl">✅</span>
                                            @elseif($sol->puede_subir)
                                                <span class="text-blue-600 text-xl">⏳</span>
                                            @else
                                                <span class="text-gray-500 text-xl">🔒</span>
                                            @endif
                                            
                                            <h4 class="font-bold text-gray-800">{{ $sol->nombre_documento }}</h4>
                                        </div>

                                        @if($sol->estado === 'subido')
                                            <p class="text-sm text-green-700 mb-2">
                                                ✓ Documento ya subido: <strong>{{ $sol->archivo_nombre }}</strong>
                                            </p>
                                        @elseif($sol->puede_subir)
                                            <p class="text-sm text-gray-700 mb-3">
                                                Por favor sube el documento: <strong>{{ $sol->tipo_documento }}</strong>
                                            </p>
                                            
                                            {{-- Formulario de Subida --}}
                                            <form method="POST" 
                                                  action="{{ route('workflow.files.store', $proceso->id) }}" 
                                                  enctype="multipart/form-data"
                                                  class="mt-3 border-t border-blue-200 pt-3">
                                                @csrf
                                                <input type="hidden" name="tipo_archivo" value="{{ $sol->tipo_documento }}">
                                                
                                                <div class="flex items-center gap-3">
                                                    <label class="flex-1">
                                                        <span class="text-sm font-medium text-gray-700">Seleccionar archivo:</span>
                                                        <input type="file" 
                                                               name="archivo" 
                                                               required
                                                               class="mt-1 block w-full text-sm text-gray-500
                                                                      file:mr-4 file:py-2 file:px-4
                                                                      file:rounded file:border-0
                                                                      file:text-sm file:font-semibold
                                                                      file:bg-blue-600 file:text-white
                                                                      hover:file:bg-blue-700">
                                                    </label>
                                                    
                                                    <button type="submit"
                                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                                        Subir
                                                    </button>
                                                </div>
                                            </form>
                                        @else
                                            <p class="text-sm text-gray-600 italic">
                                                🔒 Este documento está bloqueado hasta que se suba un documento prerequisito.
                                            </p>
                                        @endif

                                        @if($sol->observaciones)
                                            <div class="mt-2 p-2 bg-yellow-100 border border-yellow-300 rounded text-sm text-yellow-800">
                                                <strong>Observación:</strong> {{ $sol->observaciones }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Archivos ya Subidos --}}
            @if($archivosSubidos->isNotEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    📎 Archivos que has subido
                </h3>

                <div class="space-y-2">
                    @foreach($archivosSubidos as $archivo)
                        <div class="flex items-center justify-between p-3 border border-gray-300 rounded bg-gray-50">
                            <div>
                                <p class="font-medium text-gray-800">{{ $archivo->nombre_original }}</p>
                                <p class="text-xs text-gray-600">Tipo: {{ $archivo->tipo_archivo }} | Subido: {{ $archivo->uploaded_at }}</p>
                            </div>
                            <a href="{{ route('workflow.files.download', $archivo->id) }}" 
                               class="px-3 py-1 bg-gray-700 text-white rounded text-sm hover:bg-gray-800">
                                Descargar
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Botón Volver --}}
            <div class="mt-6">
                <a href="{{ route('solicitudes.index') }}" 
                   class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    ← Volver a Solicitudes
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
