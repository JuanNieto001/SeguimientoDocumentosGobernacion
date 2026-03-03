<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Motor de Flujos</h1>
                <p class="text-xs text-gray-400 mt-0.5">Administrar flujos de contratación dinámicos por Secretaría</p>
            </div>
        </div>
    </x-slot>

    {{-- Contenedor donde React monta el WorkflowApp --}}
    <div id="motor-flujos-app"></div>

    @viteReactRefresh
    @vite('resources/js/motor-flujos.jsx')
</x-app-layout>
