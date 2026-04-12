/**
 * Archivo: frontend/resources/js/dashboard-v2/DashboardMotorV2.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
import React, { useEffect, useMemo, useState, useCallback } from 'react';
import { createRoot } from 'react-dom/client';

// Importar componentes de widgets
import KpiWidget from './components/widgets/KpiWidget.jsx';
import ChartWidget from './components/widgets/ChartWidget.jsx';
import TableWidget from './components/widgets/TableWidget.jsx';
import TimelineWidget from './components/widgets/TimelineWidget.jsx';
import HeatmapWidget from './components/widgets/HeatmapWidget.jsx';
import AlertasWidget from './components/widgets/AlertasWidget.jsx';

// Importar componentes de layout
import DashboardGrid from './components/grid/DashboardGrid.jsx';
import FiltrosPanel from './components/filters/FiltrosPanel.jsx';

// Importar hooks
import { useDashboardData } from './hooks/useDashboardData';
import { useRealtimeUpdates } from './hooks/useRealtimeUpdates';
import { useResponsiveLayout } from './hooks/useResponsiveLayout';

// Obtener datos iniciales del servidor
const data = window.__DASHBOARD_V2_DATA__ || {
    usuario: {},
    dashboard: { widgets: [], layout: {}, filtros: {} },
    plantillas: [],
    permisos: [],
    urls: {},
    tiempo_real: false
};

// Registry de componentes de widgets
const WIDGET_COMPONENTS = {
    kpi: KpiWidget,
    chart: ChartWidget,
    table: TableWidget,
    timeline: TimelineWidget,
    heatmap: HeatmapWidget,
    alertas: AlertasWidget
};

// Templates predefinidos por rol
const ROLE_TEMPLATES = {
    gobernador: {
        nombre: 'Vista Ejecutiva Global',
        layout: { tipo: 'executive', columnas: 4, espaciado: 'amplio' },
        tema: 'verde-institucional',
        widgets_destacados: ['presupuesto-total', 'procesos-activos', 'eficiencia-global']
    },
    secretario: {
        nombre: 'Vista Operativa Secretaría',
        layout: { tipo: 'operational', columnas: 3, espaciado: 'normal' },
        tema: 'azul-operativo',
        widgets_destacados: ['procesos-secretaria', 'pendientes-firma', 'tiempo-tramite']
    },
    'jefe-unidad': {
        nombre: 'Gestión de Unidad',
        layout: { tipo: 'management', columnas: 4, espaciado: 'compacto' },
        tema: 'verde-gestion',
        widgets_destacados: ['carga-equipo', 'procesos-asignados', 'tiempo-respuesta']
    }
};

// Algoritmo de resolución de dashboard
const resolverDashboard = (usuario, plantillas, asignaciones) => {
    let dashboard = {};

    // 1. Cargar template base por rol principal
    const rolPrincipal = usuario.roles?.[0]?.name;
    const template = ROLE_TEMPLATES[rolPrincipal] || ROLE_TEMPLATES['jefe-unidad'];
    dashboard = { ...template };

    // 2. Aplicar asignación por rol
    const configRol = asignaciones.find(a => a.tipo === 'rol' && a.identificador === rolPrincipal);
    if (configRol?.config) {
        dashboard = mergeConfigs(dashboard, configRol.config);
    }

    // 3. Aplicar asignación por secretaría
    if (usuario.secretaria_id) {
        const configSecretaria = asignaciones.find(a => a.tipo === 'secretaria' && a.identificador === usuario.secretaria_id);
        if (configSecretaria?.config) {
            dashboard = mergeConfigs(dashboard, configSecretaria.config);
        }
    }

    // 4. Aplicar asignación por unidad
    if (usuario.unidad_id) {
        const configUnidad = asignaciones.find(a => a.tipo === 'unidad' && a.identificador === usuario.unidad_id);
        if (configUnidad?.config) {
            dashboard = mergeConfigs(dashboard, configUnidad.config);
        }
    }

    // 5. Aplicar configuración personal (mayor prioridad)
    const configUsuario = asignaciones.find(a => a.tipo === 'usuario' && a.identificador === usuario.id);
    if (configUsuario?.config) {
        dashboard = mergeConfigs(dashboard, configUsuario.config);
    }

    // 6. Filtrar por permisos
    dashboard = filtrarPorPermisos(dashboard, usuario.permisos || []);

    return dashboard;
};

// Función de merge inteligente de configuraciones
const mergeConfigs = (base, override) => {
    if (!override) return base;

    // Merge widgets preservando orden
    const widgets = [
        ...(base.widgets || []),
        ...(override.widgets || [])
    ].reduce((acc, widget) => {
        const existingIndex = acc.findIndex(w => w.id === widget.id);
        if (existingIndex >= 0) {
            acc[existingIndex] = { ...acc[existingIndex], ...widget };
        } else {
            acc.push(widget);
        }
        return acc;
    }, []);

    return {
        ...base,
        ...override,
        widgets: widgets.sort((a, b) => (a.orden || 0) - (b.orden || 0))
    };
};

// Filtrar widgets por permisos del usuario
const filtrarPorPermisos = (dashboard, permisos) => {
    const permisosSet = new Set(permisos);

    dashboard.widgets = (dashboard.widgets || []).filter(widget => {
        if (!widget.permiso_requerido) return true;
        return permisosSet.has(widget.permiso_requerido);
    });

    return dashboard;
};

// Componente principal
const DashboardMotorV2 = () => {
    // Estados principales
    const [dashboardConfig, setDashboardConfig] = useState(null);
    const [modoEdicion, setModoEdicion] = useState(false);
    const [filtrosActivos, setFiltrosActivos] = useState({
        periodo: { inicio: null, fin: null, preset: 'ultimo_mes' },
        secretaria: [],
        unidad: [],
        estado: [],
        prioridad: 'todas'
    });
    const [widgetSeleccionado, setWidgetSeleccionado] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [toast, setToast] = useState(null);

    // Hooks personalizados
    const { isMobile, isTablet, isDesktop, breakpoint } = useResponsiveLayout();
    const {
        widgetData,
        actualizarWidget,
        cargarDashboard,
        guardarConfiguracion
    } = useDashboardData(data.urls);

    const { conectado, suscribirWidget } = useRealtimeUpdates(data.tiempo_real);

    // Resolver configuración del dashboard al cargar
    useEffect(() => {
        const configuracionInicial = resolverDashboard(
            data.usuario,
            data.plantillas,
            data.asignaciones || []
        );

        setDashboardConfig(configuracionInicial);
        setCargando(false);
    }, []);

    // Función para mostrar notificaciones
    const mostrarToast = useCallback((mensaje, tipo = 'success') => {
        setToast({ mensaje, tipo });
        setTimeout(() => setToast(null), 3000);
    }, []);

    // Manejar cambios en filtros
    const handleFiltroChange = useCallback((tipoFiltro, valor, fusionar = true) => {
        setFiltrosActivos(prev => {
            if (fusionar && typeof valor === 'object' && !Array.isArray(valor)) {
                return { ...prev, [tipoFiltro]: { ...prev[tipoFiltro], ...valor } };
            }
            return { ...prev, [tipoFiltro]: valor };
        });
    }, []);

    // Manejar cambios en el layout
    const handleLayoutChange = useCallback((nuevoLayout) => {
        if (!modoEdicion) return;

        setDashboardConfig(prev => ({
            ...prev,
            layout: { ...prev.layout, grid: nuevoLayout }
        }));
    }, [modoEdicion]);

    // Guardar configuración del dashboard
    const guardarDashboard = useCallback(async () => {
        try {
            setCargando(true);
            await guardarConfiguracion(dashboardConfig, data.usuario.id);
            mostrarToast('Configuración guardada correctamente');
            setModoEdicion(false);
        } catch (error) {
            mostrarToast('Error al guardar la configuración: ' + error.message, 'error');
        } finally {
            setCargando(false);
        }
    }, [dashboardConfig, guardarConfiguracion, mostrarToast]);

    // Agregar widget al dashboard
    const agregarWidget = useCallback((tipoWidget, configuracion = {}) => {
        const nuevoWidget = {
            id: `widget-${Date.now()}`,
            tipo: tipoWidget,
            titulo: configuracion.titulo || `Nuevo ${tipoWidget}`,
            metrica: configuracion.metrica || '',
            orden: (dashboardConfig.widgets || []).length + 1,
            configuracion: {
                ancho: 1,
                alto: 1,
                ...configuracion
            },
            activo: true
        };

        setDashboardConfig(prev => ({
            ...prev,
            widgets: [...(prev.widgets || []), nuevoWidget]
        }));
    }, [dashboardConfig]);

    // Eliminar widget
    const eliminarWidget = useCallback((widgetId) => {
        setDashboardConfig(prev => ({
            ...prev,
            widgets: (prev.widgets || []).filter(w => w.id !== widgetId)
        }));

        if (widgetSeleccionado?.id === widgetId) {
            setWidgetSeleccionado(null);
        }
    }, [widgetSeleccionado]);

    // Renderizar widget según su tipo
    const renderWidget = useCallback((widget) => {
        const WidgetComponent = WIDGET_COMPONENTS[widget.tipo];

        if (!WidgetComponent) {
            return (
                <div className="widget-error">
                    <p>Tipo de widget desconocido: {widget.tipo}</p>
                </div>
            );
        }

        return (
            <WidgetComponent
                key={widget.id}
                widget={widget}
                data={widgetData[widget.id]}
                filtros={filtrosActivos}
                modoEdicion={modoEdicion}
                seleccionado={widgetSeleccionado?.id === widget.id}
                onSelect={() => setWidgetSeleccionado(widget)}
                onUpdate={(config) => actualizarWidget(widget.id, config)}
                onDelete={() => eliminarWidget(widget.id)}
                responsive={{ isMobile, isTablet, isDesktop }}
            />
        );
    }, [
        widgetData,
        filtrosActivos,
        modoEdicion,
        widgetSeleccionado,
        isMobile,
        isTablet,
        isDesktop,
        actualizarWidget,
        eliminarWidget
    ]);

    // Configuración del grid responsivo
    const gridConfig = useMemo(() => {
        const baseConfig = dashboardConfig?.layout || {};

        return {
            ...baseConfig,
            breakpoints: {
                lg: 1200,
                md: 996,
                sm: 768,
                xs: 480,
                xxs: 0
            },
            cols: {
                lg: baseConfig.columnas || 4,
                md: Math.min(3, baseConfig.columnas || 4),
                sm: 2,
                xs: 1,
                xxs: 1
            },
            margin: isMobile ? [8, 8] : [16, 16],
            containerPadding: isMobile ? [8, 8] : [16, 16]
        };
    }, [dashboardConfig, isMobile]);

    // Configuración del tema
    const temaConfig = useMemo(() => {
        const tema = dashboardConfig?.tema || 'verde-institucional';

        return {
            'verde-institucional': {
                primary: '#14532d',
                secondary: '#16a34a',
                accent: '#86efac',
                background: '#f0fdf4'
            },
            'azul-operativo': {
                primary: '#1e40af',
                secondary: '#3b82f6',
                accent: '#93c5fd',
                background: '#eff6ff'
            },
            'verde-gestion': {
                primary: '#166534',
                secondary: '#22c55e',
                accent: '#86efac',
                background: '#f0fdf4'
            }
        }[tema];
    }, [dashboardConfig]);

    if (cargando) {
        return (
            <div className="dashboard-loading">
                <div className="loading-spinner"></div>
                <p>Cargando dashboard...</p>
            </div>
        );
    }

    return (
        <div
            className={`dashboard-v2 ${breakpoint} tema-${dashboardConfig?.tema || 'verde-institucional'}`}
            style={{ '--color-primary': temaConfig.primary, '--color-secondary': temaConfig.secondary }}
        >
            {/* Header del dashboard */}
            <header className="dashboard-header">
                <div className="header-content">
                    <div className="dashboard-title">
                        <h1>{dashboardConfig?.nombre || 'Mi Dashboard'}</h1>
                        <p className="dashboard-subtitle">
                            {data.usuario.name} • {data.usuario.roles?.[0]?.label || 'Usuario'}
                        </p>
                    </div>

                    <div className="header-actions">
                        {/* Indicador de tiempo real */}
                        {data.tiempo_real && (
                            <div className={`realtime-indicator ${conectado ? 'connected' : 'disconnected'}`}>
                                <span className="status-dot"></span>
                                {conectado ? 'En vivo' : 'Desconectado'}
                            </div>
                        )}

                        {/* Controles de edición */}
                        <button
                            onClick={() => setModoEdicion(!modoEdicion)}
                            className={`btn-mode-toggle ${modoEdicion ? 'active' : ''}`}
                        >
                            {modoEdicion ? 'Finalizar Edición' : 'Editar Dashboard'}
                        </button>

                        {modoEdicion && (
                            <button
                                onClick={guardarDashboard}
                                className="btn-save primary"
                                disabled={cargando}
                            >
                                {cargando ? 'Guardando...' : 'Guardar Cambios'}
                            </button>
                        )}
                    </div>
                </div>
            </header>

            <div className="dashboard-content">
                {/* Panel de filtros */}
                <FiltrosPanel
                    filtros={filtrosActivos}
                    onChange={handleFiltroChange}
                    usuario={data.usuario}
                    responsive={{ isMobile, isTablet, isDesktop }}
                    collapsed={!isMobile || modoEdicion}
                />

                {/* Grid principal de widgets */}
                <main className="dashboard-main">
                    <DashboardGrid
                        widgets={dashboardConfig?.widgets || []}
                        renderWidget={renderWidget}
                        config={gridConfig}
                        editable={modoEdicion}
                        onLayoutChange={handleLayoutChange}
                        responsive={{ isMobile, isTablet, isDesktop }}
                    />

                    {/* Área de widgets disponibles (modo edición) */}
                    {modoEdicion && (
                        <aside className="widget-library">
                            <h3>Widgets Disponibles</h3>
                            <div className="widget-types">
                                {Object.keys(WIDGET_COMPONENTS).map(tipo => (
                                    <button
                                        key={tipo}
                                        onClick={() => agregarWidget(tipo)}
                                        className="widget-type-btn"
                                    >
                                        <span className={`widget-icon icon-${tipo}`}></span>
                                        {tipo.toUpperCase()}
                                    </button>
                                ))}
                            </div>
                        </aside>
                    )}
                </main>
            </div>

            {/* Toast de notificaciones */}
            {toast && (
                <div className={`toast toast-${toast.tipo}`}>
                    <span>{toast.mensaje}</span>
                    <button onClick={() => setToast(null)}>×</button>
                </div>
            )}

            {/* Estilos CSS en JS para tema dinámico */}
            <style jsx>{`
                .dashboard-v2 {
                    min-height: 100vh;
                    background: ${temaConfig.background};
                    color: #1f2937;
                }

                .dashboard-header {
                    background: white;
                    border-bottom: 1px solid #e5e7eb;
                    padding: 1rem 2rem;
                    position: sticky;
                    top: 0;
                    z-index: 100;
                }

                .header-content {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    max-width: none;
                }

                .dashboard-title h1 {
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: var(--color-primary);
                    margin: 0;
                }

                .dashboard-subtitle {
                    font-size: 0.875rem;
                    color: #6b7280;
                    margin: 4px 0 0;
                }

                .header-actions {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                }

                .realtime-indicator {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    font-size: 0.75rem;
                    font-weight: 500;
                    padding: 0.25rem 0.75rem;
                    border-radius: 1rem;
                }

                .realtime-indicator.connected {
                    background: #dcfce7;
                    color: #166534;
                }

                .realtime-indicator.disconnected {
                    background: #fef2f2;
                    color: #dc2626;
                }

                .status-dot {
                    width: 6px;
                    height: 6px;
                    border-radius: 50%;
                    background: currentColor;
                }

                .btn-mode-toggle {
                    padding: 0.5rem 1rem;
                    border: 1px solid var(--color-primary);
                    background: transparent;
                    color: var(--color-primary);
                    border-radius: 0.5rem;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s;
                }

                .btn-mode-toggle.active {
                    background: var(--color-primary);
                    color: white;
                }

                .btn-save {
                    padding: 0.5rem 1rem;
                    border: none;
                    border-radius: 0.5rem;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s;
                }

                .btn-save.primary {
                    background: var(--color-secondary);
                    color: white;
                }

                .btn-save:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }

                .dashboard-content {
                    display: flex;
                    min-height: calc(100vh - 80px);
                }

                .dashboard-main {
                    flex: 1;
                    padding: 1.5rem;
                }

                .widget-library {
                    width: 250px;
                    background: white;
                    border-left: 1px solid #e5e7eb;
                    padding: 1rem;
                }

                .widget-types {
                    display: grid;
                    gap: 0.5rem;
                }

                .widget-type-btn {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    padding: 0.75rem;
                    border: 1px solid #d1d5db;
                    background: white;
                    border-radius: 0.5rem;
                    cursor: pointer;
                    transition: all 0.2s;
                    font-size: 0.75rem;
                    font-weight: 500;
                }

                .widget-type-btn:hover {
                    border-color: var(--color-primary);
                    background: ${temaConfig.background};
                }

                .toast {
                    position: fixed;
                    top: 1rem;
                    right: 1rem;
                    padding: 0.75rem 1rem;
                    border-radius: 0.5rem;
                    font-weight: 500;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                    z-index: 200;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .toast-success {
                    background: #dcfce7;
                    color: #166534;
                    border: 1px solid #bbf7d0;
                }

                .toast-error {
                    background: #fef2f2;
                    color: #dc2626;
                    border: 1px solid #fecaca;
                }

                .loading-spinner {
                    width: 40px;
                    height: 40px;
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid var(--color-primary);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }

                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                .dashboard-loading {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    gap: 1rem;
                }

                /* Responsive adjustments */
                @media (max-width: 768px) {
                    .dashboard-header {
                        padding: 1rem;
                    }

                    .header-content {
                        flex-direction: column;
                        gap: 1rem;
                    }

                    .dashboard-content {
                        flex-direction: column;
                    }

                    .widget-library {
                        width: 100%;
                        border-left: none;
                        border-top: 1px solid #e5e7eb;
                    }
                }
            `}</style>
        </div>
    );
};

// Inicializar el componente
const root = document.getElementById('dashboard-motor-v2');
if (root) {
    createRoot(root).render(<DashboardMotorV2 />);
}

export default DashboardMotorV2;
