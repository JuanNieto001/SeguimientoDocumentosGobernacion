@php
    $r = request();
    $user = auth()->user();
    function sideLink(string $href, string $label, string $icon, bool $active): string {
        $cls = $active ? 'sidebar-link active' : 'sidebar-link';
        return "<a href=\"{$href}\" class=\"{$cls}\"><svg fill='none' stroke='currentColor' viewBox='0 0 24 24'>{$icon}</svg>{$label}</a>";
    }
    // Secretarías con unidades para accordion (solo carga si admin está logueado)
    $secretariasNav = auth()->user()?->hasRole('admin')
        ? \App\Models\Secretaria::with(['unidades' => fn($q) => $q->where('activo', true)->orderBy('nombre')])
            ->where('activo', true)->orderBy('nombre')->get()
        : collect();
@endphp

{{-- Dashboard siempre visible --}}
{!! sideLink(route('dashboard'),'Panel principal','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',$r->routeIs('dashboard')) !!}


@hasanyrole('admin|admin_general|admin_secretaria|gobernador|secretario|jefe_unidad')
{!! sideLink(route('contratos-aplicaciones.index'),'Contratos de aplicaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 3h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>',$r->routeIs('contratos-aplicaciones.*')) !!}
@endhasanyrole

@role('admin')
<p class="sidebar-section">Administraci&oacute;n</p>
{!! sideLink(url('/admin/usuarios'),'Usuarios','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',$r->is('admin/usuarios*')) !!}
{!! sideLink(url('/admin/roles'),'Roles','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',$r->is('admin/roles*')) !!}
{!! sideLink(url('/admin/logs'),'Logs','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',$r->is('admin/logs*')) !!}
{!! sideLink(route('admin.auth-events'),'Log autenticaci&oacute;n','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',$r->routeIs('admin.auth-events')) !!}
{!! sideLink(route('motor-flujos'),'Motor de Flujos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>',$r->routeIs('motor-flujos')) !!}
{!! sideLink(route('admin.estiven-guides.index'),'Gu&iacute;as de Marsetiv','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',$r->is('admin/estiven-guides*')) !!}

{{-- ============================================================ --}}
{{-- Sección colapsable: Secretarías (antes "Bandeja por Área")  --}}
{{-- ============================================================ --}}
@php
    $currentSecretariaId = (int) request('secretaria_id');
    $currentUnidadId     = (int) request('unidad_id');
    $openSecId = $currentSecretariaId;
    if (!$openSecId && $currentUnidadId) {
        $openSecId = $secretariasNav->first(fn($s) => $s->unidades->contains('id', $currentUnidadId))?->id ?? 0;
    }
    // Si hay alguna secretaría o unidad activa, abrir el panel principal
    $secretariaPanelOpen = $openSecId ? 'true' : 'false';
@endphp

<div x-data="{ showSecretarias: {{ $secretariaPanelOpen }}, openSec: {{ $openSecId ?: 'null' }} }" class="mt-1">
    {{-- Botón principal "Secretarías" --}}
    <button @click="showSecretarias = !showSecretarias"
            class="sidebar-link w-full justify-between pr-2"
            :class="showSecretarias ? 'active' : ''">
        <span class="flex items-center gap-2.5 min-w-0">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="truncate">Secretar&iacute;as</span>
        </span>
        <span class="flex items-center gap-1.5">
            <span class="text-[10px] font-medium rounded-full px-1.5 py-0.5 leading-none"
                  style="background:rgba(134,239,172,.15); color:#86efac">{{ $secretariasNav->count() }}</span>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 opacity-60"
                 :class="showSecretarias ? 'rotate-180' : ''">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
            </svg>
        </span>
    </button>

    {{-- Panel desplegable con todas las secretarías --}}
    <div x-show="showSecretarias"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1 max-h-0"
         x-transition:enter-end="opacity-100 translate-y-0 max-h-[2000px]"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0 -translate-y-1"
         x-cloak
         class="ml-2 pl-2 mt-0.5 space-y-0.5 overflow-hidden"
         style="border-left:1px solid rgba(134,239,172,.18)">

        @foreach($secretariasNav as $sec)
            @php
                $secActive = $currentSecretariaId === $sec->id;
                $anyUnitActive = !$secActive && $sec->unidades->contains('id', $currentUnidadId);
            @endphp
            <div class="mb-0.5">
                {{-- Cabecera de secretaría --}}
                <button @click="openSec = openSec === {{ $sec->id }} ? null : {{ $sec->id }}"
                        class="sidebar-link w-full justify-between pr-2 py-1.5 text-xs"
                        :class="openSec === {{ $sec->id }} ? 'active' : ''">
                    <span class="flex items-center gap-2 min-w-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-3.5 h-3.5 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="truncate leading-tight">{{ $sec->nombre }}</span>
                    </span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         class="w-3 h-3 shrink-0 transition-transform duration-200 opacity-60"
                         :class="openSec === {{ $sec->id }} ? 'rotate-180' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Submenu de unidades --}}
                <div x-show="openSec === {{ $sec->id }}"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="mt-0.5 ml-3 pl-2 space-y-0.5"
                     style="border-left:1px solid rgba(134,239,172,.2)">

                    {{-- Enlace: todos los procesos de esta secretaría --}}
                    <a href="{{ route('procesos.index', ['secretaria_id' => $sec->id]) }}"
                       class="sidebar-link py-1 text-[11px] {{ $secActive ? 'active' : '' }}"
                       style="padding-left:.75rem">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-3 h-3 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 10h16M4 14h16M4 18h7"/>
                        </svg>
                        <span>Todos los procesos</span>
                    </a>

                    {{-- Unidades de la secretaría --}}
                    @foreach($sec->unidades as $unidad)
                        @php $unitActive = $currentUnidadId === $unidad->id; @endphp
                        <a href="{{ route('procesos.index', ['unidad_id' => $unidad->id]) }}"
                           class="sidebar-link py-1 text-[11px] {{ $unitActive ? 'active' : '' }}"
                           style="padding-left:.75rem">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-3 h-3 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="truncate">{{ $unidad->nombre }}</span>
                        </a>
                    @endforeach

                    @if($sec->unidades->isEmpty())
                        <p class="text-[10px] py-1 pl-3 opacity-40 italic" style="color:#bbf7d0">Sin unidades</p>
                    @endif
                </div>
            </div>
        @endforeach

        @if($secretariasNav->isEmpty())
            <p class="text-xs py-2 px-3 opacity-40 italic" style="color:#bbf7d0">No hay secretar&iacute;as activas</p>
        @endif
    </div>
