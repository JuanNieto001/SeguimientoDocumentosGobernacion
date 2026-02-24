<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Unidad Solicitante</h1>
                <p class="text-xs text-gray-400 mt-0.5">Documentos radicados y en trámite</p>
            </div>
            <a href="{{ route('unidad.crear') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition-all"
               style="background:#14532d">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva solicitud
            </a>
        </div>
    </x-slot>

    <div class="p-6 max-w-5xl mx-auto space-y-4">

        @if(session('success'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('error') }}
        </div>
        @endif

        {{-- Cabecera de sección --}}
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Procesos en bandeja ({{ $procesos->count() }})
            </p>
        </div>

        {{-- Lista de procesos --}}
        @forelse($procesos as $p)
        @php
            $esCompletado = in_array($p->estado, ['completado', 'cerrado']);
            $esRechazado  = $p->estado === 'rechazado';
            $colorBadgeBg = $esCompletado ? '#dcfce7' : ($esRechazado ? '#fee2e2' : '#dbeafe');
            $colorBadgeTx = $esCompletado ? '#15803d' : ($esRechazado ? '#dc2626'  : '#2563eb');
        @endphp
        <a href="{{ route('unidad.show', $p->id) }}"
           class="block bg-white rounded-2xl border transition-all hover:shadow-md hover:border-green-200 group"
           style="border-color:#e2e8f0">
            <div class="p-4 flex items-center gap-4">

                {{-- Ícono de etapa --}}
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-base"
                     style="background:{{ $esCompletado ? '#dcfce7' : ($esRechazado ? '#fee2e2' : '#dbeafe') }}">
                    {{ $esCompletado ? '✅' : ($esRechazado ? '❌' : '📄') }}
                </div>

                {{-- Info principal --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-bold text-gray-900">{{ $p->codigo }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                              style="background:{{ $colorBadgeBg }};color:{{ $colorBadgeTx }}">
                            {{ strtoupper(str_replace('_', ' ', $p->estado)) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 truncate mt-0.5">{{ $p->objeto }}</p>
                    <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                        <span class="text-xs text-gray-400">
                            <span class="font-medium text-gray-600">Etapa:</span>
                            {{ $p->etapa_nombre ?? 'N/D' }}
                        </span>
                        @if($p->valor_estimado)
                        <span class="text-xs text-gray-400">
                            <span class="font-medium text-gray-600">Valor:</span>
                            $ {{ number_format($p->valor_estimado, 0, ',', '.') }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Fecha + flecha --}}
                <div class="shrink-0 text-right hidden sm:block">
                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</p>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-green-500 transition-colors mt-2 ml-auto"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>
        @empty
        <div class="bg-white rounded-2xl border flex flex-col items-center justify-center py-20" style="border-color:#e2e8f0">
            <svg class="w-12 h-12 mb-4" fill="none" stroke="#d1d5db" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-400 text-sm font-medium">No hay procesos en tu bandeja</p>
            <a href="{{ route('unidad.crear') }}"
               class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
               style="background:#14532d">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Crear primera solicitud
            </a>
        </div>
        @endforelse

    </div>
</x-app-layout>
