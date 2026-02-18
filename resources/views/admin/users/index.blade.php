<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Usuarios</h1>
                <p class="text-xs text-gray-400 mt-1">Gestión de cuentas y roles del sistema</p>
            </div>
            <div class="ml-8">
                <a href="{{ route('admin.usuarios.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold shadow-sm hover:shadow-md hover:opacity-95 transition-all"
                   style="background:linear-gradient(135deg,#15803d,#14532d)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Nuevo usuario
                </a>
            </div>
        </div>
    </x-slot>

    @php
    $roleColors = [
        'admin'              => ['bg'=>'#fef2f2','text'=>'#b91c1c'],
        'unidad_solicitante' => ['bg'=>'#eff6ff','text'=>'#1d4ed8'],
        'planeacion'         => ['bg'=>'#f0fdf4','text'=>'#15803d'],
        'hacienda'           => ['bg'=>'#fefce8','text'=>'#a16207'],
        'juridica'           => ['bg'=>'#fff7ed','text'=>'#c2410c'],
        'secop'              => ['bg'=>'#fdf4ff','text'=>'#7e22ce'],
    ];
    @endphp

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    @php
                        $rol = $u->roles->pluck('name')->first() ?? null;
                        $rc = $roleColors[$rol] ?? ['bg'=>'#f1f5f9','text'=>'#475569'];
                    @endphp
                    <tr style="border-bottom:1px solid #f1f5f9" class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0" style="background:linear-gradient(135deg,#15803d,#14532d)">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500">{{ $u->email }}</td>
                        <td class="px-5 py-3.5">
                            @if($rol)
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium" style="background:{{ $rc['bg'] }};color:{{ $rc['text'] }}">
                                {{ $rol }}
                            </span>
                            @else
                            <span class="text-gray-400 text-xs">Sin rol</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.usuarios.edit', $u) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-100 transition-all"
                                   style="border:1px solid #e2e8f0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" class="inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all hover:bg-red-50"
                                        style="border:1px solid #fee2e2;background:#fff;color:#b91c1c">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="text-sm text-gray-400 font-medium">No hay usuarios registrados</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($users->hasPages())
            <div class="px-5 py-3 border-t" style="border-color:#e2e8f0">
                {{ $users->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
