<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">{{ $contrato->nombre_aplicacion }}</h1>
                <p class="text-xs text-gray-400 mt-1">
                    Contrato de aplicación
                    @if($contrato->numero_contrato) — {{ $contrato->numero_contrato }} @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('contratos_app.editar')
                <a href="{{ route('contratos-app.edit', $contrato) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-xl text-white transition-colors"
                   style="background:#1e3a5f">
                    Editar
                </a>
                @endcan
                <a href="{{ route('contratos-app.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-xl border transition-colors"
                   style="border-color:#e2e8f0;color:#374151">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium"
             style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @php
            $estadoEfectivo = $contrato->estado_efectivo;
            $estadoColors = [
                'activo'     => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0', 'label' => 'Activo'],
                'por_vencer' => ['bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a', 'label' => 'Por vencer (≤ 30 días)'],
                'vencido'    => ['bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca', 'label' => 'Vencido'],
                'cancelado'  => ['bg' => '#f8fafc', 'text' => '#64748b', 'border' => '#e2e8f0', 'label' => 'Cancelado'],
            ];
            $sc = $estadoColors[$estadoEfectivo] ?? $estadoColors['cancelado'];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Estado --}}
            <div class="rounded-2xl p-4 shadow-sm border" style="background:{{ $sc['bg'] }};border-color:{{ $sc['border'] }}">
                <p class="text-xs font-medium" style="color:{{ $sc['text'] }}">Estado</p>
                <p class="text-xl font-bold mt-1" style="color:{{ $sc['text'] }}">{{ $sc['label'] }}</p>
                @if($estadoEfectivo === 'activo')
                <p class="text-xs mt-0.5" style="color:{{ $sc['text'] }}">{{ $contrato->dias_restantes }} días restantes</p>
                @endif
            </div>

            {{-- Vigencia --}}
            <div class="rounded-2xl p-4 shadow-sm border" style="background:#fff;border-color:#e2e8f0">
                <p class="text-xs font-medium text-gray-500">Vigencia</p>
                <p class="text-sm font-semibold text-gray-800 mt-1">
                    {{ $contrato->fecha_inicio->format('d/m/Y') }} — {{ $contrato->fecha_fin->format('d/m/Y') }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $contrato->fecha_inicio->diffInDays($contrato->fecha_fin) }} días de vigencia
                </p>
            </div>

            {{-- Valor --}}
            <div class="rounded-2xl p-4 shadow-sm border" style="background:#fff;border-color:#e2e8f0">
                <p class="text-xs font-medium text-gray-500">Valor del contrato</p>
                @if($contrato->valor_contrato)
                <p class="text-xl font-bold text-gray-800 mt-1">
                    ${{ number_format($contrato->valor_contrato, 0, ',', '.') }}
                </p>
                @else
                <p class="text-sm text-gray-400 mt-1">No especificado</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Información principal --}}
            <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Información del contrato</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Proveedor</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $contrato->proveedor ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Número de contrato</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $contrato->numero_contrato ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Modalidad de contratación</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $contrato->modalidad_contratacion ?? '—' }}</dd>
                    </div>
                    @if($contrato->descripcion)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Descripción</dt>
                        <dd class="text-sm text-gray-800 mt-0.5 whitespace-pre-line">{{ $contrato->descripcion }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- SECOP y dependencia --}}
            <div class="space-y-4">
                {{-- SECOP --}}
                <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Referencia SECOP II</h2>
                    @if($contrato->secop_id || $contrato->secop_url)
                    <dl class="space-y-3">
                        @if($contrato->secop_id)
                        <div>
                            <dt class="text-xs font-medium text-gray-500">ID en SECOP</dt>
                            <dd class="text-sm text-gray-800 mt-0.5 font-mono">{{ $contrato->secop_id }}</dd>
                        </div>
                        @endif
                        @if($contrato->secop_url)
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Enlace directo</dt>
                            <dd class="mt-0.5">
                                <a href="{{ $contrato->secop_url }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-sm font-medium"
                                   style="color:#1e40af">
                                    Ver en SECOP II
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </dd>
                        </div>
                        @endif
                    </dl>
                    @else
                    <p class="text-sm text-gray-400">No hay referencia SECOP registrada.</p>
                    @can('contratos_app.editar')
                    <a href="{{ route('contratos-app.edit', $contrato) }}"
                       class="mt-2 inline-block text-xs font-medium"
                       style="color:#1e40af">
                        Agregar referencia →
                    </a>
                    @endcan
                    @endif
                </div>

                {{-- Dependencia --}}
                <div class="rounded-2xl shadow-sm border p-5" style="background:#fff;border-color:#e2e8f0">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Dependencia responsable</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Secretaría</dt>
                            <dd class="text-sm text-gray-800 mt-0.5">{{ $contrato->secretaria?->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Unidad</dt>
                            <dd class="text-sm text-gray-800 mt-0.5">{{ $contrato->unidad?->nombre ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Auditoría --}}
        <div class="rounded-2xl shadow-sm border p-4" style="background:#fff;border-color:#e2e8f0">
            <p class="text-xs text-gray-400">
                Registrado por
                <span class="font-medium text-gray-600">{{ $contrato->creadoPor?->name ?? 'sistema' }}</span>
                el {{ $contrato->created_at->format('d/m/Y H:i') }}
                @if($contrato->actualizadoPor && $contrato->updated_at->gt($contrato->created_at))
                — Última modificación por
                <span class="font-medium text-gray-600">{{ $contrato->actualizadoPor->name }}</span>
                el {{ $contrato->updated_at->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
    </div>
</x-app-layout>
