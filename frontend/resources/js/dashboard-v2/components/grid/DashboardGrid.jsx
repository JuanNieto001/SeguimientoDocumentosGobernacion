import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { Responsive, WidthProvider } from 'react-grid-layout';
import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';

const ResponsiveGridLayout = WidthProvider(Responsive);

// Configuraciones por tipo de widget
const WIDGET_CONSTRAINTS = {
    kpi: {
        minW: 1, maxW: 3,
        minH: 1, maxH: 2,
        defaultW: 1, defaultH: 1
    },
    chart: {
        minW: 2, maxW: 6,
        minH: 2, maxH: 4,
        defaultW: 3, defaultH: 2
    },
    table: {
        minW: 3, maxW: 12,
        minH: 3, maxH: 8,
        defaultW: 4, defaultH: 4
    },
    timeline: {
        minW: 3, maxW: 12,
        minH: 2, maxH: 4,
        defaultW: 6, defaultH: 3
    },
    heatmap: {
        minW: 3, maxW: 8,
        minH: 3, maxH: 5,
        defaultW: 4, defaultH: 3
    },
    alertas: {
        minW: 2, maxW: 6,
        minH: 3, maxH: 6,
        defaultW: 3, defaultH: 4
    }
};

// Configuración de breakpoints responsivos
const BREAKPOINTS = {
    xxl: 1600,
    xl: 1200,
    lg: 996,
    md: 768,
    sm: 576,
    xs: 0
};

const COLUMNS = {
    xxl: 6,
    xl: 5,
    lg: 4,
    md: 3,
    sm: 2,
    xs: 1
};

