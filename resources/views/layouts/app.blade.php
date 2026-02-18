<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Gobernación de Manizales') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html,body{font-family:'Inter',sans-serif;}
        .sidebar-link{display:flex;align-items:center;gap:.75rem;padding:.625rem .75rem;border-radius:.875rem;font-size:.8125rem;font-weight:500;transition:all .15s;color:#bbf7d0;text-decoration:none}
        .sidebar-link:hover{background:rgba(255,255,255,.1);color:#fff}
        .sidebar-link.active{background:rgba(255,255,255,.18);color:#fff;font-weight:600}
        .sidebar-link svg{width:1rem;height:1rem;flex-shrink:0;opacity:.85}
        .sidebar-link.active svg{opacity:1}
        .sidebar-section{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#4ade80;padding:.75rem .75rem .375rem;margin-top:.5rem}
        /* Ocultar scrollbar del sidebar */
        .sidebar-nav::-webkit-scrollbar{width:0;background:transparent}
        .sidebar-nav{-ms-overflow-style:none;scrollbar-width:none}
    </style>
</head>
<body class="antialiased" style="background:#f1f5f9;font-family:'Inter',sans-serif" x-data="{ sidebar: false }">
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
            <div class="w-9 h-9 rounded-xl overflow-hidden shrink-0 flex items-center justify-center" style="background:rgba(255,255,255,0.95)">
                <img src="/images/gobernacion.png" alt="Escudo" class="w-8 h-8 object-contain">
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-none">Gobernación</p>
                <p class="text-green-300 text-xs mt-0.5">Caldas</p>
            </div>
        </div>

        {{-- Usuario --}}
        <div class="flex items-center gap-3 px-4 py-3.5 shrink-0 border-b" style="border-color:rgba(255,255,255,.08)">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white uppercase shrink-0" style="background:#16a34a">
                {{ strtoupper(substr(Auth::user()->name,0,2)) }}
            </div>
            <div class="min-w-0">
                <p class="text-white text-xs font-semibold truncate">{{ Auth::user()->name }}</p>
                <p class="text-green-300 text-xs truncate opacity-70">{{ Auth::user()->email }}</p>
            </div>
        </div>

        {{-- Navegación --}}
        <nav class="sidebar-nav flex-1 overflow-y-auto px-3 py-3">
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
        <header class="flex items-center justify-between h-16 px-6 bg-white border-b shrink-0" style="border-color:#e2e8f0">
            <div class="flex items-center gap-4">
                <button @click="sidebar=!sidebar" class="lg:hidden p-2 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                @isset($header)
                <div class="min-w-0">{{ $header }}</div>
                @endisset
            </div>
            <div class="flex items-center gap-2">
                @php $unread = \App\Models\Alerta::where('user_id',auth()->id())->where('leida',false)->count(); @endphp
                <a href="{{ route('alertas.index') }}" class="relative p-2 rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($unread > 0)
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                    @endif
                </a>
                <div class="flex items-center gap-2.5 pl-3 ml-1 border-l" style="border-color:#e2e8f0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white uppercase shrink-0" style="background:#166534">
                        {{ strtoupper(substr(Auth::user()->name,0,2)) }}
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-gray-700">{{ explode(' ',Auth::user()->name)[0] }}</span>
                </div>
            </div>
        </header>

        {{-- Contenido de la página --}}
        <main class="flex-1 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
