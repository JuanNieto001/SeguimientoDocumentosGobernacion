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

                        {{-- RECIBÍ --}}
                        <form method="POST" action="{{ route('workflow.recibir', $proceso->id) }}">
                            @csrf
                            <input type="hidden" name="area_role" value="{{ $areaRole }}">
                            <button
                                class="px-4 py-2 rounded text-white {{ $procesoEtapa && $procesoEtapa->recibido ? 'bg-gray-400' : 'bg-gray-800' }}"
                                {{ $procesoEtapa && $procesoEtapa->recibido ? 'disabled' : '' }}>
                                {{ $procesoEtapa && $procesoEtapa->recibido ? 'Documento recibido' : 'Marcar como recibido' }}
                            </button>
                        </form>

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
