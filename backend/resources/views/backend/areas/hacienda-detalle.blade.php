{{-- Archivo: backend/resources/views/backend/areas/hacienda-detalle.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="breadcrumb-row text-xs text-gray-400 mb-0.5 leading-none">
                    <a href="{{ route('hacienda.index') }}" class="hover:text-blue-700 transition-colors">Hacienda</a>
                    <span class="mx-1">/</span>
                    <span class="breadcrumb-code text-gray-600 font-medium">{{ $proceso->codigo }}</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Detalle del proceso — Hacienda</h1>
            </div>
            <a href="{{ route('hacienda.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm text-gray-600 hover:bg-gray-50 transition-all"
               style="border-color:#e2e8f0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">
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

        {{-- Info del proceso --}}
        <div class="bg-white rounded-2xl border p-5" style="border-color:#e2e8f0">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ $proceso->codigo }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $proceso->objeto }}</p>
                </div>
                @php
                    $estadoConfig = [
                        'EN_CURSO'   => ['bg'=>'#dbeafe','text'=>'#1d4ed8','label'=>'En curso'],
                        'FINALIZADO' => ['bg'=>'#dcfce7','text'=>'#15803d','label'=>'Finalizado'],
                        'RECHAZADO'  => ['bg'=>'#fee2e2','text'=>'#b91c1c','label'=>'Rechazado'],
                    ];
                    $ec = $estadoConfig[$proceso->estado] ?? ['bg'=>'#f1f5f9','text'=>'#475569','label'=>$proceso->estado];
                @endphp
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold shrink-0"
                      style="background:{{ $ec['bg'] }};color:{{ $ec['text'] }}">
                    {{ $ec['label'] }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-t pt-4" style="border-color:#f1f5f9">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Flujo</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->workflow)->nombre ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Etapa actual</p>
                    <p class="font-medium text-gray-700">{{ optional($proceso->etapaActual)->nombre ?? 'N/D' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Valor estimado</p>
                    <p class="font-medium text-gray-700">$ {{ number_format($proceso->valor_estimado ?? 0, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Creado</p>
                    <p class="font-medium text-gray-700">{{ $proceso->created_at?->format('d/m/Y') ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Documentos del proceso --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📄 Documentos</h3>
            </div>
            <div class="p-5">
                @forelse($proceso->archivos as $archivo)
                <div class="flex items-center justify-between py-2 border-b last:border-0" style="border-color:#f8fafc">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $archivo->nombre_original }}</p>
                        <p class="text-xs text-gray-400">{{ $archivo->tipo_archivo }} · {{ $archivo->created_at?->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $archivo->estado === 'aprobado' ? 'bg-green-100 text-green-700' : ($archivo->estado === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ ucfirst($archivo->estado) }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-gray-400">No hay documentos cargados aún.</p>
                @endforelse
            </div>
        </div>

        {{-- Auditoría --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">📋 Historial de auditoría</h3>
            </div>
            <div class="p-5 space-y-2">
                @forelse($proceso->auditorias->take(20) as $auditoria)
                <div class="flex items-start gap-3 py-2 border-b last:border-0" style="border-color:#f8fafc">
                    <div class="w-2 h-2 rounded-full bg-blue-400 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-sm text-gray-700">{{ $auditoria->descripcion }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $auditoria->created_at?->format('d/m/Y H:i') }} · {{ optional($auditoria->usuario)->name ?? 'Sistema' }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400">Sin registros de auditoría.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