</div>


<p class="sidebar-section">Procesos</p>
{!! sideLink(route('procesos.create'),'Nueva solicitud','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',$r->routeIs('procesos.create')) !!}
{!! sideLink(route('procesos.index'),'Ver todos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}

<p class="sidebar-section">An&aacute;lisis</p>
{!! sideLink(route('reportes.index'),'Reportes','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',$r->routeIs('reportes.*')) !!}
{!! sideLink(route('secop.consulta'),'Consulta SECOP II','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>',$r->is('secop-consulta*')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endrole

@role('unidad_solicitante')
<p class="sidebar-section">Mi &Aacute;rea</p>
{!! sideLink(route('unidad.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('unidad*')) !!}
{!! sideLink(route('procesos.create'),'Nueva solicitud','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',$r->routeIs('procesos.create')) !!}
{!! sideLink(route('secop.consulta'),'Consulta SECOP II','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>',$r->is('secop-consulta*')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endrole

@role('planeacion')
@if(!auth()->user()->hasAnyRole(['inversiones_publicas']))
<p class="sidebar-section">Mi &Aacute;rea</p>
{!! sideLink(route('planeacion.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('planeacion*')) !!}

<p class="sidebar-section">Procesos</p>
{!! sideLink(route('procesos.create'),'Nueva solicitud','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',$r->routeIs('procesos.create')) !!}
{!! sideLink(route('procesos.index'),'Ver todos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}
{!! sideLink(route('paa.index'),'Plan Anual (PAA)','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',$r->is('paa*')) !!}
{!! sideLink(route('reportes.index'),'Reportes','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',$r->routeIs('reportes.*')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endif
@endrole

@role('hacienda')
@if(!auth()->user()->hasAnyRole(['contabilidad', 'rentas', 'presupuesto']))
<p class="sidebar-section">Mi &Aacute;rea</p>
{!! sideLink(route('hacienda.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('hacienda*')) !!}
{!! sideLink(route('procesos.index'),'Ver procesos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endif
@endrole

@role('juridica')
<p class="sidebar-section">Mi &Aacute;rea</p>
{!! sideLink(route('juridica.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('juridica*')) !!}
{!! sideLink(route('procesos.index'),'Ver procesos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endrole

@role('secop')
<p class="sidebar-section">Mi &Aacute;rea</p>
{!! sideLink(route('secop.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('secop*') && !$r->is('secop-consulta*')) !!}
{!! sideLink(route('secop.consulta'),'Consulta SECOP II','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>',$r->is('secop-consulta*')) !!}
{!! sideLink(route('procesos.index'),'Ver procesos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endrole

@role('gobernador')
<p class="sidebar-section">Despacho del Gobernador</p>
{!! sideLink(route('procesos.index'),'Ver procesos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}
{!! sideLink(route('secop.consulta'),'Consulta SECOP II','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>',$r->is('secop-consulta*')) !!}
{!! sideLink(route('reportes.index'),'Reportes','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',$r->routeIs('reportes.*')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endrole

@hasanyrole('compras|talento_humano|contabilidad|rentas|inversiones_publicas|presupuesto|radicacion')
<p class="sidebar-section">Mis Solicitudes</p>
{!! sideLink(route('solicitudes.index'),'Documentos Pendientes','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',$r->routeIs('solicitudes.*')) !!}
{!! sideLink(route('procesos.index'),'Ver procesos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endhasanyrole

