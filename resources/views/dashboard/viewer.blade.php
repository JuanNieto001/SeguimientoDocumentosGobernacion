@extends('layouts.app')

@section('title', 'Mis Dashboards')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Mis Dashboards</h1>
            <p class="text-muted mb-0">
                Dashboards asignados a tu rol: <strong>{{ $user->roles->first()?->name ?? 'Sin rol' }}</strong>
            </p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                Usuario: {{ $user->name }}<br>
                @if($user->secretaria)
                    Secretaría: {{ $user->secretaria->nombre }}<br>
                @endif
                @if($user->unidad)
                    Unidad: {{ $user->unidad->nombre }}
                @endif
            </small>
        </div>
    </div>

    @if($dashboards->count() > 0)
        <div class="row">
            @foreach($dashboards as $dashboard)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-left-primary">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">{{ $dashboard->name }}</h6>
                                <span class="badge bg-primary">
                                    {{ count($dashboard->widgets) }} widgets
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($dashboard->description)
                                <p class="text-muted small">{{ $dashboard->description }}</p>
                            @endif
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    Creado por: {{ $dashboard->creator->name }}
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $dashboard->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('dashboard.viewer.show', $dashboard->id) }}" 
                               class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-chart-bar me-2"></i>
                                Ver Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-4"></i>
                        <h4 class="text-muted">Sin dashboards asignados</h4>
                        <p class="text-muted mb-4">
                            No tienes dashboards asignados en este momento. 
                            Los administradores pueden asignarte dashboards personalizados.
                        </p>
                        
                        @if($user->hasRole(['admin', 'admin_general']))
                            <a href="{{ route('dashboard.builder.index') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Crear Dashboard
                            </a>
                        @else
                            <p class="small text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Contacta a los administradores para solicitar acceso a dashboards
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    
    .card:hover {
        transform: translateY(-2px);
        transition: all 0.2s ease;
    }
</style>
@endpush