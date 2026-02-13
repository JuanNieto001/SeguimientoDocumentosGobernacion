<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Procesos
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('procesos.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded">
                    Crear proceso
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 rounded">
                    {{ session('success') }}
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
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($procesos as $p)
                                <tr class="border-b">
                                    <td class="py-2">{{ $p->codigo }}</td>
                                    <td class="py-2">{{ $p->objeto }}</td>
                                    <td class="py-2">{{ $p->estado }}</td>
                                    <td class="py-2">{{ $p->area_actual_role }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-4" colspan="4">No hay procesos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
