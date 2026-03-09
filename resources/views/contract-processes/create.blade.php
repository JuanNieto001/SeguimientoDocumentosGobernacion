<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-bold text-gray-900">Nuevo Proceso Contractual</h1>
    </x-slot>

    <div class="p-6 max-w-2xl">
        <form method="POST" action="{{ route('contract-processes.store') }}" class="bg-white rounded-2xl border p-6 space-y-5" style="border-color:#e2e8f0">
            @csrf

            <div>
                <label for="process_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de proceso</label>
                <select id="process_type" name="process_type" required
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleccione...</option>
                    @foreach ($processTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('process_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                @error('process_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="object" class="block text-sm font-medium text-gray-700 mb-1">Objeto del contrato</label>
                <textarea id="object" name="object" rows="3" required
                          class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('object') }}</textarea>
                @error('object') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="estimated_value" class="block text-sm font-medium text-gray-700 mb-1">Valor estimado</label>
                    <input type="number" id="estimated_value" name="estimated_value" value="{{ old('estimated_value') }}" step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('estimated_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="term_days" class="block text-sm font-medium text-gray-700 mb-1">Plazo (días)</label>
                    <input type="number" id="term_days" name="term_days" value="{{ old('term_days') }}" min="1"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('term_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="secretaria_id" class="block text-sm font-medium text-gray-700 mb-1">Secretaría</label>
                    <select id="secretaria_id" name="secretaria_id" required
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccione...</option>
                        @foreach ($secretarias as $sec)
                        <option value="{{ $sec->id }}" @selected(old('secretaria_id') == $sec->id)>{{ $sec->nombre }}</option>
                        @endforeach
                    </select>
                    @error('secretaria_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="unidad_id" class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                    <select id="unidad_id" name="unidad_id" required
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccione...</option>
                        @foreach ($unidades as $uni)
                        <option value="{{ $uni->id }}" @selected(old('unidad_id') == $uni->id)>{{ $uni->nombre }}</option>
                        @endforeach
                    </select>
                    @error('unidad_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="observations" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                <textarea id="observations" name="observations" rows="2"
                          class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('observations') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">Crear proceso</button>
                <a href="{{ route('contract-processes.index') }}" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm hover:bg-gray-200">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
