{{-- Archivo: backend/resources/views/layouts/guest.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
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
<body class="antialiased overflow-x-hidden lg:overflow-hidden" style="margin:0">
<div class="min-h-screen flex lg:overflow-hidden">

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
            </div>

            <p class="text-green-600 text-xs">&copy; {{ date('Y') }} Gobernación de Caldas &mdash; Todos los derechos reservados</p>
        </div>
    </div>

    {{-- Panel derecho: Formulario --}}
    <div class="flex-1 flex flex-col items-center justify-center min-h-screen box-border p-6 sm:p-8" style="background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%)">
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

