<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                    <a href="{{ route('reportes.index') }}" class="hover:text-gray-700">Reportes</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">Certificados por vencer</span>
                </div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Certificados por vencer</h1>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
            $kpis = [
                ['label'=>'Total por vencer','val'=>$estadisticas['total'],'bg'=>'#fee2e2','text'=>'#b91c1c'],
                ['label'=>'Vencen hoy','val'=>$estadisticas['vencen_hoy'],'bg'=>'#fecaca','text'=>'#991b1b'],
                ['label'=>'Vencen mañana','val'=>$estadisticas['vencen_manana'],'bg'=>'#fed7aa','text'=>'#c2410c'],
                ['label'=>'Próximos 3 días','val'=>$estadisticas['proximos_3_dias'],'bg'=>'#fef9c3','text'=>'#854d0e'],
            ];
            @endphp
            @foreach($kpis as $k)
            <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-2" style="background:{{ $k['bg'] }}">
                    <span class="text-sm font-bold" style="color:{{ $k['text'] }}">{{ $k['val'] }}</span>
                </div>
                <p class="text-xs text-gray-400">{{ $k['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Filtro días --}}
        <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Mostrar certificados con vigencia en los próximos</label>
                    <div class="flex gap-2">
                        @foreach([3, 5, 7, 15, 30] as $d)
                        <a href="{{ route('reportes.certificados.vencer', ['dias' => $d]) }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $dias == $d ? 'text-white' : 'text-gray-600' }}"
                           style="background:{{ $dias == $d ? '#b91c1c' : '#f1f5f9' }}">{{ $d }} días</a>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabla --}}
        @if($certificados->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center" style="border:1px solid #e2e8f0">
            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background:#dcfce7">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-700">¡Sin certificados por vencer!</p>
            <p class="text-xs text-gray-400 mt-1">No hay documentos con vigencia en los próximos {{ $dias }} días.</p>
        </div>
        @else
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <table class="w-full text-xs">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Proceso</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Documento</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Etapa</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Vence</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-400 uppercase tracking-wide">Urgencia</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($certificados as $cert)
                    @php
                    $diasRestantes = now()->diffInDays($cert->fecha_vigencia, false);
                    if ($diasRestantes <= 0) {
                        $urgBg = '#fecaca'; $urgText = '#991b1b'; $urgLabel = 'Vencido';
                    } elseif ($diasRestantes <= 1) {
                        $urgBg = '#fee2e2'; $urgText = '#b91c1c'; $urgLabel = 'Hoy';
                    } elseif ($diasRestantes <= 2) {
                        $urgBg = '#fed7aa'; $urgText = '#c2410c'; $urgLabel = 'Mañana';
                    } elseif ($diasRestantes <= 3) {
                        $urgBg = '#fef9c3'; $urgText = '#854d0e'; $urgLabel = $diasRestantes.' días';
                    } else {
                        $urgBg = '#f0fdf4'; $urgText = '#15803d'; $urgLabel = $diasRestantes.' días';
                    }
                    @endphp
                    <tr style="border-bottom:1px solid #f1f5f9;background:{{ $diasRestantes <= 1 ? '#fff5f5' : 'white' }}" class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('procesos.show', $cert->proceso_id) }}" class="font-mono font-bold text-blue-600 hover:underline">
                                #{{ $cert->proceso_id }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-gray-700 max-w-xs truncate">{{ $cert->nombre_archivo ?? $cert->tipo_archivo ?? 'Documento' }}</td>
                        <td class="px-5 py-3 text-gray-400">{{ $cert->etapa->nombre ?? '-' }}</td>
                        <td class="px-5 py-3 font-semibold text-gray-700">{{ $cert->fecha_vigencia->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background:{{ $urgBg }};color:{{ $urgText }}">{{ $urgLabel }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('procesos.show', $cert->proceso_id) }}" class="text-blue-600 hover:underline text-xs">Ver proceso →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</x-app-layout>
