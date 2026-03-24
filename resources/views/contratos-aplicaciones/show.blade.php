<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-lg font-bold text-gray-900 leading-none">Detalle contrato de aplicación</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('contratos-aplicaciones.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-gray-600" style="background:#f1f5f9">Volver</a>
                @if(auth()->user()->hasAnyRole(['admin', 'admin_general', 'admin_secretaria']))
                    <a href="{{ route('contratos-aplicaciones.edit', $contrato) }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-white" style="background:#2563eb">Editar</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
            <dl class="grid md:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs text-gray-400">Aplicación</dt><dd class="font-semibold text-gray-800">{{ $contrato->aplicacion }}</dd></div>
                <div><dt class="text-xs text-gray-400">Número contrato</dt><dd>{{ $contrato->numero_contrato ?: 'N/A' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Proveedor</dt><dd>{{ $contrato->proveedor ?: 'N/A' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Responsable</dt><dd>{{ $contrato->responsable ?: 'N/A' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Fecha inicio</dt><dd>{{ optional($contrato->fecha_inicio)->format('Y-m-d') ?: 'N/A' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Fecha finalización</dt><dd>{{ optional($contrato->fecha_fin)->format('Y-m-d') ?: 'N/A' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Valor total</dt><dd>$ {{ number_format((float) $contrato->valor_total, 0, ',', '.') }}</dd></div>
                <div><dt class="text-xs text-gray-400">Estado</dt><dd>{{ strtoupper($contrato->estado) }}</dd></div>
                <div class="md:col-span-2"><dt class="text-xs text-gray-400">Objeto</dt><dd>{{ $contrato->objeto ?: 'N/A' }}</dd></div>
                <div><dt class="text-xs text-gray-400">SECOP ID</dt><dd>{{ $contrato->secop_proceso_id ?: 'N/A' }}</dd></div>
                <div>
                    <dt class="text-xs text-gray-400">SECOP URL</dt>
                    <dd>
                        @if($contrato->secop_url)
                            <a href="{{ $contrato->secop_url }}" target="_blank" class="text-blue-600 hover:underline">Abrir en SECOP</a>
                        @else
                            N/A
                        @endif
                    </dd>
                </div>
                <div class="md:col-span-2"><dt class="text-xs text-gray-400">Observaciones</dt><dd>{{ $contrato->observaciones ?: 'N/A' }}</dd></div>
            </dl>
        </div>
    </div>
</x-app-layout>
