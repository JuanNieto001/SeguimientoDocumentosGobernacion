<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $contractProcess->process_number }} - Etapa {{ $step }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $stepInfo->step_name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @if($canAdvance && $step === $contractProcess->current_step)
                    <form method="POST" action="{{ route('contract-processes.advance', $contractProcess) }}" 
                        onsubmit="return confirm('¿Está seguro de avanzar a la siguiente etapa?')">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">
                            ✓ Avanzar a Siguiente Etapa
                        </button>
                    </form>
                @endif
                <a href="{{ route('contract-processes.index') }}" class="text-gray-600 hover:text-gray-800">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Barra de progreso --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    @for($i = 0; $i <= 9; $i++)
                        <div class="flex flex-col items-center {{ $i === $contractProcess->current_step ? 'opacity-100' : ($i < $contractProcess->current_step ? 'opacity-75' : 'opacity-40') }}">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold {{ $i < $contractProcess->current_step ? 'bg-green-500 text-white' : ($i === $contractProcess->current_step ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600') }}">
                                {{ $i }}
                            </div>
                            <span class="text-xs mt-1 text-center">Etapa {{ $i }}</span>
                        </div>
                        @if($i < 9)
                            <div class="flex-1 h-1 {{ $i < $contractProcess->current_step ? 'bg-green-500' : 'bg-gray-300' }} mx-2"></div>
                        @endif
                    @endfor
                </div>
            </div>

            {{-- Alertas de validación --}}
            @if(count($validationErrors) > 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Requisitos pendientes para avanzar:</h3>
                            <ul class="mt-2 text-sm text-yellow-700 list-disc list-inside">
                                @foreach($validationErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Panel principal --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Información de la etapa --}}
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">{{ $stepInfo->step_name }}</h3>
                        
                        @if($stepInfo->requirements)
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-700 mb-2">Requisitos de esta etapa:</h4>
                                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                    @foreach($stepInfo->requirements as $req)
                                        <li>{{ $req }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($stepInfo->notes)
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-700">{{ $stepInfo->notes }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Documentos requeridos --}}
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Documentos de esta Etapa</h3>
                            @can('uploadDocument', $contractProcess)
                                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                    + Subir Documento
                                </button>
                            @endcan
                        </div>

                        <div class="space-y-3">
                            @forelse($contractProcess->documents as $document)
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ $document->document_name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $document->document_type->getLabel() }}</p>
                                            <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                                <span>📎 {{ $document->getFileSize() }}</span>
                                                <span>👤 {{ $document->uploadedBy->name }}</span>
                                                <span>📅 {{ $document->created_at->format('d/m/Y') }}</span>
                                                @if($document->expires_at)
                                                    <span class="{{ $document->is_expired ? 'text-red-600 font-semibold' : ($document->isExpiringSoon() ? 'text-orange-600' : '') }}">
                                                        ⏰ Vence: {{ $document->expires_at->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $document->approval_status->getBadgeClass() }}">
                                                {{ $document->approval_status->getLabel() }}
                                            </span>
                                            <a href="{{ route('contract-processes.documents.download', [$contractProcess, $document]) }}" 
                                                class="text-blue-600 hover:text-blue-800">
                                                ⬇
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-8">No hay documentos cargados en esta etapa</p>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- Panel lateral --}}
                <div class="space-y-6">
                    
                    {{-- Información del proceso --}}
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Información del Proceso</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="font-medium text-gray-600">Objeto:</dt>
                                <dd class="text-gray-900">{{ $contractProcess->object }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-600">Valor Estimado:</dt>
                                <dd class="text-gray-900">${{ number_format($contractProcess->estimated_value, 0) }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-600">Plazo:</dt>
                                <dd class="text-gray-900">{{ $contractProcess->term_days }} días</dd>
                            </div>
                            @if($contractProcess->contractor_name)
                                <div>
                                    <dt class="font-medium text-gray-600">Contratista:</dt>
                                    <dd class="text-gray-900">{{ $contractProcess->contractor_name }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Responsables --}}
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Responsables</h3>
                        <dl class="space-y-2 text-sm">
                            @if($contractProcess->unitHead)
                                <div>
                                    <dt class="font-medium text-gray-600">Jefe de Unidad:</dt>
                                    <dd>{{ $contractProcess->unitHead->name }}</dd>
                                </div>
                            @endif
                            @if($contractProcess->supervisor)
                                <div>
                                    <dt class="font-medium text-gray-600">Supervisor:</dt>
                                    <dd>{{ $contractProcess->supervisor->name }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                </div>

            </div>

        </div>
    </div>

    {{-- Modal para subir documento --}}
    <div id="uploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold mb-4">Subir Documento</h3>
            <form method="POST" action="{{ route('contract-processes.documents.upload', $contractProcess) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="step_number" value="{{ $step }}">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Documento</label>
                    <select name="document_type" required class="w-full rounded-lg border-gray-300">
                        <option value="">Seleccione...</option>
                        @foreach($requiredDocuments as $docType)
                            <option value="{{ $docType->value }}">{{ $docType->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Archivo</label>
                    <input type="file" name="file" required class="w-full">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Expedición</label>
                    <input type="date" name="issued_at" class="w-full rounded-lg border-gray-300">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Subir
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
