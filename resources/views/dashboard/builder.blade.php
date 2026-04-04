@extends('layouts.app')

@section('title', 'Dashboard Builder')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Dashboard Builder</h1>
            <p class="text-muted mb-0">
                Construye dashboards personalizados con datos filtrados por tu rol: <strong>{{ $userRole->name ?? 'Sin rol' }}</strong>
            </p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                Usuario: {{ $user->name }}<br>
                Secretaría: {{ $user->secretaria->nombre ?? 'No asignada' }}<br>
                Unidad: {{ $user->unidad->nombre ?? 'No asignada' }}
            </small>
        </div>
    </div>

    <!-- Contenedor del Dashboard Builder React -->
    <div id="dashboard-builder-root" class="bg-white rounded-lg shadow-sm border" 
         data-user="{{ json_encode([
             'id' => $user->id,
             'name' => $user->name,
             'role' => $userRole->name ?? null,
             'secretaria_id' => $user->secretaria_id,
             'unidad_id' => $user->unidad_id
         ]) }}"
         data-entities="{{ json_encode($entities) }}"
         data-csrf-token="{{ $csrf_token }}">
        
        <!-- Loading state mientras carga React -->
        <div class="d-flex align-items-center justify-content-center" style="min-height: 500px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <h5 class="text-muted">Cargando Dashboard Builder...</h5>
                <p class="text-muted">Inicializando componentes React y validando permisos</p>
            </div>
        </div>
    </div>

    <!-- Información de ayuda -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card border-left-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="fas fa-info-circle"></i> Cómo usar el Dashboard Builder
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li><strong>Selecciona una entidad</strong> del panel izquierdo (procesos, usuarios, etc.)</li>
                                <li><strong>Arrastra</strong> la entidad al lienzo central</li>
                                <li><strong>Configura</strong> el tipo de widget en el panel derecho</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li><strong>Aplica filtros</strong> para segmentar los datos</li>
                                <li><strong>Agrupa y agrega</strong> datos para obtener insights</li>
                                <li><strong>Los datos se filtran automáticamente</strong> según tu rol y permisos</li>
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
<!-- Dashboard Builder CSS -->
<style>
    #dashboard-builder-root {
        min-height: 600px;
        border-radius: 8px;
    }

    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }

    .card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
    }

    .container-fluid {
        background-color: #f8f9fc;
    }
</style>
@endpush

@push('scripts')
<!-- Dashboard Builder React App -->
@vite(['resources/js/dashboard-builder.jsx'])
@endpush