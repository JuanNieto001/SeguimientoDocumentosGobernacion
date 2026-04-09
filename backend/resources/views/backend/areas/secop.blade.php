<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Mi Bandeja - SECOP</h1>
                <p class="text-xs text-gray-400 mt-1">Procesos en etapa activa de SECOP</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium"
             style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium"
             style="background:#fef2f2;border-color:#fecaca;color:#dc2626">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('error') }}
        </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

            <div class="xl:col-span-1 bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                    <h2 class="text-sm font-bold text-gray-700">Procesos en SECOP</h2>
                </div>
                <div class="p-3 space-y-2 max-h-[70vh] overflow-y-auto">
                    @forelse($procesos as $p)
                    @php $selected = $proceso && $proceso->id == $p->id; @endphp
                    <a href="{{ url('/secop?proceso_id='.$p->id) }}"
                       class="block p-3 rounded-xl border transition-all {{ $selected ? 'ring-2 ring-blue-200' : 'hover:bg-gray-50' }}"
                       style="border-color:{{ $selected ? '#bfdbfe' : '#e2e8f0' }};background:{{ $selected ? '#eff6ff' : '#fff' }}">
                        <p class="text-xs font-mono font-semibold text-gray-800 truncate">{{ $p->codigo }}</p>
                        <p class="text-sm text-gray-700 truncate mt-0.5">{{ $p->objeto }}</p>
                        <p class="text-xs text-gray-400 mt-1">Estado: {{ $p->estado }}</p>
                    </a>
                    @empty
                    <p class="text-sm text-gray-500 p-3">No hay procesos en SECOP.</p>
                    @endforelse
                </div>
            </div>

            <div class="xl:col-span-2 bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                @if($proceso)
                <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                    <div>
                        <p class="text-xs text-gray-400">Proceso seleccionado</p>
                        <h2 class="text-base font-bold text-gray-900">{{ $proceso->codigo }}</h2>
                    </div>
                    <a href="{{ route('procesos.show', $proceso->id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold"
                       style="background:#eff6ff;color:#2563eb">
                        Ver expediente
                    </a>
                </div>

                <div class="p-5 space-y-5">
                    <div class="p-4 rounded-xl" style="background:#f8fafc;border:1px solid #e2e8f0">
                        <p class="text-sm text-gray-700">{{ $proceso->objeto }}</p>
                    </div>

                    <form method="POST" action="{{ route('workflow.recibir', $proceso->id) }}">
                        @csrf
                        <input type="hidden" name="area_role" value="{{ $areaRole }}">
                        <button class="px-4 py-2 rounded-xl text-sm font-semibold text-white {{ $procesoEtapa && $procesoEtapa->recibido ? 'bg-gray-400' : 'bg-gray-800' }}"
                                {{ $procesoEtapa && $procesoEtapa->recibido ? 'disabled' : '' }}>
                            {{ $procesoEtapa && $procesoEtapa->recibido ? 'Documento recibido' : 'Marcar como recibido' }}
                        </button>
                    </form>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Checklist</h3>
                        @if($checks->count() === 0)
                        <p class="text-sm text-gray-500">No hay checklist disponible para esta etapa.</p>
                        @else
                        <div class="space-y-2">
                            @foreach($checks as $c)
                            <form method="POST" action="{{ route('workflow.checks.toggle', [$proceso->id, $c->check_id]) }}">
                                @csrf
                                <input type="hidden" name="area_role" value="{{ $areaRole }}">
                                <button type="submit"
                                        class="w-full text-left px-3 py-2 rounded-xl border text-sm {{ $c->checked ? 'bg-green-50 border-green-200' : 'bg-white border-gray-200' }} {{ !$procesoEtapa || !$procesoEtapa->recibido ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ !$procesoEtapa || !$procesoEtapa->recibido ? 'disabled' : '' }}>
                                    <span class="inline-block w-5">{{ $c->checked ? '✅' : '⬜' }}</span>
                                    <span class="font-medium text-gray-700">{{ $c->label }}</span>
                                    @if($c->requerido)
                                    <span class="text-xs text-gray-400">(requerido)</span>
                                    @endif
                                </button>
                            </form>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('workflow.enviar', $proceso->id) }}" class="pt-1">
                        @csrf
                        <input type="hidden" name="area_role" value="{{ $areaRole }}">
                        <button class="px-4 py-2 rounded-xl text-sm font-semibold text-white {{ $enviarHabilitado ? 'bg-gray-800' : 'bg-gray-400 cursor-not-allowed' }}"
                                {{ $enviarHabilitado ? '' : 'disabled' }}>
                            Finalizar proceso
                        </button>
                        @if(!$procesoEtapa || !$procesoEtapa->recibido)
                        <p class="text-xs text-red-600 mt-1">Debes marcar recibido antes de finalizar.</p>
                        @elseif(!$enviarHabilitado)
                        <p class="text-xs text-amber-700 mt-1">Completa todos los items requeridos del checklist.</p>
                        @endif
                    </form>
                </div>
                @else
                <div class="p-12 text-center text-sm text-gray-500">
                    Selecciona un proceso de la izquierda para gestionarlo.
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
