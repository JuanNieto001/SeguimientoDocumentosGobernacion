{{-- Archivo: backend/resources/views/backend/dashboards/motor/index.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Motor de Dashboards</h1>
                <p class="text-xs text-gray-400 mt-0.5">Constructor visual de asignaciones por secretaria, rol y usuario.</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">
        @if(session('success'))
            <div class="px-4 py-3 rounded-xl text-sm font-medium" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="px-4 py-3 rounded-xl text-sm" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl p-5 text-white" style="background:linear-gradient(135deg,#0f172a,#1e3a8a);border:1px solid rgba(255,255,255,.15)">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold">Panel de configuracion tipo motor</p>
                    <p class="text-xs text-blue-100 mt-1">Configura objetivo, plantilla y tipo de grafica. Prioridad: Usuario > Secretaria > Rol.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-[11px]">
                    <span class="px-2.5 py-1 rounded-full" style="background:rgba(16,185,129,.18);border:1px solid rgba(16,185,129,.4)">Asignacion por Secretaria</span>
                    <span class="px-2.5 py-1 rounded-full" style="background:rgba(59,130,246,.18);border:1px solid rgba(59,130,246,.4)">Asignacion por Rol</span>
                    <span class="px-2.5 py-1 rounded-full" style="background:rgba(245,158,11,.18);border:1px solid rgba(245,158,11,.4)">Asignacion por Usuario</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl" style="border:1px solid #e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h2 class="text-sm font-bold text-gray-800">Objetos de dashboard (arrastrables)</h2>
                <p class="text-xs text-gray-400 mt-1">Arrastra una plantilla y sueltala sobre un bloque de asignacion para aplicar.</p>
            </div>
            <div class="p-5 grid md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($plantillas as $plantilla)
                    <div class="dashboard-template-item rounded-xl p-3 cursor-grab active:cursor-grabbing"
                         draggable="true"
                         data-dashboard-template="{{ $plantilla->id }}"
                         style="border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#f8fafc)">
                        <p class="text-sm font-semibold text-gray-800">{{ $plantilla->nombre }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $plantilla->descripcion }}</p>
                        <p class="text-[11px] text-blue-700 mt-2">{{ $plantilla->widgets->count() }} widgets</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl" style="border:1px solid #e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h2 class="text-sm font-bold text-gray-800">1) Asignacion por secretaria</h2>
                <p class="text-xs text-gray-400 mt-1">Selecciona secretarias y define plantilla + tipos de grafica para su tablero.</p>
            </div>

            <div class="p-5 space-y-3">
                @foreach($secretarias as $secretaria)
                    @php
                        $asigSec = $asignacionesSecretaria[$secretaria->id] ?? null;
                        $typesSec = $chartTypesSecretaria[$secretaria->id] ?? [];
                    @endphp
                    <form method="POST" action="{{ route('dashboards.motor.assign-secretaria') }}" class="rounded-xl p-4" style="border:1px solid #e2e8f0;background:#f8fafc">
                        @csrf
                        <input type="hidden" name="secretaria_id" value="{{ $secretaria->id }}">

                        <div class="grid lg:grid-cols-5 gap-3 items-end">
                            <div class="lg:col-span-2">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">Secretaria</label>
                                <div class="text-sm font-semibold text-gray-800">{{ $secretaria->nombre }}</div>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">Plantilla</label>
                                <select id="sec_tpl_{{ $secretaria->id }}" name="dashboard_plantilla_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#d1d5db">
                                    <option value="">-- Sin asignacion (usa rol) --</option>
                                    @foreach($plantillas as $plantilla)
                                        <option value="{{ $plantilla->id }}" @selected(($asigSec->dashboard_plantilla_id ?? null) == $plantilla->id)>
                                            {{ $plantilla->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="dashboard-drop-zone mt-2 rounded-lg px-3 py-2 text-xs text-blue-700"
                                     data-drop-template-select="sec_tpl_{{ $secretaria->id }}"
                                     style="border:1px dashed #93c5fd;background:#eff6ff">
                                    Suelta aqui una plantilla para {{ $secretaria->nombre }}
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="w-full px-3 py-2 rounded-xl text-white text-sm font-semibold" style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">
                                    Guardar
                                </button>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-3 gap-3 mt-3">
                            @foreach($chartTypeOptions as $metric => $options)
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">{{ str_replace('_', ' ', $metric) }}</label>
                                    <select name="chart_types_secretaria[{{ $metric }}]" class="w-full rounded-lg border px-3 py-2 text-xs" style="border-color:#d1d5db">
                                        <option value="">Default</option>
                                        @foreach($options as $opt)
                                            <option value="{{ $opt }}" @selected(($typesSec[$metric] ?? '') === $opt)>{{ strtoupper($opt) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </form>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl" style="border:1px solid #e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h2 class="text-sm font-bold text-gray-800">2) Asignacion por usuario, perfil o rol</h2>
                <p class="text-xs text-gray-400 mt-1">Busca por nombre/correo/rol y asigna dashboards personalizados.</p>
            </div>

            <form method="GET" action="{{ route('dashboards.motor.index') }}" class="p-5">
                <div class="flex flex-col md:flex-row gap-3">
                    <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Ej: secretario, jefe_unidad, correo@..."
                           class="flex-1 rounded-xl border px-3 py-2 text-sm" style="border-color:#d1d5db">
                    <button type="submit" class="px-4 py-2 rounded-xl text-white text-sm font-semibold" style="background:#0ea5e9">Buscar</button>
                </div>
            </form>

            @if($buscar !== '')
                <div class="px-5 pb-5 space-y-3">
                    @forelse($usuariosEncontrados as $usuario)
                        @php
                            $asigUser = $asignacionesUsuario[$usuario->id] ?? null;
                            $typesUser = $asigUser->config_json['chart_types'] ?? [];
                        @endphp
                        <form method="POST" action="{{ route('dashboards.motor.assign-user') }}" class="rounded-xl p-4" style="border:1px solid #e2e8f0;background:#f8fafc">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $usuario->id }}">

                            <div class="grid lg:grid-cols-5 gap-3 items-end">
                                <div class="lg:col-span-2">
                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">Usuario</label>
                                    <p class="text-sm font-semibold text-gray-800">{{ $usuario->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $usuario->email }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Roles: {{ $usuario->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</p>
                                </div>

                                <div class="lg:col-span-2">
                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">Plantilla personalizada</label>
                                    <select id="usr_tpl_{{ $usuario->id }}" name="dashboard_plantilla_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#d1d5db">
                                        <option value="">-- Heredar por secretaria/rol --</option>
                                        @foreach($plantillas as $plantilla)
                                            <option value="{{ $plantilla->id }}" @selected(($asigUser->dashboard_plantilla_id ?? null) == $plantilla->id)>
                                                {{ $plantilla->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="dashboard-drop-zone mt-2 rounded-lg px-3 py-2 text-xs text-emerald-700"
                                         data-drop-template-select="usr_tpl_{{ $usuario->id }}"
                                         style="border:1px dashed #6ee7b7;background:#ecfdf5">
                                        Suelta aqui una plantilla para {{ $usuario->name }}
                                    </div>
                                </div>

                                <div>
                                    <button type="submit" class="w-full px-3 py-2 rounded-xl text-white text-sm font-semibold" style="background:linear-gradient(135deg,#15803d,#14532d)">
                                        Guardar
                                    </button>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-3 gap-3 mt-3">
                                @foreach($chartTypeOptions as $metric => $options)
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">{{ str_replace('_', ' ', $metric) }}</label>
                                        <select name="chart_types_usuario[{{ $metric }}]" class="w-full rounded-lg border px-3 py-2 text-xs" style="border-color:#d1d5db">
                                            <option value="">Default</option>
                                            @foreach($options as $opt)
                                                <option value="{{ $opt }}" @selected(($typesUser[$metric] ?? '') === $opt)>{{ strtoupper($opt) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    @empty
                        <div class="rounded-xl p-4 text-sm text-gray-500" style="border:1px solid #e2e8f0;background:#f8fafc">
                            No se encontraron usuarios para el criterio ingresado.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl" style="border:1px solid #e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h2 class="text-sm font-bold text-gray-800">3) Asignacion por rol</h2>
                <p class="text-xs text-gray-400 mt-1">Configura dashboards base para Gobernador, Secretario y Jefe de Unidad.</p>
            </div>

            <form method="POST" action="{{ route('dashboards.motor.assign') }}" class="p-5 space-y-3">
                @csrf
                @foreach($roles as $role)
                    @php $typesRol = $chartTypesRol[$role->name] ?? []; @endphp
                    <div class="rounded-xl p-4" style="border:1px solid #e2e8f0;background:#f8fafc">
                        <div class="grid lg:grid-cols-5 gap-3 items-end">
                            <div class="lg:col-span-2">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">Rol</label>
                                <p class="text-sm font-semibold text-gray-800">{{ \App\Support\RoleLabels::label($role->name) }}</p>
                                <p class="text-xs text-gray-500">{{ $role->name }}</p>
                            </div>
                            <div class="lg:col-span-3">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">Plantilla</label>
                                <select id="role_tpl_{{ $role->name }}" name="asignaciones[{{ $role->name }}]" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#d1d5db">
                                    <option value="">-- Sin asignacion --</option>
                                    @foreach($plantillas as $plantilla)
                                        <option value="{{ $plantilla->id }}" @selected(($asignaciones[$role->name] ?? null) == $plantilla->id)>
                                            {{ $plantilla->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="dashboard-drop-zone mt-2 rounded-lg px-3 py-2 text-xs text-violet-700"
                                     data-drop-template-select="role_tpl_{{ $role->name }}"
                                     style="border:1px dashed #c4b5fd;background:#f5f3ff">
                                    Suelta aqui una plantilla para {{ \App\Support\RoleLabels::label($role->name) }}
                                </div>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-3 gap-3 mt-3">
                            @foreach($chartTypeOptions as $metric => $options)
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">{{ str_replace('_', ' ', $metric) }}</label>
                                    <select name="chart_types_role[{{ $role->name }}][{{ $metric }}]" class="w-full rounded-lg border px-3 py-2 text-xs" style="border-color:#d1d5db">
                                        <option value="">Default</option>
                                        @foreach($options as $opt)
                                            <option value="{{ $opt }}" @selected(($typesRol[$metric] ?? '') === $opt)>{{ strtoupper($opt) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div>
                    <button type="submit" class="px-4 py-2 rounded-xl text-white text-sm font-semibold" style="background:linear-gradient(135deg,#15803d,#14532d)">
                        Guardar asignaciones por rol
                    </button>
                </div>
            </form>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            @foreach($plantillas as $plantilla)
                <div class="bg-white rounded-2xl p-5" style="border:1px solid #e2e8f0">
                    <h3 class="text-sm font-bold text-gray-800">{{ $plantilla->nombre }}</h3>
                    <p class="text-xs text-gray-400 mt-1">{{ $plantilla->descripcion }}</p>
                    <div class="mt-3 space-y-2">
                        @foreach($plantilla->widgets as $widget)
                            <div class="text-xs px-3 py-2 rounded-lg" style="background:#f8fafc;border:1px solid #e2e8f0">
                                <strong>{{ $widget->titulo }}</strong>
                                <span class="text-gray-500">({{ $widget->tipo }} / {{ $widget->metrica }})</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl" style="border:1px solid #e2e8f0">
            <div class="px-5 py-4 border-b" style="border-color:#f1f5f9">
                <h2 class="text-sm font-bold text-gray-800">Historial de asignaciones</h2>
                <p class="text-xs text-gray-400 mt-1">Registro de cambios para auditoria (rol, usuario y secretaria).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="background:#f8fafc">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Actor</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Objetivo</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Accion</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500">Cambio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:#f1f5f9">
                        @forelse($historialAsignaciones as $item)
                            <tr>
                                <td class="px-4 py-3 text-xs text-gray-600">{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ $item->actor->name ?? 'Sistema' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">
                                    @if($item->tipo_objetivo === 'rol')
                                        Rol: {{ $item->role_name }}
                                    @elseif($item->tipo_objetivo === 'secretaria')
                                        Secretaria: {{ $item->metadata['secretaria_nombre'] ?? 'N/A' }}
                                    @else
                                        Usuario: {{ $item->targetUser->name ?? 'N/A' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ strtoupper($item->accion) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ $item->plantillaAnterior->nombre ?? 'Sin asignacion' }} -> {{ $item->plantillaNueva->nombre ?? 'Sin asignacion' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">Aun no hay cambios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .dashboard-template-item {
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .dashboard-template-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(30,64,175,.12);
        }
        .dashboard-drop-zone.drop-over {
            border-style: solid !important;
            box-shadow: inset 0 0 0 2px rgba(37,99,235,.25);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const draggableTemplates = document.querySelectorAll('[data-dashboard-template]');
            const dropZones = document.querySelectorAll('[data-drop-template-select]');

            draggableTemplates.forEach(function (item) {
                item.addEventListener('dragstart', function (event) {
                    const templateId = item.getAttribute('data-dashboard-template');
                    event.dataTransfer.setData('text/dashboard-template-id', templateId);
                    event.dataTransfer.effectAllowed = 'copy';
                });
            });

            dropZones.forEach(function (zone) {
                zone.addEventListener('dragover', function (event) {
                    event.preventDefault();
                    zone.classList.add('drop-over');
                });

                zone.addEventListener('dragleave', function () {
                    zone.classList.remove('drop-over');
                });

                zone.addEventListener('drop', function (event) {
                    event.preventDefault();
                    zone.classList.remove('drop-over');

                    const templateId = event.dataTransfer.getData('text/dashboard-template-id');
                    const selectId = zone.getAttribute('data-drop-template-select');
                    const select = document.getElementById(selectId);
                    if (!templateId || !select) return;

                    select.value = templateId;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        });
    </script>
</x-app-layout>

