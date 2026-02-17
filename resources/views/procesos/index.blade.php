<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Procesos
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Acciones arriba --}}
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    @if(isset($procesos))
                        Total: {{ $procesos->count() }}
                    @endif
                </div>

                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('unidad_solicitante'))
                    <a href="{{ route('procesos.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                        Crear solicitud
                    </a>
                @endif
            </div>

            @if(session('success'))
                <div class="p-3 bg-green-100 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-3 bg-red-100 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="py-2">Código</th>
                                <th class="py-2">Objeto</th>
                                <th class="py-2">Estado</th>
                                <th class="py-2">Área actual</th>
                                <th class="py-2 w-40">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($procesos as $p)
                                @php
                                    // Mapea el area_role a la ruta de bandeja
                                    $inboxUrl = match($p->area_actual_role) {
                                        'unidad_solicitante' => url('/unidad?proceso_id='.$p->id),
                                        'planeacion'         => url('/planeacion?proceso_id='.$p->id),
                                        'hacienda'           => url('/hacienda?proceso_id='.$p->id),
                                        'juridica'           => url('/juridica?proceso_id='.$p->id),
                                        'secop'              => url('/secop?proceso_id='.$p->id),
                                        default              => null,
                                    };

                                    // Solo habilita "Abrir" si el usuario tiene el rol del área actual o es admin
                                    $canOpen = auth()->user()->hasRole('admin') || auth()->user()->hasRole($p->area_actual_role);
                                @endphp

                                <tr class="border-b align-top">
                                    <td class="py-2 font-medium">{{ $p->codigo }}</td>
                                    <td class="py-2">{{ $p->objeto }}</td>
                                    <td class="py-2">{{ $p->estado }}</td>
                                    <td class="py-2">
                                        <span class="inline-flex px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs">
                                            {{ $p->area_actual_role }}
                                        </span>
                                    </td>
                                    <td class="py-2">
                                        @if($inboxUrl && $canOpen)
                                            <a href="{{ $inboxUrl }}"
                                               class="inline-flex items-center px-3 py-2 rounded bg-gray-800 text-white hover:bg-gray-700 text-xs">
                                                Abrir
                                            </a>
                                        @elseif($inboxUrl && !$canOpen)
                                            <span class="inline-flex items-center px-3 py-2 rounded bg-gray-200 text-gray-600 text-xs">
                                                Sin acceso
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-500">No disponible</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-4 text-gray-600" colspan="5">No hay procesos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
