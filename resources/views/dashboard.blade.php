<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">
                    Bienvenido, {{ explode(' ', auth()->user()->name)[0] }} 👋
                </h1>
                <p class="text-xs text-gray-400 mt-1">
                    @php
                        $roleLabels = [
                            'unidad_solicitante' => 'Unidad Solicitante',
                            'planeacion'         => 'Secretaría de Planeación',
                            'hacienda'           => 'Secretaría de Hacienda',
                            'juridica'           => 'Secretaría Jurídica',
                            'secop'              => 'Grupo SECOP',
                        ];
                        $userRoleLabel = collect($roleLabels)->first(fn($label, $role) => auth()->user()->hasRole($role)) ?? 'Usuario';
                    @endphp
                    {{ $userRoleLabel }} &mdash; Gobernación de Caldas
                </p>
            </div>
        </div>
    </x-slot>

    @php
    $bandejaRoutes = [
        'unidad_solicitante' => 'unidad.index',
        'planeacion'         => 'planeacion.index',
        'hacienda'           => 'hacienda.index',
        'juridica'           => 'juridica.index',
        'secop'              => 'secop.index',
    ];
    $myBandeja = collect($bandejaRoutes)->first(fn($route, $role) => auth()->user()->hasRole($role));
    $myBandejaLabel = collect(['unidad_solicitante'=>'Unidad','planeacion'=>'Planeación','hacienda'=>'Hacienda','juridica'=>'Jurídica','secop'=>'SECOP'])
        ->first(fn($lbl, $role) => auth()->user()->hasRole($role)) ?? '';

    $enCursoCount = ($enCurso ?? collect())->count();
    $finalizadoCount = ($finalizados ?? collect())->count();
    $total = $enCursoCount + $finalizadoCount;
    @endphp

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- KPIs --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 flex items-center gap-4" style="border:1px solid #e2e8f0">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0" style="background:#dbeafe">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $total }}</p>
                    <p class="text-xs text-gray-400">Total procesos</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 flex items-center gap-4" style="border:1px solid #e2e8f0">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0" style="background:#fefce8">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $enCursoCount }}</p>
                    <p class="text-xs text-gray-400">En curso</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 flex items-center gap-4" style="border:1px solid #e2e8f0">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0" style="background:#dcfce7">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $finalizadoCount }}</p>
                    <p class="text-xs text-gray-400">Finalizados</p>
                </div>
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @if($myBandeja)
            <a href="{{ route($myBandeja) }}"
               class="flex items-center gap-3 p-4 bg-white rounded-2xl transition-all hover:shadow-md hover:-translate-y-0.5"
               style="border:1px solid #e2e8f0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#f0fdf4">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800">Mi bandeja</p>
                    <p class="text-xs text-gray-400 truncate">{{ $myBandejaLabel }}</p>
                </div>
            </a>
            @endif

            @if(auth()->user()->hasRole('planeacion') || auth()->user()->hasRole('unidad_solicitante') || auth()->user()->hasRole('admin'))
            <a href="{{ route('procesos.create') }}"
               class="flex items-center gap-3 p-4 bg-white rounded-2xl transition-all hover:shadow-md hover:-translate-y-0.5"
               style="border:1px solid #e2e8f0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#dbeafe">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Nueva solicitud</p>
                    <p class="text-xs text-gray-400">CD-PN</p>
                </div>
            </a>
            @endif

            <a href="{{ route('procesos.index') }}"
               class="flex items-center gap-3 p-4 bg-white rounded-2xl transition-all hover:shadow-md hover:-translate-y-0.5"
               style="border:1px solid #e2e8f0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#f5f3ff">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Ver procesos</p>
                    <p class="text-xs text-gray-400">Lista completa</p>
                </div>
            </a>

            @if(auth()->user()->hasRole('planeacion') || auth()->user()->hasRole('admin'))
            <a href="{{ route('paa.index') }}"
               class="flex items-center gap-3 p-4 bg-white rounded-2xl transition-all hover:shadow-md hover:-translate-y-0.5"
               style="border:1px solid #e2e8f0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#fff7ed">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Plan Anual</p>
                    <p class="text-xs text-gray-400">PAA</p>
                </div>
            </a>
            @endif
        </div>

        {{-- Procesos en curso --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <h2 class="text-sm font-bold text-gray-700">Procesos en curso</h2>
                <a href="{{ route('procesos.index') }}" class="text-xs text-green-700 hover:text-green-900 font-medium">Ver todos →</a>
            </div>

            @if(($enCurso ?? collect())->isEmpty())
            <div class="flex flex-col items-center gap-2 py-12">
                <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm text-gray-400">No tienes procesos en curso actualmente</p>
                @if(auth()->user()->hasRole('planeacion') || auth()->user()->hasRole('unidad_solicitante'))
                <a href="{{ route('procesos.create') }}" class="mt-2 text-xs font-semibold text-green-700 hover:text-green-900">+ Crear primera solicitud</a>
                @endif
            </div>
            @else
            <div class="divide-y" style="divide-color:#f8fafc">
                @foreach($enCurso as $p)
                @php
                    $areaLabels = [
                        'unidad_solicitante' => ['label'=>'Unidad','bg'=>'#eff6ff','text'=>'#1d4ed8'],
                        'planeacion'         => ['label'=>'Planeación','bg'=>'#f0fdf4','text'=>'#15803d'],
                        'hacienda'           => ['label'=>'Hacienda','bg'=>'#fefce8','text'=>'#a16207'],
                        'juridica'           => ['label'=>'Jurídica','bg'=>'#fff7ed','text'=>'#c2410c'],
                        'secop'              => ['label'=>'SECOP','bg'=>'#fdf4ff','text'=>'#7e22ce'],
                    ];
                    $ac = $areaLabels[$p->area_actual_role] ?? ['label'=>$p->area_actual_role,'bg'=>'#f1f5f9','text'=>'#475569'];
                @endphp
                <div class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" style="background:#f0fdf4">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('procesos.show', $p->id) }}" class="text-sm font-semibold text-gray-900 hover:text-green-700 transition-colors font-mono">{{ $p->codigo }}</a>
                            <p class="text-xs text-gray-500 truncate">{{ $p->objeto }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0 ml-4">
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium" style="background:{{ $ac['bg'] }};color:{{ $ac['text'] }}">{{ $ac['label'] }}</span>
                        <a href="{{ route('procesos.show', $p->id) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                           style="background:#f0fdf4;color:#15803d">
                            Ver
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Historial --}}
        @if(($finalizados ?? collect())->isNotEmpty())
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h2 class="text-sm font-bold text-gray-700">Historial (finalizados)</h2>
            </div>
            <div class="divide-y" style="divide-color:#f8fafc">
                @foreach($finalizados->take(5) as $p)
                <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                    <div class="min-w-0">
                        <a href="{{ route('procesos.show', $p->id) }}" class="text-sm font-semibold text-gray-700 font-mono hover:text-green-700">{{ $p->codigo }}</a>
                        <p class="text-xs text-gray-400 truncate">{{ $p->objeto }}</p>
                    </div>
                    <span class="text-xs text-gray-400 shrink-0 ml-4">{{ \Carbon\Carbon::parse($p->updated_at)->format('d/m/Y') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
