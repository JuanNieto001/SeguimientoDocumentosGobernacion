<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gobernacion de Caldas · Sistema de Contratacion</title>
    <link rel="icon" type="image/png" href="/images/gobernacion.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>html,body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="antialiased">
<div class="min-h-screen flex">

    {{-- Panel izquierdo: Branding --}}
    <div class="hidden lg:flex lg:w-[46%] relative overflow-hidden flex-col" style="background-image:url('/images/gobernacion.png');background-size:cover;background-position:center;">
        {{-- Overlay oscuro verde para legibilidad --}}
        <div class="absolute inset-0" style="background:linear-gradient(155deg,rgba(5,46,22,0.95) 0%,rgba(20,83,45,0.90) 55%,rgba(22,101,52,0.92) 100%)"></div>


<div class="relative z-10 flex flex-col h-full justify-between p-14">
            {{-- Logo --}}
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl overflow-hidden flex items-center justify-center" style="background:rgba(255,255,255,0.97);box-shadow:0 4px 24px rgba(0,0,0,0.25),0 0 0 1px rgba(255,255,255,0.15)">
                    <img src="/images/gobernacion.png" alt="Escudo Gobernación de Caldas" class="w-12 h-12 object-contain">
                </div>
                <div>
                    <p class="text-white font-bold text-base leading-tight">Gobernación de Caldas</p>
                    <p class="text-green-300 text-xs font-light tracking-wide">Manizales, Colombia</p>
                </div>
            </div>

            {{-- Contenido central --}}
            <div>
                <h1 class="text-white font-extrabold leading-none mb-5" style="font-size:3rem;letter-spacing:-0.02em">Sistema de<br>Seguimiento<br><span style="color:#86efac">Contractual</span></h1>
                <p class="text-green-100 text-base font-light leading-relaxed mb-10" style="max-width:22rem;opacity:0.8">
                    Gestión integral del proceso de contratación pública de la Gobernación de Caldas.
                </p>

                {{-- Características destacadas --}}
                <div class="space-y-3">
                    @foreach([
                        ['icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','label'=>'Control total del ciclo contractual'],
                        ['icon'=>'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','label'=>'Reportes y dashboards en tiempo real'],
                        ['icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','label'=>'Motor de flujos por rol y secretaría'],
                    ] as $feature)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(74,222,128,0.15);border:1px solid rgba(74,222,128,0.2)">
                            <svg class="w-4 h-4" style="color:#86efac" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/></svg>
                        </div>
                        <span class="text-sm" style="color:rgba(255,255,255,0.75)">{{ $feature['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <p class="text-green-600 text-xs">&copy; {{ date('Y') }} Gobernación de Caldas &mdash; Todos los derechos reservados</p>
        </div>
    </div>

    {{-- Panel derecho: Formulario --}}
    <div class="flex-1 flex flex-col items-center justify-center min-h-screen p-8" style="background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%)">
        {{-- Header móvil --}}
        <div class="lg:hidden mb-10 text-center">
            <div class="w-16 h-16 rounded-2xl overflow-hidden flex items-center justify-center mx-auto mb-3 border border-gray-200" style="background:#fff">
                <img src="/images/gobernacion.png" alt="Gobernación de Caldas" class="w-14 h-14 object-contain">
            </div>
            <p class="font-bold text-gray-900 text-xl">Gobernación de Caldas</p>
            <p class="text-gray-500 text-sm mt-1">Sistema de Seguimiento Contractual</p>
        </div>
        <div class="w-full" style="max-width:26rem">
            {{ $slot }}
        </div>
    </div>
</div>
</body>
</html>
