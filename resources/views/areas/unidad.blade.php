<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Unidad Solicitante
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
            @if($errors->any())
                <div class="p-3 bg-red-100 rounded">
                    <div class="font-semibold mb-1">Hay errores:</div>
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Lista procesos --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6">

                    {{-- Header + botón crear --}}
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">Procesos en Unidad Solicitante</div>

                        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('unidad_solicitante'))
                            <a href="{{ route('procesos.create') }}"
                               class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                                Crear solicitud
                            </a>
                        @endif
                    </div>

                    <div class="mt-4">
                        @forelse($procesos as $p)
                            <a class="block px-3 py-2 rounded border mb-2 {{ $proceso && $proceso->id == $p->id ? 'bg-gray-100' : '' }}"
                               href="{{ url('/unidad?proceso_id='.$p->id) }}">
                                <div class="text-sm font-medium">{{ $p->codigo }} — {{ $p->objeto }}</div>
                                <div class="text-xs text-gray-500">Estado: {{ $p->estado }}</div>
                            </a>
                        @empty
                            <div class="text-sm text-gray-600">No hay procesos en esta unidad.</div>
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

                        {{-- ✅ SOPORTES INICIALES (Unidad) --}}
                        @php
                            $archivos = collect();
                            $tieneFormato = false;
                            $tieneBorrador = false;

                            if ($procesoEtapa) {
                                $archivos = \DB::table('proceso_etapa_archivos')
                                    ->where('proceso_etapa_id', $procesoEtapa->id)
                                    ->orderByDesc('id')
                                    ->get();

                                $tieneFormato = \DB::table('proceso_etapa_archivos')
                                    ->where('proceso_etapa_id', $procesoEtapa->id)
                                    ->where('tipo', 'formato_necesidades')
                                    ->exists();

                                $tieneBorrador = \DB::table('proceso_etapa_archivos')
                                    ->where('proceso_etapa_id', $procesoEtapa->id)
                                    ->where('tipo', 'borrador_estudios')
                                    ->exists();
                            }

                            // ✅ Reglas Unidad:
                            // obligatorio: formato_necesidades + borrador_estudios
                            // opcional: anexo
                            $soportesOk = $tieneFormato && $tieneBorrador;
                        @endphp

                        <div class="border-t pt-4">
                            <div class="font-semibold mb-2">Soportes iniciales</div>

                            <div class="text-sm text-gray-600 mb-3">
                                Carga estos soportes para poder enviar:
                                <b>Formato de necesidades</b> y <b>Borrador de estudios previos</b>.
                                <span class="text-gray-500">(Anexos opcional)</span>
                            </div>

                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-xs px-2 py-1 rounded {{ $tieneFormato ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $tieneFormato ? 'Formato necesidades: OK' : 'Formato necesidades: pendiente' }}
                                </span>

                                <span class="text-xs px-2 py-1 rounded {{ $tieneBorrador ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $tieneBorrador ? 'Borrador estudios: OK' : 'Borrador estudios: pendiente' }}
                                </span>

                                <span class="text-xs px-2 py-1 rounded {{ $soportesOk ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $soportesOk ? 'Listo para enviar' : 'Aún no puedes enviar' }}
                                </span>
                            </div>

                            {{-- Form subir archivos --}}
                            <form method="POST"
                                  action="{{ route('workflow.archivos.store', $proceso->id) }}"
                                  enctype="multipart/form-data"
                                  class="space-y-3">
                                @csrf

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipo de archivo</label>
                                    <select name="tipo" class="mt-1 w-full border rounded px-3 py-2" required>
                                        <option value="formato_necesidades">Formato de necesidades</option>
                                        <option value="borrador_estudios">Borrador estudios previos</option>
                                        <option value="anexo">Anexo</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Archivos</label>
                                    <input type="file"
                                           name="files[]"
                                           class="mt-1 w-full border rounded px-3 py-2"
                                           multiple
                                           required>
                                    <div class="text-xs text-gray-500 mt-1">Puedes subir varios a la vez.</div>
                                </div>

                                <button type="submit"
                                        class="px-4 py-2 rounded text-white bg-gray-800 hover:bg-gray-700">
                                    Subir archivos
                                </button>
                            </form>

                            {{-- Listado de archivos --}}
                            <div class="mt-5">
                                <div class="font-semibold mb-2 text-sm">Archivos cargados</div>

                                @if($archivos->isEmpty())
                                    <div class="text-sm text-gray-600">Aún no hay archivos.</div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($archivos as $a)
                                            <div class="flex items-center justify-between border rounded px-3 py-2">
                                                <div class="text-sm">
                                                    <span class="text-xs px-2 py-1 rounded bg-gray-100 mr-2">{{ $a->tipo }}</span>
                                                    <span class="font-medium">{{ $a->nombre_original }}</span>
                                                </div>

                                                <div class="flex items-center gap-3 text-sm">
                                                    <a href="{{ route('workflow.archivos.download', [$proceso->id, $a->id]) }}"
                                                       class="text-blue-600 hover:underline">
                                                        Descargar
                                                    </a>

                                                    <form method="POST"
                                                          action="{{ route('workflow.archivos.destroy', [$proceso->id, $a->id]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                onclick="return confirm('¿Eliminar archivo?')"
                                                                class="text-red-600 hover:underline">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- ✅ ENVIAR (Unidad): solo depende de soportes --}}
                        <form method="POST" action="{{ route('workflow.enviar', $proceso->id) }}" class="pt-2">
                            @csrf

                            @if(!$soportesOk)
                                <div class="text-sm text-red-600 mb-2">
                                    No puedes enviar hasta cargar: <b>Formato de necesidades</b> y <b>Borrador de estudios previos</b>.
                                </div>
                            @endif

                            <button class="px-4 py-2 rounded text-white {{ $soportesOk ? 'bg-gray-800 hover:bg-gray-700' : 'bg-gray-400' }}"
                                    {{ $soportesOk ? '' : 'disabled' }}>
                                Enviar a la siguiente secretaría
                            </button>
                        </form>

                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
