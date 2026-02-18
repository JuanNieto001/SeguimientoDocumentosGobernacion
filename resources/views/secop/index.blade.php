<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-gray-900">SECOP II</h1>
            <p class="text-xs text-gray-400 mt-0.5">Publicación, contratación y cierre de procesos</p>
        </div>
    </x-slot>

    <div class="p-6 space-y-4">

        @if(session('success'))
        <div class="flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border p-4 flex items-center gap-3" style="border-color:#e2e8f0">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#f0fdf4">
                    <svg class="w-5 h-5" fill="none" stroke="#15803d" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400">Total en bandeja</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border p-4 flex items-center gap-3" style="border-color:#e2e8f0">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#fefce8">
                    <svg class="w-5 h-5" fill="none" stroke="#ca8a04" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pendiente'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400">Pendientes</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border p-4 flex items-center gap-3" style="border-color:#e2e8f0">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#eff6ff">
                    <svg class="w-5 h-5" fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['publicado_secop'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400">Publicados</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border p-4 flex items-center gap-3" style="border-color:#e2e8f0">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#f0fdf4">
                    <svg class="w-5 h-5" fill="none" stroke="#15803d" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['en_curso'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400">En proceso</p>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-2xl border p-4 flex items-center gap-3" style="border-color:#e2e8f0">
            <span class="text-xs text-gray-400 font-medium">Filtrar:</span>
            @foreach(['pendiente'=>'Pendientes','en_curso'=>'En curso','todos'=>'Todos'] as $val => $label)
            <a href="{{ url('/secop?estado='.$val) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all"
               style="background:{{ $estado==$val?'#14532d':'#f1f5f9' }};color:{{ $estado==$val?'#fff':'#374151' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Código</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Objeto</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">N° SECOP</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Estado</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Fecha</th>
                            <th class="px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:#f8fafc">
                        @forelse($procesos as $p)
                        @php
                        $est = $p->estado ?? 'pendiente';
                        $eBg = in_array($est,['completado','cerrado']) ? '#dcfce7' : ($est=='rechazado' ? '#fee2e2' : '#dbeafe');
                        $eC  = in_array($est,['completado','cerrado']) ? '#15803d' : ($est=='rechazado' ? '#dc2626' : '#2563eb');
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 font-bold text-gray-900 whitespace-nowrap">{{ $p->codigo }}</td>
                            <td class="px-5 py-4 text-gray-600" style="max-width:16rem">
                                <span class="block truncate">{{ $p->objeto }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs font-medium text-blue-700">
                                {{ $p->numero_proceso_secop ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                      style="background:{{ $eBg }};color:{{ $eC }}">
                                    {{ ucfirst(str_replace('_',' ',$est)) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-400 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('secop.show', $p->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                   style="background:#f0fdf4;color:#15803d">
                                    Gestionar
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-gray-400 text-sm">No hay procesos en la bandeja de SECOP.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($procesos->hasPages())
            <div class="px-5 py-4 border-t" style="border-color:#f1f5f9">
                {{ $procesos->appends(['estado' => $estado])->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
