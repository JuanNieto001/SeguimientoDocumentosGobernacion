{{-- Archivo: backend/resources/views/layouts/app.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Gobernación de Manizales') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet" media="print" onload="this.media='all'"/>
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/></noscript>
    <x-vite-assets :entries="['resources/css/app.css', 'resources/js/app.js']" />
</head>
<body class="antialiased" style="margin:0;background:#f1f5f9;font-family:'Inter',sans-serif" x-data="{ sidebar: false }">
<div class="flex h-screen overflow-hidden">

    {{-- Overlay móvil --}}
    <div x-show="sidebar" @click="sidebar=false"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-20 lg:hidden" style="background:rgba(0,0,0,.45);display:none"></div>

    {{-- SIDEBAR --}}
    <aside class="fixed inset-y-0 left-0 z-30 flex flex-col w-60 transition-transform duration-200 lg:static lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           style="background:linear-gradient(175deg,#052e16 0%,#14532d 100%);min-width:15rem">

        {{-- Logo --}}
        <div class="flex items-center gap-3 h-16 px-5 border-b shrink-0" style="border-color:rgba(255,255,255,.1)">
            <div class="w-9 h-9 rounded-xl overflow-hidden shrink-0 flex items-center justify-center shadow-lg" style="background:rgba(255,255,255,0.97);box-shadow:0 0 0 2px rgba(134,239,172,0.3)">
                <img src="/images/gobernacion.png" alt="Escudo" class="w-8 h-8 object-contain">
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-none">Gobernación</p>
                <p class="text-green-300 text-xs mt-0.5 font-light tracking-wide">de Caldas</p>
            </div>
        </div>

        {{-- Navegación --}}
        <nav class="sidebar-nav flex-1 overflow-y-auto px-3 py-3 scroll-smooth" 
             style="scrollbar-width: thin; 
                    scrollbar-color: rgba(255,255,255,0.3) transparent;">
            @include('layouts.navigation')
        </nav>

        {{-- Cerrar sesión --}}
        <div class="px-3 pb-4 shrink-0 border-t" style="border-color:rgba(255,255,255,.08);padding-top:.75rem">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link w-full" style="width:100%">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- CONTENIDO PRINCIPAL --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Barra superior --}}
        <header class="flex items-center justify-between h-16 px-6 bg-white border-b shrink-0" style="border-color:#e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.04)">
            <div class="flex items-center gap-4">
                <button @click="sidebar=!sidebar" class="lg:hidden p-2 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                @isset($header)
                <div class="min-w-0">{{ $header }}</div>
                @endisset
            </div>
            <div class="flex items-center gap-2">
                <div class="relative w-11 h-11 shrink-0" data-marsetiv-anchor aria-hidden="true" style="pointer-events:none"></div>
                @php
                    $authUser = auth()->user();
                    $areaUsuario = match(true) {
                        $authUser->hasRole('rentas') => 'rentas',
                        $authUser->hasRole('contabilidad') => 'contabilidad',
                        $authUser->hasRole('presupuesto') => 'presupuesto',
                        $authUser->hasRole('inversiones_publicas') => 'inversiones_publicas',
                        $authUser->hasRole('radicacion') => 'radicacion',
                        $authUser->hasRole('compras') => 'compras',
                        $authUser->hasRole('talento_humano') => 'talento_humano',
                        // Mapeo de alias funcional para compartir bandeja con Planeación.
                        $authUser->hasRole('descentralizacion') => 'planeacion',
                        $authUser->hasRole('planeacion') => 'planeacion',
                        $authUser->hasRole('hacienda') => 'hacienda',
                        $authUser->hasRole('juridica') => 'juridica',
                        $authUser->hasRole('unidad_solicitante') => 'unidad_solicitante',
                        $authUser->hasRole('secop') => 'secop',
                        default => null,
                    };
                    $unread = \App\Models\Alerta::where('leida', false)
                        ->where(function($q) use ($authUser, $areaUsuario) {
                            $q->where('user_id', $authUser->id);
                            if ($areaUsuario) {
                                $q->orWhere('area_responsable', $areaUsuario);
                            }
                            if ($authUser->hasRole('admin')) {
                                $q->orWhereNotNull('id');
                            }
                        })->count();
                    $displayName = $authUser->name;
                    $displaySub = $authUser->unidad?->nombre ?: $authUser->secretaria?->nombre;
                    $displaySub = $displaySub ?: 'Sin unidad';
                    $displayInitials = strtoupper(substr($displayName, 0, 2));
                    $displayLabel = \Illuminate\Support\Str::limit($displayName, 24);
                    $displaySubLabel = \Illuminate\Support\Str::limit($displaySub, 26);
                @endphp
                <a href="{{ route('alertas.abrir') }}" class="relative p-2 rounded-xl transition-all duration-150" style="color:#9ca3af" onmouseover="this.style.background='#f1f5f9';this.style.color='#374151'" onmouseout="this.style.background='';this.style.color='#9ca3af'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($unread > 0)
                    <span class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full text-white text-[10px] font-bold leading-none" style="background:#ef4444;box-shadow:0 0 0 2px white">{{ $unread > 9 ? '9+' : $unread }}</span>
                    @endif
                </a>
                <div class="flex items-center gap-2.5 pl-3 ml-1 border-l" style="border-color:#e2e8f0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white uppercase shrink-0" style="background:linear-gradient(135deg,#166534,#14532d);box-shadow:0 1px 3px rgba(0,0,0,0.2)">
                        {{ $displayInitials }}
                    </div>
                    <div class="hidden sm:flex flex-col leading-tight">
                        <span class="text-sm font-medium text-gray-700" title="{{ $displayName }}">{{ $displayLabel }}</span>
                        <span class="text-xs text-gray-400" title="{{ $displaySub }}">{{ $displaySubLabel }}</span>
                    </div>
                </div>
            </div>
        </header>

        {{-- Contenido de la página --}}
        <main class="flex-1 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>
</div>

{{-- Marsetiv bot – Asistente flotante --}}
@include('partials.agente-estiven')

{{-- Modal de previsualización de documentos --}}
@include('components.documento-preview-modal')

{{-- Prefetch: precarga páginas al pasar el mouse sobre enlaces (navegación instantánea) --}}
<script src="//instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0bbe5qXNB3owCJEElBhHxIivTO0TAoaXM70e3QRYPOjEL1E3v"></script>

</body>
</html>