const DashboardGrid = ({
    widgets = [],
    renderWidget,
    config = {},
    editable = false,
    onLayoutChange,
    responsive,
    autoLayout = true,
    animationsEnabled = true
}) => {
    const { isMobile, isTablet, isDesktop, deviceFeatures } = responsive;

    // Estados del grid
    const [layouts, setLayouts] = useState({});
    const [isDragging, setIsDragging] = useState(false);
    const [draggedWidget, setDraggedWidget] = useState(null);
    const [snapToGrid, setSnapToGrid] = useState(false);
    const [compactType, setCompactType] = useState('vertical');

    // Configuraciones dinámicas según dispositivo
    const gridConfig = useMemo(() => ({
        breakpoints: config.breakpoints || BREAKPOINTS,
        cols: config.cols || COLUMNS,
        rowHeight: config.rowHeight || (isMobile ? 80 : isTablet ? 100 : 120),
        margin: config.margin || (isMobile ? [8, 8] : [16, 16]),
        containerPadding: config.containerPadding || (isMobile ? [8, 8] : [16, 16]),
        autoSize: true,
        draggableCancel: '.no-drag',
        draggableHandle: editable ? '.drag-handle' : null,
        resizeHandles: editable ? (isMobile ? [] : ['se', 's', 'e']) : [],
        useCSSTransforms: true,
        transformScale: 1,
        preventCollision: false,
        compactType: isMobile ? 'vertical' : compactType,
        verticalCompact: true
    }), [config, isMobile, isTablet, editable, compactType]);

    // Generar layouts iniciales para todos los breakpoints
    const generateInitialLayouts = useCallback(() => {
        const layoutsMap = {};

        Object.keys(gridConfig.cols).forEach(breakpoint => {
            const cols = gridConfig.cols[breakpoint];
            let currentY = 0;
            let currentX = 0;

            layoutsMap[breakpoint] = widgets.map((widget, index) => {
                const constraints = WIDGET_CONSTRAINTS[widget.tipo] || WIDGET_CONSTRAINTS.kpi;

                // Calcular dimensiones para el breakpoint
                let w = Math.min(constraints.defaultW, cols);
                let h = constraints.defaultH;

                // Auto-layout inteligente
                if (autoLayout) {
                    if (widget.tipo === 'kpi') {
                        // KPIs en la parte superior
                        w = 1;
                        h = 1;
                    } else if (widget.tipo === 'table' || widget.tipo === 'timeline') {
                        // Tablas y timelines ocupan más ancho
                        w = Math.min(cols, 4);
                    }
                }

                // Ajustar posición
                if (currentX + w > cols) {
                    currentX = 0;
                    currentY++;
                }

                const layoutItem = {
                    i: widget.id,
                    x: currentX,
                    y: currentY,
                    w,
                    h,
                    minW: constraints.minW,
                    maxW: Math.min(constraints.maxW, cols),
                    minH: constraints.minH,
                    maxH: constraints.maxH,
                    static: !editable || widget.static,
                    isDraggable: editable && !widget.static,
                    isResizable: editable && !widget.static && !isMobile
                };

                currentX += w;

                return layoutItem;
            });
        });

        return layoutsMap;
    }, [widgets, gridConfig.cols, editable, autoLayout, isMobile]);

    // Inicializar layouts
    useEffect(() => {
        const initialLayouts = generateInitialLayouts();
        setLayouts(initialLayouts);
    }, [generateInitialLayouts]);

    // Manejar cambios en el layout
    const handleLayoutChange = useCallback((currentLayout, allLayouts) => {
        setLayouts(allLayouts);

        if (onLayoutChange && !isDragging) {
            // Debounce para evitar demasiadas actualizaciones
            const timeoutId = setTimeout(() => {
                onLayoutChange(allLayouts);
            }, 300);

            return () => clearTimeout(timeoutId);
        }
    }, [onLayoutChange, isDragging]);

    // Eventos de drag
    const handleDragStart = useCallback((layout, oldItem, newItem, placeholder, e, element) => {
        setIsDragging(true);
        setDraggedWidget(newItem.i);

        // Agregar clase visual al elemento
        if (element) {
            element.classList.add('dragging');
        }

        // Habilitar snap to grid en desktop
        if (isDesktop && e.ctrlKey) {
            setSnapToGrid(true);
        }
    }, [isDesktop]);

    const handleDragStop = useCallback((layout, oldItem, newItem, placeholder, e, element) => {
        setIsDragging(false);
        setDraggedWidget(null);
        setSnapToGrid(false);

        // Remover clase visual
        if (element) {
            element.classList.remove('dragging');
        }

        // Efecto visual de "drop"
        if (animationsEnabled && element) {
            element.style.transform = 'scale(1.05)';
            setTimeout(() => {
                element.style.transform = '';
            }, 150);
        }
    }, [animationsEnabled]);

    // Eventos de resize
    const handleResizeStart = useCallback((layout, oldItem, newItem, placeholder, e, element) => {
        if (element) {
            element.classList.add('resizing');
        }
    }, []);

    const handleResizeStop = useCallback((layout, oldItem, newItem, placeholder, e, element) => {
        if (element) {
            element.classList.remove('resizing');
        }
    }, []);

    // Auto-organizar widgets
    const autoOrganize = useCallback((tipo = 'optimize') => {
        const currentBreakpoint = Object.keys(gridConfig.breakpoints)
            .find(bp => window.innerWidth >= gridConfig.breakpoints[bp]) || 'xs';

        const currentLayout = layouts[currentBreakpoint] || [];
        let newLayout = [...currentLayout];

        switch (tipo) {
            case 'optimize':
                // Reorganizar para minimizar espacios vacíos
                newLayout.sort((a, b) => {
                    if (a.y !== b.y) return a.y - b.y;
                    return a.x - b.x;
                });

                let currentRow = 0;
                let currentCol = 0;
                const cols = gridConfig.cols[currentBreakpoint];

                newLayout.forEach(item => {
                    if (currentCol + item.w > cols) {
                        currentRow++;
                        currentCol = 0;
                    }

                    item.x = currentCol;
                    item.y = currentRow;
                    currentCol += item.w;

                    if (currentCol >= cols) {
                        currentRow++;
                        currentCol = 0;
                    }
                });
                break;

            case 'kpis-top':
                // KPIs arriba, otros abajo
                const kpis = newLayout.filter(item => {
                    const widget = widgets.find(w => w.id === item.i);
                    return widget?.tipo === 'kpi';
                });

                const otros = newLayout.filter(item => {
                    const widget = widgets.find(w => w.id === item.i);
                    return widget?.tipo !== 'kpi';
                });

                // Reorganizar KPIs en la parte superior
                let x = 0, y = 0;
                kpis.forEach(item => {
                    if (x + item.w > gridConfig.cols[currentBreakpoint]) {
                        x = 0;
                        y++;
                    }
                    item.x = x;
                    item.y = y;
                    x += item.w;
                });

                // Otros widgets debajo
                y++;
                x = 0;
                otros.forEach(item => {
                    if (x + item.w > gridConfig.cols[currentBreakpoint]) {
                        x = 0;
                        y++;
                    }
                    item.x = x;
                    item.y = y;
                    x += item.w;
                    if (x >= gridConfig.cols[currentBreakpoint]) {
                        x = 0;
                        y++;
                    }
                });

                newLayout = [...kpis, ...otros];
                break;
        }

        setLayouts(prev => ({
            ...prev,
            [currentBreakpoint]: newLayout
        }));
    }, [layouts, gridConfig, widgets]);

    // Alternar compacto vertical/horizontal
    const toggleCompactType = useCallback(() => {
        setCompactType(prev => prev === 'vertical' ? 'horizontal' : 'vertical');
    }, []);

    // Reset layout
    const resetLayout = useCallback(() => {
        const newLayouts = generateInitialLayouts();
        setLayouts(newLayouts);
    }, [generateInitialLayouts]);

    // Renderizar widget con contenedor mejorado
    const renderEnhancedWidget = useCallback((widget) => {
        return (
            <div
                key={widget.id}
                className={`
                    widget-container
                    widget-${widget.tipo}
                    ${isDragging && draggedWidget === widget.id ? 'dragging' : ''}
                    ${editable ? 'editable' : 'readonly'}
                `}
            >
                {editable && (
                    <div className="widget-controls">
                        <div className="drag-handle" title="Arrastrar para mover">
                            <span className="drag-icon">⋮⋮</span>
                        </div>
                        <div className="widget-actions no-drag">
                            <button
                                className="btn-widget-action"
                                title="Configurar widget"
                                onClick={() => {/* Modal configuración */}}
                            >
                                ⚙️
                            </button>
                        </div>
                    </div>
                )}

                <div className="widget-content">
                    {renderWidget(widget)}
                </div>

                {editable && !isMobile && (
                    <div className="resize-indicator">
                        <span>↘</span>
                    </div>
                )}
            </div>
        );
    }, [editable, isDragging, draggedWidget, isMobile, renderWidget]);

    return (
        <div className="dashboard-grid-container">
            {/* Controles del grid (modo edición) */}
            {editable && (
                <div className="grid-controls">
                    <div className="grid-controls-group">
                        <button
                            onClick={() => autoOrganize('optimize')}
                            className="btn-grid-control"
                            title="Optimizar distribución"
                        >
                            📐 Optimizar
                        </button>

                        <button
                            onClick={() => autoOrganize('kpis-top')}
                            className="btn-grid-control"
                            title="KPIs arriba"
                        >
                            ⬆️ KPIs Arriba
                        </button>

                        <button
                            onClick={toggleCompactType}
                            className="btn-grid-control"
                            title={`Cambiar a compacto ${compactType === 'vertical' ? 'horizontal' : 'vertical'}`}
                        >
                            {compactType === 'vertical' ? '↕️' : '↔️'} Compactar
                        </button>

                        <button
                            onClick={resetLayout}
                            className="btn-grid-control warning"
                            title="Restaurar layout original"
                        >
                            🔄 Reset
                        </button>
                    </div>

                    <div className="grid-info">
                        <span className="grid-stats">
                            {widgets.length} widgets • {Object.keys(layouts).length} breakpoints
                        </span>
                    </div>
                </div>
            )}

            {/* Grid principal */}
            <ResponsiveGridLayout
                className="dashboard-grid"
                layouts={layouts}
                onLayoutChange={handleLayoutChange}
                onDragStart={handleDragStart}
                onDragStop={handleDragStop}
                onResizeStart={handleResizeStart}
                onResizeStop={handleResizeStop}
                measureBeforeMount={false}
                {...gridConfig}
            >
                {widgets.map(renderEnhancedWidget)}
            </ResponsiveGridLayout>

            {/* Indicador de drag global */}
            {isDragging && (
                <div className="drag-overlay">
                    <div className="drag-info">
                        Arrastrando: {widgets.find(w => w.id === draggedWidget)?.titulo || 'Widget'}
                    </div>
                </div>
            )}

            <style jsx>{`
                .dashboard-grid-container {
                    position: relative;
                    width: 100%;
                    min-height: 400px;
                }

                .grid-controls {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 12px 16px;
                    margin-bottom: 16px;
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                }

                .grid-controls-group {
                    display: flex;
                    gap: 8px;
                }

                .btn-grid-control {
                    padding: 6px 12px;
                    border: 1px solid #cbd5e1;
                    background: white;
                    border-radius: 6px;
                    font-size: 12px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                }

                .btn-grid-control:hover {
                    border-color: #64748b;
                    background: #f1f5f9;
                }

                .btn-grid-control.warning {
                    border-color: #f59e0b;
                    color: #92400e;
                }

                .btn-grid-control.warning:hover {
                    background: #fef3c7;
                }

                .grid-info {
                    font-size: 11px;
                    color: #64748b;
                }

                .widget-container {
                    position: relative;
                    background: white;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    overflow: hidden;
                    transition: all 0.2s ease;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                }

                .widget-container:hover {
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }

                .widget-container.dragging {
                    z-index: 1000;
                    transform: rotate(2deg);
                    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
                    border-color: #3b82f6;
                }

                .widget-container.editable .widget-controls {
                    opacity: 0;
                    transition: opacity 0.2s;
                }

                .widget-container.editable:hover .widget-controls {
                    opacity: 1;
                }

                .widget-controls {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 32px;
                    background: linear-gradient(to bottom, rgba(0,0,0,0.1), transparent);
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 0 8px;
                    z-index: 10;
                }

                .drag-handle {
                    cursor: grab;
                    padding: 4px;
                    border-radius: 4px;
                    background: rgba(255, 255, 255, 0.9);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 24px;
                    height: 24px;
                }

                .drag-handle:active {
                    cursor: grabbing;
                }

                .drag-icon {
                    font-size: 14px;
                    color: #64748b;
                    line-height: 1;
                }

                .widget-actions {
                    display: flex;
                    gap: 4px;
                }

                .btn-widget-action {
                    width: 24px;
                    height: 24px;
                    border: none;
                    background: rgba(255, 255, 255, 0.9);
                    border-radius: 4px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    transition: all 0.2s;
                }

                .btn-widget-action:hover {
                    background: white;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }

                .widget-content {
                    height: 100%;
                    padding: ${editable ? '8px' : '12px'};
                    padding-top: ${editable ? '40px' : '12px'};
                }

                .resize-indicator {
                    position: absolute;
                    bottom: 4px;
                    right: 4px;
                    width: 16px;
                    height: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    color: #94a3b8;
                    pointer-events: none;
                }

                .widget-container.resizing .resize-indicator {
                    color: #3b82f6;
                }

                .drag-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;
                    background: rgba(59, 130, 246, 0.1);
                    z-index: 999;
                    pointer-events: none;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .drag-info {
                    background: rgba(59, 130, 246, 0.9);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 8px;
                    font-weight: 500;
                    font-size: 14px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                }

                /* Responsive adjustments */
                @media (max-width: 768px) {
                    .grid-controls {
                        flex-direction: column;
                        gap: 12px;
                        text-align: center;
                    }

                    .widget-controls {
                        opacity: 1;
                        background: rgba(0, 0, 0, 0.05);
                    }

                    .drag-handle {
                        background: rgba(59, 130, 246, 0.9);
                        color: white;
                    }

                    .drag-icon {
                        color: white;
                    }
                }

                /* Animaciones */
                @media (prefers-reduced-motion: no-preference) {
                    .widget-container {
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    }
                }

                @media (prefers-reduced-motion: reduce) {
                    .widget-container {
                        transition: none;
                    }
                }

                /* Estilos react-grid-layout personalizados */
                :global(.react-grid-item > .react-resizable-handle) {
                    background: none;
                    width: 16px;
                    height: 16px;
                    bottom: 4px;
                    right: 4px;
                }

                :global(.react-grid-item > .react-resizable-handle::after) {
                    content: '↘';
                    position: absolute;
                    bottom: 0;
                    right: 0;
                    color: #94a3b8;
                    font-size: 12px;
                    line-height: 1;
                }

                :global(.react-grid-item.react-grid-placeholder) {
                    background: rgba(59, 130, 246, 0.2) !important;
                    border: 2px dashed #3b82f6 !important;
                    border-radius: 8px !important;
                    z-index: 2 !important;
                }

                :global(.react-grid-item.react-draggable-dragging) {
                    z-index: 1000 !important;
                }
            `}</style>
        </div>
    );
};

export default DashboardGrid;