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

@role('admin')
<p class="sidebar-section">Administraci&oacute;n</p>
{!! sideLink(url('/admin/usuarios'),'Usuarios','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',$r->is('admin/usuarios*')) !!}
{!! sideLink(url('/admin/roles'),'Roles','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',$r->is('admin/roles*')) !!}
{!! sideLink(url('/admin/logs'),'Logs','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',$r->is('admin/logs*')) !!}
{!! sideLink(route('admin.auth-events'),'Log autenticaci&oacute;n','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',$r->routeIs('admin.auth-events')) !!}

<p class="sidebar-section">Bandeja por &Aacute;rea</p>

{{-- Accordion dinámico de secretarías → unidades --}}
@php
    $currentSecretariaId = (int) request('secretaria_id');
    $currentUnidadId     = (int) request('unidad_id');
    // Determinar qué secretaría debe estar abierta al cargar
    $openSecId = $currentSecretariaId;
    if (!$openSecId && $currentUnidadId) {
        $openSecId = $secretariasNav->first(fn($s) => $s->unidades->contains('id', $currentUnidadId))?->id ?? 0;
    }
@endphp
<div x-data="{ open: {{ $openSecId ?: 'null' }} }">
@foreach($secretariasNav as $sec)
    @php
        $secActive = $currentSecretariaId === $sec->id;
        $anyUnitActive = !$secActive && $sec->unidades->contains('id', $currentUnidadId);
    @endphp
    <div class="mb-0.5">
        {{-- Cabecera de secretaría --}}
        <button @click="open = open === {{ $sec->id }} ? null : {{ $sec->id }}"
                class="sidebar-link w-full justify-between pr-2"
                :class="open === {{ $sec->id }} ? 'active' : ''">
            <span class="flex items-center gap-2.5 min-w-0">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="truncate text-xs leading-tight">{{ $sec->nombre }}</span>
            </span>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 class="w-3 h-3 shrink-0 transition-transform duration-200 opacity-60"
                 :class="open === {{ $sec->id }} ? 'rotate-180' : ''">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- Submenu de unidades --}}
        <div x-show="open === {{ $sec->id }}"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mt-0.5 ml-3 pl-2 space-y-0.5"
             style="border-left:1px solid rgba(134,239,172,.25)">

            {{-- Enlace: todos los procesos de esta secretaría --}}
            <a href="{{ route('procesos.index', ['secretaria_id' => $sec->id]) }}"
               class="sidebar-link py-1.5 text-xs {{ $secActive ? 'active' : '' }}"
               style="padding-left:.875rem">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-3.5 h-3.5 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 10h16M4 14h16M4 18h7"/>
                </svg>
                <span>Todos los procesos</span>
            </a>

            {{-- Unidades de la secretaría --}}
            @foreach($sec->unidades as $unidad)
                @php $unitActive = $currentUnidadId === $unidad->id; @endphp
                <a href="{{ route('procesos.index', ['unidad_id' => $unidad->id]) }}"
                   class="sidebar-link py-1.5 text-xs {{ $unitActive ? 'active' : '' }}"
                   style="padding-left:.875rem">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-3.5 h-3.5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">{{ $unidad->nombre }}</span>
                </a>
            @endforeach

            @if($sec->unidades->isEmpty())
                <p class="text-xs py-1 pl-3.5 opacity-40 italic" style="color:#bbf7d0">Sin unidades</p>
            @endif
        </div>
    </div>
@endforeach

@if($secretariasNav->isEmpty())
    <p class="text-xs py-2 px-3 opacity-40 italic" style="color:#bbf7d0">No hay secretarías activas</p>
@endif
</div>


<p class="sidebar-section">Procesos</p>
{!! sideLink(route('procesos.create'),'Nueva solicitud','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',$r->routeIs('procesos.create')) !!}
{!! sideLink(route('procesos.index'),'Ver todos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}

<p class="sidebar-section">An&aacute;lisis</p>
{!! sideLink(route('reportes.index'),'Reportes','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',$r->routeIs('reportes.*')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endrole

@role('unidad_solicitante')
<p class="sidebar-section">Mi &Aacute;rea</p>
{!! sideLink(route('unidad.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('unidad*')) !!}
{!! sideLink(route('procesos.create'),'Nueva solicitud','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',$r->routeIs('procesos.create')) !!}

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
{!! sideLink(route('secop.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('secop*')) !!}
{!! sideLink(route('procesos.index'),'Ver procesos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endrole

@hasanyrole('compras|talento_humano|contabilidad|rentas|inversiones_publicas|presupuesto|radicacion')
<p class="sidebar-section">Mis Solicitudes</p>
{!! sideLink(route('solicitudes.index'),'Documentos Pendientes','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',$r->routeIs('solicitudes.*')) !!}
{!! sideLink(route('procesos.index'),'Ver procesos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}
{!! sideLink(route('alertas.index'),'Notificaciones','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',$r->routeIs('alertas.*')) !!}
@endhasanyrole

