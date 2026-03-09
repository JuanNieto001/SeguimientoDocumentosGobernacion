@php use App\Support\RoleLabels; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Gu&iacute;as de Estiven</h1>
                <p class="text-xs text-gray-400 mt-1">Administra las gu&iacute;as de ayuda del Agente Estiven</p>
            </div>
            <a href="{{ route('admin.estiven-guides.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-95 transition-all"
               style="background:linear-gradient(135deg,#15803d,#14532d)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Nueva gu&iacute;a
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

        {{-- Filtro por rol --}}
        <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-gray-600">Filtrar por rol:</label>
            <form method="GET" action="{{ route('admin.estiven-guides.index') }}" class="flex items-center gap-2">
                <select name="role" onchange="this.form.submit()"
                        class="rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500 py-1.5 px-3">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" @selected($filterRole === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Gu&iacute;a</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pasos</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Orden</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guides as $guide)
                    <tr style="border-bottom:1px solid #f1f5f9" class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ $guide->icon }}</span>
                                <p class="font-semibold text-gray-800 leading-snug">{{ $guide->title }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                {{ $guide->role === '_common' ? 'bg-blue-50 text-blue-700' : 'bg-green-50 text-green-700' }}">
                                {{ $guide->role === '_common' ? 'Común' : ($roles[$guide->role] ?? $guide->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="font-medium text-gray-700">{{ $guide->steps_count }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="text-gray-500">{{ $guide->orden }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($guide->activo)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.estiven-guides.edit', $guide) }}"
                                   class="p-2 rounded-lg hover:bg-blue-50 text-blue-600 transition-colors" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.estiven-guides.destroy', $guide) }}"
                                      onsubmit="return confirm('¿Eliminar esta guía?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-500 transition-colors" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="font-medium">No hay gu&iacute;as configuradas</p>
                            <p class="text-xs mt-1">Crea la primera gu&iacute;a para el Agente Estiven.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
