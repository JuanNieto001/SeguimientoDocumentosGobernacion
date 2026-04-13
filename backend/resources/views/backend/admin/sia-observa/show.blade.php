{{-- Archivo: backend/resources/views/backend/admin/sia-observa/show.blade.php | Proposito: Gestion de accesos SIA Observa por proceso. --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Gestion SIA Observa</h1>
                <p class="text-xs text-gray-400 mt-1">Proceso {{ $proceso->codigo }}</p>
            </div>
            <a href="{{ route('admin.sia-observa.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100"
               style="border:1px solid #e2e8f0">
                Volver a bandeja
            </a>
        </div>
    </x-slot>

    @php
        $etapaOrden = (int) (optional($proceso->etapaActual)->orden ?? 0);
        $habilitado = strtoupper((string) $proceso->estado) === 'FINALIZADO' || $etapaOrden >= 8;
    @endphp

    <div class="p-6 space-y-5" style="background:#f1f5f9;min-height:calc(100vh - 65px)">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="px-4 py-3 rounded-xl border text-sm" style="background:#fef2f2;border-color:#fecaca;color:#991b1b">
            <p class="font-semibold mb-1">Se encontraron errores en la solicitud:</p>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                <div>
                    <p class="text-xs text-gray-400">Codigo proceso</p>
                    <p class="font-mono font-semibold text-gray-800 mt-1">{{ $proceso->codigo }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Contratista</p>
                    <p class="font-medium text-gray-700 mt-1">{{ $proceso->contratista_nombre ?: 'Sin nombre' }}</p>
                    <p class="text-xs text-gray-400">CC: {{ $proceso->contratista_documento ?: 'Sin cedula' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Contrato / SECOP</p>
                    <p class="font-medium text-gray-700 mt-1">{{ $proceso->numero_contrato ?: 'Sin contrato' }}</p>
                    <p class="text-xs text-gray-400">{{ $proceso->secop_codigo ?: ($proceso->numero_proceso_secop ?: 'Sin SECOP') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Estado de habilitacion</p>
                    @if($habilitado)
                        <span class="inline-flex mt-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#dcfce7;color:#15803d">Habilitado para repositorio</span>
                    @else
                        <span class="inline-flex mt-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#fef9c3;color:#a16207">Solo asignacion (etapa pendiente)</span>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">Etapa {{ $etapaOrden ?: 'N/A' }} - {{ optional($proceso->etapaActual)->nombre ?: 'Sin etapa' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                <h2 class="text-sm font-semibold text-gray-800">Asignar acceso por rol</h2>
                <p class="text-xs text-gray-400 mt-1">Permite dejar configurado el acceso para que el rol pueda consultar o subir documentos.</p>

                <form method="POST" action="{{ route('admin.sia-observa.accesos.rol', ['proceso' => $proceso->id]) }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1">Rol</label>
                        <select name="role_name" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                                style="background:#f8fafc">
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->name }}">{{ \App\Support\RoleLabels::label($rol->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <label class="inline-flex items-center gap-2 text-gray-600">
                            <input type="checkbox" name="puede_ver" value="1" checked class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Puede ver
                        </label>
                        <label class="inline-flex items-center gap-2 text-gray-600">
                            <input type="checkbox" name="puede_subir" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Puede subir
                        </label>
                        <label class="inline-flex items-center gap-2 text-gray-600">
                            <input type="checkbox" name="activo" value="1" checked class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Activo
                        </label>
                    </div>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-semibold shadow-sm hover:shadow-md hover:opacity-95 transition-all"
                            style="background:linear-gradient(135deg,#15803d,#14532d)">
                        Guardar acceso por rol
                    </button>
                </form>
            </div>

            <div x-data="{
                    secretariaId: '{{ request('user_secretaria_id') }}',
                    unidades: {!! isset($unidadesUsuarios) ? $unidadesUsuarios->toJson() : '[]' !!},
                    async fetchUnidades() {
                        if (!this.secretariaId) { this.unidades = []; return; }
                        try {
                            const res = await fetch('/api/secretarias/' + this.secretariaId + '/unidades');
                            this.unidades = await res.json();
                        } catch(e) {
                            this.unidades = [];
                        }
                    }
                }"
                class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                <h2 class="text-sm font-semibold text-gray-800">Asignar acceso por usuario</h2>
                <p class="text-xs text-gray-400 mt-1">Busca usuarios activos para asignar permisos puntuales a este proceso.</p>

                <form method="GET" action="{{ route('admin.sia-observa.show', ['proceso' => $proceso->id]) }}" class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-2">
                    <input type="text" name="user_q" value="{{ request('user_q') }}" placeholder="Nombre o correo"
                           class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                           style="background:#f8fafc">

                    <select name="user_secretaria_id" x-model="secretariaId" @change="fetchUnidades(); $refs.userUnidad.value = ''"
                            class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                            style="background:#f8fafc">
                        <option value="">Todas las secretarias</option>
                        @foreach($secretariasUsuarios as $sec)
                            <option value="{{ $sec->id }}" {{ request('user_secretaria_id') == $sec->id ? 'selected' : '' }}>{{ $sec->nombre }}</option>
                        @endforeach
                    </select>

                    <select name="user_unidad_id" x-ref="userUnidad" :disabled="!secretariaId"
                            class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                            style="background:#f8fafc">
                        <option value="">Todas las unidades</option>
                        <template x-for="u in unidades" :key="u.id">
                            <option :value="u.id" x-text="u.nombre" :selected="u.id == {{ request('user_unidad_id', 0) }}"></option>
                        </template>
                    </select>

                    <div class="md:col-span-3 flex items-center gap-2">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-white text-xs font-semibold shadow-sm hover:opacity-95 transition-all"
                                style="background:linear-gradient(135deg,#15803d,#14532d)">
                            Buscar usuarios
                        </button>
                        @if(request()->hasAny(['user_q', 'user_secretaria_id', 'user_unidad_id']))
                        <a href="{{ route('admin.sia-observa.show', ['proceso' => $proceso->id]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-gray-600 hover:bg-gray-100 transition-all"
                           style="border:1px solid #e2e8f0">
                            Limpiar busqueda
                        </a>
                        @endif
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.sia-observa.accesos.usuario', ['proceso' => $proceso->id]) }}" class="mt-3 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1">Usuario</label>
                        <select name="user_id" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                                style="background:#f8fafc">
                            <option value="">Seleccione un usuario</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }} - {{ $u->email }}
                                    @if($u->secretaria)
                                        ({{ $u->secretaria->nombre }}@if($u->unidad) / {{ $u->unidad->nombre }}@endif)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Resultados cargados: {{ $usuarios->count() }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <label class="inline-flex items-center gap-2 text-gray-600">
                            <input type="checkbox" name="puede_ver" value="1" checked class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Puede ver
                        </label>
                        <label class="inline-flex items-center gap-2 text-gray-600">
                            <input type="checkbox" name="puede_subir" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Puede subir
                        </label>
                        <label class="inline-flex items-center gap-2 text-gray-600">
                            <input type="checkbox" name="activo" value="1" checked class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            Activo
                        </label>
                    </div>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-semibold shadow-sm hover:shadow-md hover:opacity-95 transition-all"
                            style="background:linear-gradient(135deg,#15803d,#14532d)">
                        Guardar acceso por usuario
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
            <div class="px-4 py-3 border-b" style="border-color:#e2e8f0">
                <h2 class="text-sm font-semibold text-gray-800">Accesos configurados</h2>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Destino</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Permisos</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proceso->siaObservaAccesos as $acceso)
                    <tr style="border-bottom:1px solid #f1f5f9" class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-600">{{ ucfirst($acceso->asignacion_tipo) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">
                            @if($acceso->asignacion_tipo === 'rol')
                                {{ \App\Support\RoleLabels::label((string) $acceso->role_name) }}
                            @else
                                {{ optional($acceso->usuario)->name ?: 'Usuario eliminado' }}
                                <span class="text-gray-400">{{ optional($acceso->usuario)->email ? ' - ' . optional($acceso->usuario)->email : '' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $acceso->puede_ver ? 'Ver' : 'Sin vista' }}
                            {{ $acceso->puede_subir ? ' + Subir' : '' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($acceso->activo)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#dcfce7;color:#15803d">Activo</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#fee2e2;color:#b91c1c">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.sia-observa.accesos.estado', ['proceso' => $proceso->id, 'acceso' => $acceso->id]) }}">
                                @csrf
                                <input type="hidden" name="activo" value="{{ $acceso->activo ? 0 : 1 }}">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                                        style="border:1px solid {{ $acceso->activo ? '#fecaca' : '#bbf7d0' }};background:#fff;color:{{ $acceso->activo ? '#b91c1c' : '#15803d' }}">
                                    {{ $acceso->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-400">No hay accesos configurados para este proceso.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #e2e8f0">
                <div class="px-4 py-3 border-b" style="border-color:#e2e8f0">
                    <h2 class="text-sm font-semibold text-gray-800">Archivos en repositorio SIA Observa</h2>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Documento</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Version</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Subido por</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proceso->siaObservaArchivos as $archivo)
                        <tr style="border-bottom:1px solid #f1f5f9" class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-xs text-gray-700">
                                <div class="font-medium">{{ $archivo->nombre_original }}</div>
                                <div class="text-gray-400">{{ $archivo->tipo_documento }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">v{{ $archivo->version }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ optional($archivo->subidoPor)->name ?: 'N/A' }}</td>
                            <td class="px-4 py-3">
                                @if($habilitado)
                                <a href="{{ route('sia-observa.archivos.descargar', ['archivo' => $archivo->id]) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-100 transition-all"
                                   style="border:1px solid #e2e8f0">
                                    Descargar
                                </a>
                                @else
                                <span class="text-[11px] text-gray-400">Se habilita en etapa final</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-400">Aun no hay archivos cargados para este proceso.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-2xl p-4" style="border:1px solid #e2e8f0">
                <h2 class="text-sm font-semibold text-gray-800">Paquete final para SIA Observa</h2>
                <p class="text-xs text-gray-500 mt-1">Descarga consolidada en ZIP de documentos para carga externa.</p>

                @if($habilitado)
                <a href="{{ route('sia-observa.paquete-final.descargar', ['proceso' => $proceso->id]) }}"
                   class="inline-flex mt-4 items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-semibold shadow-sm hover:shadow-md hover:opacity-95 transition-all"
                   style="background:linear-gradient(135deg,#15803d,#14532d)">
                    Descargar paquete final
                </a>
                @else
                <div class="mt-4 px-3 py-2 rounded-lg text-xs" style="background:#fef9c3;color:#a16207;border:1px solid #fde68a">
                    El paquete final se habilita cuando el proceso llega a etapa avanzada o estado finalizado.
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
