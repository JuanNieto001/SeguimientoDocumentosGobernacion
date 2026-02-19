<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Permisos</h1>
                <p class="text-xs text-gray-400 mt-1">Gestión de permisos del sistema</p>
            </div>
            <a href="{{ route('admin.permisos.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-95 transition-all"
               style="background:linear-gradient(135deg,#15803d,#14532d)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Nuevo permiso
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

        @if($permissions->isEmpty())
        <div class="bg-white rounded-2xl flex flex-col items-center justify-center py-20" style="border:1px solid #e2e8f0">
            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <p class="text-sm text-gray-400 font-medium">No hay permisos registrados</p>
        </div>
        @else
            @foreach($permissions as $grupo => $permsGrupo)
            <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                <div class="px-5 py-3 flex items-center gap-2" style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $grupo }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:#dbeafe;color:#1d4ed8">
                        {{ $permsGrupo->count() }}
                    </span>
                </div>
                <table class="w-full text-sm">
                    <tbody>
                        @foreach($permsGrupo as $perm)
                        <tr style="border-bottom:1px solid #f1f5f9" class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <code class="text-xs font-mono px-2 py-1 rounded" style="background:#f1f5f9;color:#374151">{{ $perm->name }}</code>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.permisos.edit', $perm) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-100 transition-all"
                                       style="border:1px solid #e2e8f0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('admin.permisos.destroy', $perm) }}" class="inline"
                                          onsubmit="return confirm('¿Eliminar el permiso {{ $perm->name }}?')">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        @endif

    </div>
</x-app-layout>
