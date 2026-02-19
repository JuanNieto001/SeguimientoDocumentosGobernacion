<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Editar rol: <span class="text-green-700">{{ \App\Support\RoleLabels::label($role->name) }}</span></h1>
                <p class="text-xs text-gray-400 mt-1">Modifica el nombre y los permisos del rol</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl p-8" style="border:1px solid #e2e8f0">
                <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Nombre del rol --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre del rol</label>
                        <input name="name" value="{{ old('name', $role->name) }}"
                            class="w-full rounded-xl px-3.5 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-green-500 transition-all"
                            style="border:1px solid #e2e8f0"
                            @if($role->name === 'admin') readonly @endif>
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @if($role->name === 'admin')
                        <p class="text-xs text-amber-600 mt-1">⚠ El nombre del rol <strong>admin</strong> no puede modificarse.</p>
                        @endif
                    </div>

                    {{-- Permisos --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-semibold text-gray-700">Permisos</label>
                            <span class="text-xs text-gray-400">
                                <span id="count-assigned">{{ count($assignedIds) }}</span> de {{ $permissions->flatten()->count() }} asignados
                            </span>
                        </div>

                        @if($permissions->isEmpty())
                            <p class="text-sm text-gray-400">No hay permisos registrados en el sistema.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($permissions as $grupo => $permsGrupo)
                                @php
                                    $grupoIds = $permsGrupo->pluck('id')->toArray();
                                    $allChecked = count(array_intersect($grupoIds, $assignedIds)) === count($grupoIds);
                                @endphp
                                <div class="rounded-xl overflow-hidden" style="border:1px solid #e2e8f0">
                                    <div class="flex items-center justify-between px-4 py-2.5" style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ $grupo }}</span>
                                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-gray-500 select-none">
                                            <input type="checkbox" class="rounded"
                                                   data-group="{{ $grupo }}"
                                                   @checked($allChecked)
                                                   onchange="toggleGroup('{{ $grupo }}', this.checked)">
                                            Seleccionar todos
                                        </label>
                                    </div>
                                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($permsGrupo as $perm)
                                        <label class="flex items-center gap-2.5 cursor-pointer group">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $perm->id }}"
                                                   data-group="{{ $grupo }}"
                                                   class="rounded perm-check"
                                                   @checked(in_array($perm->id, old('permissions', $assignedIds)))
                                                   onchange="updateCount()">
                                            <span class="text-sm text-gray-700 group-hover:text-gray-900 transition-colors">{{ $perm->name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif

                        @error('permissions') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-95 transition-all"
                            style="background:linear-gradient(135deg,#15803d,#14532d)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Guardar cambios
                        </button>
                        <a href="{{ route('admin.roles.index') }}"
                            class="inline-flex items-center px-6 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition-all"
                            style="border:1px solid #e2e8f0;background:#fff">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function toggleGroup(group, checked) {
        document.querySelectorAll(`input[type="checkbox"][data-group="${group}"].perm-check`)
            .forEach(cb => { cb.checked = checked; });
        updateCount();
    }

    function updateCount() {
        const total = document.querySelectorAll('input.perm-check:checked').length;
        const el = document.getElementById('count-assigned');
        if (el) el.textContent = total;
    }
    </script>
</x-app-layout>
