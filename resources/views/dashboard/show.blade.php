@extends('layouts.app')

@section('title', $dashboard->name)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.viewer.index') }}">Mis Dashboards</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $dashboard->name }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">{{ $dashboard->name }}</h1>
            @if($dashboard->description)
                <p class="text-muted mb-0">{{ $dashboard->description }}</p>
            @endif
        </div>
        <div class="text-end">
            <small class="text-muted">
                <i class="fas fa-user me-1"></i>
                Creado por: {{ $dashboard->creator->name }}<br>
                <i class="fas fa-calendar me-1"></i>
                {{ $dashboard->created_at->format('d/m/Y H:i') }}<br>
                <i class="fas fa-widgets me-1"></i>
                {{ count($dashboard->widgets) }} widgets
            </small>
        </div>
    </div>

    <!-- Contenedor del Dashboard Viewer React -->
    <div id="dashboard-viewer-root" class="bg-white rounded-lg shadow-sm border" 
         data-dashboard="{{ json_encode([
             'id' => $dashboard->id,
             'name' => $dashboard->name,
             'description' => $dashboard->description,
             'widgets' => $widgets,
             'readonly' => true
         ]) }}"
         data-user="{{ json_encode([
             'id' => $user->id,
             'name' => $user->name,
             'role' => $user->roles->first()?->name,
             'secretaria_id' => $user->secretaria_id,
             'unidad_id' => $user->unidad_id
         ]) }}"
         data-csrf-token="{{ $csrf_token }}">
        
        <!-- Loading state mientras carga React -->
        <div class="d-flex align-items-center justify-content-center" style="min-height: 500px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <h5 class="text-muted">Cargando Dashboard...</h5>
                <p class="text-muted">Preparando {{ count($widgets) }} widgets</p>
            </div>
        </div>
    </div>

    <!-- Información de dashboard -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card border-left-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="fas fa-info-circle"></i> Información del Dashboard
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li><strong>Modo:</strong> Solo lectura</li>
                                <li><strong>Actualización:</strong> Automática cada 30 segundos</li>
                                <li><strong>Filtros:</strong> Aplicados según tu rol automáticamente</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li><strong>Widgets interactivos:</strong> Hover para detalles</li>
                                <li><strong>Datos en tiempo real:</strong> Filtrados por permisos</li>
                                <li><strong>Sin edición:</strong> Solo administradores pueden modificar</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Dashboard Viewer CSS -->
<style>
    #dashboard-viewer-root {
        min-height: 600px;
        border-radius: 8px;
    }

    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: ">";
    }

    .container-fluid {
        background-color: #f8f9fc;
    }
</style>
@endpush

@push('scripts')
<!-- Dashboard Viewer React App - usar el mismo builder pero en modo readonly -->
@vite(['resources/js/dashboard-builder.jsx'])
@endpush