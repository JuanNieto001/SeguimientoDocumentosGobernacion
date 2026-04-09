<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Motor de Dashboards</h1>
                <p class="text-xs text-gray-400 mt-0.5">Constructor interactivo de dashboards por secretaria, rol y usuario</p>
            </div>
        </div>
    </x-slot>

    <div id="dashboard-motor-app"></div>

    @php
        $historialMotor = $historialAsignaciones->map(function ($item) {
            return [
                'id' => $item->id,
                'fecha' => optional($item->created_at)->format('Y-m-d H:i'),
                'actor' => $item->actor->name ?? 'Sistema',
                'tipo_objetivo' => $item->tipo_objetivo,
                'role_name' => $item->role_name,
                'target_user' => $item->targetUser->name ?? null,
                'secretaria_nombre' => $item->metadata['secretaria_nombre'] ?? null,
                'accion' => $item->accion,
                'anterior' => $item->plantillaAnterior->nombre ?? 'Sin asignacion',
                'nueva' => $item->plantillaNueva->nombre ?? 'Sin asignacion',
            ];
        })->values();

        $motorData = [
            'plantillas' => $plantillasMotor,
            'roles' => $rolesMotor,
            'secretarias' => $secretariasMotor,
            'unidades' => $unidadesMotor,
            'usuarios' => $usuariosMotor,
            'chartTypeOptions' => $chartTypeOptions,
            'widgetLibrary' => $widgetLibrary,
            'dataScopeOptions' => $dataScopeOptions,
            'historial' => $historialMotor,
            'urls' => [
                'assignRole' => route('dashboards.motor.assign'),
                'assignUser' => route('dashboards.motor.assign-user'),
                'assignSecretaria' => route('dashboards.motor.assign-secretaria'),
                'assignUnidad' => route('dashboards.motor.assign-unidad'),
            ],
        ];
    @endphp

    <script>
        window.__DASHBOARD_MOTOR_DATA__ = @json($motorData);
    </script>

    @viteReactRefresh
    @vite('resources/js/dashboard-motor.jsx')
</x-app-layout>
