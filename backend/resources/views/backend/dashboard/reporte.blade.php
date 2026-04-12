{{-- Archivo: backend/resources/views/backend/dashboard/reporte.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-bold text-gray-900">Reporte General del Sistema</h1>
    </x-slot>

    <div class="p-6 space-y-6">
        {{-- Período --}}
        <div class="bg-white rounded-2xl border p-4 flex items-center gap-2 text-sm text-gray-600" style="border-color:#e2e8f0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Período: {{ optional($datos['periodo']['inicio'])->format('d/m/Y') ?? $datos['periodo']['inicio'] }}
            — {{ optional($datos['periodo']['fin'])->format('d/m/Y') ?? $datos['periodo']['fin'] }}
        </div>

        {{-- Indicadores generales --}}
        @if(!empty($datos['indicadores']))
        <section>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Indicadores Generales</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach ($datos['indicadores'] as $key => $value)
                <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                    <p class="text-xl font-bold text-gray-800">{{ is_numeric($value) ? number_format($value) : $value }}</p>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Estadísticas por área --}}
        @if(!empty($datos['por_area']))
        <section>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Por Área</h2>
            <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
                @foreach ($datos['por_area'] as $area => $stats)
                <div class="flex items-center justify-between p-4 border-b last:border-b-0" style="border-color:#f1f5f9">
                    <span class="font-medium text-gray-800">{{ ucfirst($area) }}</span>
                    <div class="flex gap-4 text-sm text-gray-600">
                        @if(is_array($stats))
                            @foreach($stats as $sk => $sv)
                            <span>{{ str_replace('_', ' ', ucfirst($sk)) }}: <strong>{{ $sv }}</strong></span>
                            @endforeach
                        @else
                            <strong>{{ $stats }}</strong>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Por etapa --}}
        @if(!empty($datos['por_etapa']))
        <section>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Distribución por Etapa</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach ($datos['por_etapa'] as $etapa => $count)
                <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ $etapa }}</p>
                    <p class="text-xl font-bold text-blue-600">{{ $count }}</p>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Cumplimiento documental --}}
        @if(!empty($datos['cumplimiento_documental']))
        <section>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Cumplimiento Documental</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach ($datos['cumplimiento_documental'] as $key => $value)
                <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                    <p class="text-xl font-bold text-gray-800">{{ is_numeric($value) ? number_format($value) : $value }}</p>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Alertas y riesgos --}}
        @if(!empty($datos['alertas_riesgos']))
        <section>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Alertas y Riesgos</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach ($datos['alertas_riesgos'] as $key => $value)
                <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                    <p class="text-xl font-bold {{ str_contains($key, 'critica') || str_contains($key, 'alta') ? 'text-red-600' : 'text-amber-600' }}">
                        {{ is_numeric($value) ? number_format($value) : $value }}
                    </p>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Eficiencia --}}
        @if(!empty($datos['eficiencia']))
        <section>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Eficiencia</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach ($datos['eficiencia'] as $key => $value)
                <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                    <p class="text-xl font-bold text-green-600">{{ is_numeric($value) ? number_format($value, 1) : $value }}</p>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</x-app-layout>

