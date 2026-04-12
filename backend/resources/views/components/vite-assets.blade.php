{{-- Archivo: backend/resources/views/components/vite-assets.blade.php | Proposito: Carga segura de assets Vite para evitar caidas cuando falta manifest. | @documentado-copilot 2026-04-12 --}}
@props(['entries' => []])

@php
    $resolvedEntries = $entries;

    if ($resolvedEntries instanceof \Illuminate\Support\Collection) {
        $resolvedEntries = $resolvedEntries->values()->all();
    }

    if (is_string($resolvedEntries)) {
        $resolvedEntries = [$resolvedEntries];
    }

    if (!is_array($resolvedEntries)) {
        $resolvedEntries = [];
    }

    $viteReady = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
@endphp

@if($viteReady)
    @vite($resolvedEntries)
@elseif(app()->environment('local'))
    <script>
        console.warn('Vite assets no disponibles (public/build/manifest.json o public/hot). El render continua en modo seguro.');
    </script>
@endif
