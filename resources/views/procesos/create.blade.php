<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Proceso
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 space-y-4">

                    @if($errors->any())
                        <div class="p-3 bg-red-100 rounded">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('procesos.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium mb-1">Tipo de proceso (Workflow)</label>
                            <select name="workflow_id" class="w-full rounded border-gray-300" required>
                                <option value="">-- Selecciona --</option>
                                @foreach($workflows as $w)
                                    <option value="{{ $w->id }}" @selected(old('workflow_id') == $w->id)>
                                        {{ $w->nombre }} ({{ $w->codigo }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Código</label>
                            <input name="codigo" value="{{ old('codigo') }}"
                                   class="w-full rounded border-gray-300" placeholder="CD-2026-0001" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Objeto</label>
                            <input name="objeto" value="{{ old('objeto') }}"
                                   class="w-full rounded border-gray-300" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Descripción (opcional)</label>
                            <textarea name="descripcion" class="w-full rounded border-gray-300"
                                      rows="4">{{ old('descripcion') }}</textarea>
                        </div>

                        <button class="px-4 py-2 bg-gray-800 text-white rounded">
                            Crear
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
