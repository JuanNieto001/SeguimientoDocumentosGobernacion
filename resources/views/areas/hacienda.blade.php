<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Secretaría de Hacienda
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div class="space-y-4">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" class="rounded border-gray-300">
                            <span class="font-medium">Recibí el documento</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" class="rounded border-gray-300">
                            <span class="font-medium">Envié el documento</span>
                        </label>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
