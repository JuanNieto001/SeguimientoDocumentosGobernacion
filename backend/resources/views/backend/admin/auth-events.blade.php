<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Log de autenticación</h1>
                <p class="text-xs text-gray-400 mt-1">Registro de ingresos, cierres y eventos de seguridad</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
            $kpis = [
                ['label'=>'Eventos hoy',        'value'=>$stats['total_hoy'],        'color'=>'#2563eb','bg'=>'#dbeafe','border'=>'#bfdbfe'],
                ['label'=>'Ingresos exitosos',  'value'=>$stats['exitosos_hoy'],     'color'=>'#15803d','bg'=>'#dcfce7','border'=>'#bbf7d0'],
                ['label'=>'Intentos fallidos',  'value'=>$stats['fallidos_hoy'],     'color'=>'#dc2626','bg'=>'#fee2e2','border'=>'#fecaca'],
                ['label'=>'Usuarios activos hoy','value'=>$stats['usuarios_activos'],'color'=>'#7c3aed','bg'=>'#ede9fe','border'=>'#ddd6fe'],
            ];
            @endphp
            @foreach($kpis as $k)
            <div class="bg-white rounded-2xl p-4" style="border:1px solid {{ $k['border'] }}">
                <p class="text-2xl font-bold" style="color:{{ $k['color'] }}">{{ $k['value'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $k['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- IPs sospechosas --}}
        @if($ipsSospechosas->isNotEmpty())
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#fecaca">
            <div class="px-5 py-3 flex items-center gap-2" style="background:#fee2e2">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p class="text-xs font-semibold text-red-700">IPs con múltiples intentos fallidos (últimas 24h)</p>
            </div>
            <div class="px-5 py-3 flex flex-wrap gap-3">
                @foreach($ipsSospechosas as $ip)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold" style="background:#fee2e2;color:#b91c1c">
                    {{ $ip->ip_address }} — {{ $ip->intentos }} intentos
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Filtros --}}
        <div class="bg-white rounded-2xl border p-4" style="border-color:#e2e8f0">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipo de evento</label>
                    <select name="event_type" class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                        <option value="">Todos</option>
                        @foreach($tipos as $t)
                        <option value="{{ $t }}" @selected(request('event_type')==$t)>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Usuario</label>
                    <select name="user_id" class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                        <option value="">Todos</option>
                        @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">IP</label>
                    <input type="text" name="ip" value="{{ request('ip') }}" placeholder="192.168.x.x"
                           class="text-sm rounded-xl px-3 py-2 border w-36 focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}"
                           class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}"
                           class="text-sm rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-green-500" style="border-color:#e2e8f0">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-white" style="background:#14532d">
                    Filtrar
                </button>
                @if(request()->hasAny(['event_type','user_id','ip','desde','hasta']))
                <a href="{{ route('admin.auth-events') }}" class="px-4 py-2 rounded-xl text-xs font-semibold border" style="border-color:#e2e8f0;color:#6b7280">
                    Limpiar
                </a>
                @endif
            </form>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#f1f5f9">
                <h3 class="text-sm font-semibold text-gray-700">Eventos de autenticación</h3>
                <span class="text-xs text-gray-400">{{ number_format($eventos->total()) }} registros</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha / Hora</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Evento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($eventos as $ev)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                {{ $ev->created_at->format('d/m/Y') }}<br>
                                <span class="text-gray-400">{{ $ev->created_at->format('H:i:s') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                      style="background:{{ $ev->bg_evento }};color:{{ $ev->color_evento }}">
                                    {{ $ev->label_evento }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                {{ $ev->user?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $ev->email ?? $ev->user?->email ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs font-mono text-gray-500">
                                {{ $ev->ip_address ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400 max-w-xs">
                                @php
                                    $ua = $ev->user_agent ?? '';
                                    // Navegador
                                    if (str_contains($ua, 'Edg/'))         $browser = 'Edge';
                                    elseif (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera'))  $browser = 'Opera';
                                    elseif (str_contains($ua, 'Chrome'))   $browser = 'Chrome';
                                    elseif (str_contains($ua, 'Firefox'))  $browser = 'Firefox';
                                    elseif (str_contains($ua, 'Safari'))   $browser = 'Safari';
                                    else $browser = null;
                                    // Sistema operativo
                                    if (str_contains($ua, 'Windows NT 10')) $os = 'Windows 10/11';
                                    elseif (str_contains($ua, 'Windows'))   $os = 'Windows';
                                    elseif (str_contains($ua, 'Mac OS X'))  $os = 'macOS';
                                    elseif (str_contains($ua, 'Android'))   $os = 'Android';
                                    elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $os = 'iOS';
                                    elseif (str_contains($ua, 'Linux'))     $os = 'Linux';
                                    else $os = null;
                                @endphp
                                @if($browser || $os)
                                    <span class="block font-medium text-gray-600">{{ $browser }}</span>
                                    <span class="block text-gray-400">{{ $os }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400">
                                No hay eventos de autenticación registrados aún.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($eventos->hasPages())
            <div class="px-5 py-4 border-t" style="border-color:#f1f5f9">
                {{ $eventos->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
