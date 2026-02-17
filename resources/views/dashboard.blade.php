<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>

            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('unidad_solicitante'))
                <a href="{{ route('procesos.create') }}"
                   class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                    Crear solicitud
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-3 bg-green-100 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-3 bg-red-100 rounded">{{ session('error') }}</div>
            @endif

            {{-- Procesos en curso --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="font-semibold mb-3">Procesos en curso</div>

                    @if(($enCurso ?? collect())->isEmpty())
                        <div class="text-sm text-gray-600">No tienes procesos en curso.</div>
                    @else
                        <div class="space-y-2">
                            @foreach($enCurso as $p)
                                <a class="block border rounded px-3 py-2 hover:bg-gray-50"
                                   href="{{ url('/'.$p->area_actual_role.'?proceso_id='.$p->id) }}">
                                    <div class="text-sm font-medium">{{ $p->codigo }} — {{ $p->objeto }}</div>
                                    <div class="text-xs text-gray-500">
                                        Flujo: {{ $p->workflow_nombre ?? '—' }} | Bandeja: {{ $p->area_actual_role }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Historial --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="font-semibold mb-3">Historial (finalizados)</div>

                    @if(($finalizados ?? collect())->isEmpty())
                        <div class="text-sm text-gray-600">Aún no hay procesos finalizados.</div>
                    @else
                        <div class="space-y-2">
                            @foreach($finalizados as $p)
                                <div class="block border rounded px-3 py-2">
                                    <div class="text-sm font-medium">{{ $p->codigo }} — {{ $p->objeto }}</div>
                                    <div class="text-xs text-gray-500">
                                        Flujo: {{ $p->workflow_nombre ?? '—' }} |
                                        Finalizado: {{ $p->updated_at }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
