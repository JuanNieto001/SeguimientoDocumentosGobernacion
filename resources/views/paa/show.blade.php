<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('paa.index', ['anio' => $paa->anio]) }}" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">{{ $paa->codigo_necesidad }}</h1>
                <p class="text-xs text-gray-400 mt-1">PAA {{ $paa->anio }} — {{ $modalidades[$paa->modalidad_contratacion] ?? $paa->modalidad_contratacion }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @php
            $estadoCfg = [
                'vigente'   => ['bg'=>'#dcfce7','text'=>'#15803d'],
                'modificado'=> ['bg'=>'#fefce8','text'=>'#a16207'],
                'ejecutado' => ['bg'=>'#dbeafe','text'=>'#1d4ed8'],
                'cancelado' => ['bg'=>'#fee2e2','text'=>'#b91c1c'],
            ][$paa->estado] ?? ['bg'=>'#f1f5f9','text'=>'#475569'];
            @endphp
            <span class="px-3 py-1 text-xs font-bold rounded-full" style="background:{{ $estadoCfg['bg'] }};color:{{ $estadoCfg['text'] }}">{{ ucfirst($paa->estado) }}</span>
            <a href="{{ route('paa.certificado', $paa->id) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border transition-colors"
               style="border-color:#1d4ed8;color:#1d4ed8;background:#dbeafe">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                Certificado
            </a>
            <a href="{{ route('paa.edit', $paa->id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl text-white transition-colors"
               style="background:#166534">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Editar
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Panel izquierdo --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Datos principales --}}
                <div class="bg-white rounded-2xl p-6 space-y-4" style="border:1px solid #e2e8f0">
                    <h2 class="text-sm font-bold text-gray-800 pb-3 border-b" style="border-color:#f1f5f9">Datos de la necesidad</h2>

                    <div>
                        <p class="text-xs text-gray-400 font-medium mb-1">Descripción del objeto</p>
                        <p class="text-sm text-gray-800 leading-relaxed">{{ $paa->descripcion }}</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-2">
                        @php
                        $campos = [
                            ['label'=>'Código necesidad','value'=>$paa->codigo_necesidad,'mono'=>true],
                            ['label'=>'Vigencia','value'=>$paa->anio],
                            ['label'=>'Modalidad','value'=>$modalidades[$paa->modalidad_contratacion] ?? $paa->modalidad_contratacion],
                            ['label'=>'Trimestre estimado','value'=>$trimestres[$paa->trimestre_estimado] ?? 'T'.$paa->trimestre_estimado],
                            ['label'=>'Valor estimado','value'=>'$ '.number_format($paa->valor_estimado, 0, ',', '.'),'mono'=>true],
                            ['label'=>'Dependencia','value'=>$paa->dependencia_solicitante],
                        ];
                        @endphp
                        @foreach($campos as $c)
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-0.5">{{ $c['label'] }}</p>
                            <p class="text-sm text-gray-800 font-{{ ($c['mono'] ?? false) ? 'mono' : 'medium' }}">{{ $c['value'] }}</p>
                        </div>
                        @endforeach
                    </div>

                    <div class="pt-2">
                        <p class="text-xs text-gray-400 font-medium mb-1">Código de inclusión</p>
                        <p class="text-sm font-mono font-semibold text-green-700">CERT-PAA-{{ $paa->anio }}-{{ $paa->id }}</p>
                    </div>
                </div>

                {{-- Procesos asociados --}}
                <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                        <h2 class="text-sm font-bold text-gray-700">Procesos vinculados</h2>
                        <span class="text-xs font-semibold text-gray-400">{{ $procesos->count() }} proceso(s)</span>
                    </div>
                    @if($procesos->isEmpty())
                    <div class="flex flex-col items-center gap-2 py-10">
                        <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-xs text-gray-400">No hay procesos vinculados a esta necesidad</p>
                    </div>
                    @else
                    <div class="divide-y" style="divide-color:#f8fafc">
                        @foreach($procesos as $p)
                        <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                            <div>
                                <p class="text-sm font-semibold font-mono text-gray-800">{{ $p->codigo }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $p->objeto }}</p>
                            </div>
                            <a href="{{ route('procesos.show', $p->id) }}"
                               class="text-xs font-medium px-2.5 py-1 rounded-lg transition-colors"
                               style="background:#f0fdf4;color:#15803d">Ver →</a>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Panel derecho --}}
            <div class="space-y-4">
                <div class="bg-white rounded-2xl p-5 space-y-3" style="border:1px solid #e2e8f0">
                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wide pb-2 border-b" style="border-color:#f1f5f9">Información del registro</h3>
                    <div>
                        <p class="text-xs text-gray-400">Creado</p>
                        <p class="text-sm text-gray-700 font-medium">{{ \Carbon\Carbon::parse($paa->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Última actualización</p>
                        <p class="text-sm text-gray-700 font-medium">{{ \Carbon\Carbon::parse($paa->updated_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Estado</p>
                        <span class="inline-block mt-0.5 px-2.5 py-1 text-xs font-semibold rounded-full" style="background:{{ $estadoCfg['bg'] }};color:{{ $estadoCfg['text'] }}">{{ ucfirst($paa->estado) }}</span>
                    </div>
                </div>

                {{-- Acciones rápidas --}}
                <div class="bg-white rounded-2xl p-5 space-y-2" style="border:1px solid #e2e8f0">
                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wide pb-2 border-b" style="border-color:#f1f5f9">Acciones</h3>
                    <a href="{{ route('paa.edit', $paa->id) }}"
                       class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar necesidad
                    </a>
                    <a href="{{ route('paa.certificado', $paa->id) }}" target="_blank"
                       class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Generar certificado
                    </a>
                    <a href="{{ route('paa.index', ['anio' => $paa->anio]) }}"
                       class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Ver PAA {{ $paa->anio }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
