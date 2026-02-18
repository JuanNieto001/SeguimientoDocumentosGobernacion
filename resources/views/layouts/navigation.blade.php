@php
    $r = request();
    $user = auth()->user();
    function sideLink(string $href, string $label, string $icon, bool $active): string {
        $cls = $active ? 'sidebar-link active' : 'sidebar-link';
        return "<a href=\"{$href}\" class=\"{$cls}\"><svg fill='none' stroke='currentColor' viewBox='0 0 24 24'>{$icon}</svg>{$label}</a>";
    }
@endphp

{{-- Dashboard siempre visible --}}
{!! sideLink(route('dashboard'),'Panel principal','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',$r->routeIs('dashboard')) !!}

@role('admin')
<p class="sidebar-section">Administración</p>
{!! sideLink(url('/admin/usuarios'),'Usuarios','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',$r->is('admin/usuarios*')) !!}
{!! sideLink(url('/admin/roles'),'Roles','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',$r->is('admin/roles*')) !!}

<p class="sidebar-section">Bandeja por Área</p>
{!! sideLink(url('/unidad'),'Unidad Solicitante','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',$r->is('unidad*')) !!}
{!! sideLink(url('/planeacion'),'Planeación','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>',$r->is('planeacion*')) !!}
{!! sideLink(url('/hacienda'),'Hacienda','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',$r->is('hacienda*')) !!}
{!! sideLink(url('/juridica'),'Jurídica','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',$r->is('juridica*')) !!}
{!! sideLink(url('/secop'),'SECOP','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',$r->is('secop*')) !!}

<p class="sidebar-section">Procesos</p>
{!! sideLink(route('procesos.create'),'Nueva solicitud','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',$r->routeIs('procesos.create')) !!}
{!! sideLink(route('procesos.index'),'Ver todos','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',$r->routeIs('procesos.index')) !!}
@endrole

@role('unidad_solicitante')
<p class="sidebar-section">Mi Área</p>
{!! sideLink(route('unidad.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('unidad*')) !!}
{!! sideLink(route('procesos.create'),'Nueva solicitud','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',$r->routeIs('procesos.create')) !!}
@endrole

@role('planeacion')
<p class="sidebar-section">Mi Área</p>
{!! sideLink(route('planeacion.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('planeacion*')) !!}
@endrole

@role('hacienda')
<p class="sidebar-section">Mi Área</p>
{!! sideLink(route('hacienda.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('hacienda*')) !!}
@endrole

@role('juridica')
<p class="sidebar-section">Mi Área</p>
{!! sideLink(route('juridica.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('juridica*')) !!}
@endrole

@role('secop')
<p class="sidebar-section">Mi Área</p>
{!! sideLink(route('secop.index'),'Mi bandeja','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',$r->is('secop*')) !!}
@endrole
