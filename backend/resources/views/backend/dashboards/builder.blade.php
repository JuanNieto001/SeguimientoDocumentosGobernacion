{{-- Archivo: backend/resources/views/backend/dashboards/builder.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
@extends('layouts.app')

@section('title', 'Dashboard Builder')

@push('styles')
<style>
    /* Reset para el builder */
    #dashboard-builder-root {
        height: calc(100vh - 64px);
        overflow: hidden;
    }

    /* Estilos para react-grid-layout */
    .react-grid-item {
        transition: all 200ms ease;
        transition-property: left, top, width, height;
    }

    .react-grid-item.cssTransforms {
        transition-property: transform, width, height;
    }

    .react-grid-item.resizing {
        z-index: 1;
        will-change: width, height;
    }

    .react-grid-item.react-draggable-dragging {
        transition: none;
        z-index: 3;
        will-change: transform;
    }

    .react-grid-item.dropping {
        visibility: hidden;
    }

    .react-grid-item > .react-resizable-handle {
        position: absolute;
        width: 20px;
        height: 20px;
    }

    .react-grid-item > .react-resizable-handle::after {
        content: "";
        position: absolute;
        right: 3px;
        bottom: 3px;
        width: 6px;
        height: 6px;
        border-right: 2px solid rgba(0, 0, 0, 0.2);
        border-bottom: 2px solid rgba(0, 0, 0, 0.2);
    }

    .react-resizable-hide > .react-resizable-handle {
        display: none;
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-sw {
        bottom: 0;
        left: 0;
        cursor: sw-resize;
        transform: rotate(90deg);
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-se {
        bottom: 0;
        right: 0;
        cursor: se-resize;
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-nw {
        top: 0;
        left: 0;
        cursor: nw-resize;
        transform: rotate(180deg);
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-ne {
        top: 0;
        right: 0;
        cursor: ne-resize;
        transform: rotate(270deg);
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-w,
    .react-grid-item > .react-resizable-handle.react-resizable-handle-e {
        top: 50%;
        margin-top: -10px;
        cursor: ew-resize;
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-w {
        left: 0;
        transform: rotate(135deg);
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-e {
        right: 0;
        transform: rotate(315deg);
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-n,
    .react-grid-item > .react-resizable-handle.react-resizable-handle-s {
        left: 50%;
        margin-left: -10px;
        cursor: ns-resize;
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-n {
        top: 0;
        transform: rotate(225deg);
    }

    .react-grid-item > .react-resizable-handle.react-resizable-handle-s {
        bottom: 0;
        transform: rotate(45deg);
    }

    /* Placeholder durante drag */
    .react-grid-placeholder {
        background: rgb(5, 150, 105);
        opacity: 0.2;
        border-radius: 0.75rem;
        transition-duration: 100ms;
        z-index: 2;
        user-select: none;
    }
</style>
@endpush

@section('content')
<div id="dashboard-builder-root"
     data-initial-config="{{ $initialConfig ?? '' }}"
     data-read-only="{{ $readOnly ?? 'false' }}">
    {{-- React se montará aquí --}}
    <div class="flex items-center justify-center h-full bg-gray-100">
        <div class="text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-green-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Cargando Dashboard Builder...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@viteReactRefresh
@vite('resources/js/dashboard-builder.jsx')
@endpush

