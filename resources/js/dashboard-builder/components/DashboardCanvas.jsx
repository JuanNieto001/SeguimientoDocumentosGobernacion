/**
 * Dashboard Canvas
 *
 * Canvas central donde se arrastran y organizan los widgets.
 * Usa react-grid-layout para el grid responsive.
 */

import React, { useCallback, memo } from 'react';
import GridLayout from 'react-grid-layout';
import { useDrop } from 'react-dnd';
import DynamicWidgetRenderer from '../widgets/DynamicWidgetRenderer';

const DashboardCanvas = ({
    widgets,
    layout,
    widgetData,
    widgetErrors,
    selectedWidgetId,
    isEditMode,
    onLayoutChange,
    onWidgetSelect,
    onWidgetRemove,
    onFieldDrop,
}) => {
    // Configurar drop zone
    const [{ isOver, canDrop }, drop] = useDrop(() => ({
        accept: 'FIELD',
        drop: (item, monitor) => {
            const offset = monitor.getClientOffset();
            if (offset && onFieldDrop) {
                // Calcular posición en el grid (aproximada)
                const gridX = Math.floor((offset.x - 288) / 100); // 288 = ancho panel izquierdo
                const gridY = Math.floor(offset.y / 100);
                onFieldDrop(item, { x: Math.max(0, gridX), y: Math.max(0, gridY) });
            }
            return undefined;
        },
        collect: (monitor) => ({
            isOver: monitor.isOver(),
            canDrop: monitor.canDrop(),
        }),
    }), [onFieldDrop]);

    // Manejar cambio de layout
    const handleLayoutChange = useCallback((newLayout) => {
        if (onLayoutChange) {
            onLayoutChange(newLayout);
        }
    }, [onLayoutChange]);

    // Renderizar widget individual
    const renderWidget = useCallback((widget) => {
        const data = widgetData[widget.id];
        const error = widgetErrors[widget.id];
        const isSelected = selectedWidgetId === widget.id;

        return (
            <div
                key={widget.id}
                className={`
                    h-full rounded-xl overflow-hidden shadow-sm
                    transition-all duration-200
                    ${isSelected
                        ? 'ring-2 ring-green-500 ring-offset-2'
                        : 'hover:shadow-md'
                    }
                    ${isEditMode ? 'cursor-move' : ''}
                `}
                onClick={() => isEditMode && onWidgetSelect?.(widget.id)}
            >
                <div className="h-full bg-white rounded-xl flex flex-col">
                    {/* Header del widget */}
                    <div className="flex items-center justify-between px-4 py-2 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h3 className="text-sm font-semibold text-gray-700 truncate">
                            {widget.titulo || 'Widget sin título'}
                        </h3>

                        {isEditMode && (
                            <div className="flex items-center space-x-1">
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onWidgetSelect?.(widget.id);
                                    }}
                                    className="p-1 text-gray-400 hover:text-blue-500 transition-colors"
                                    title="Configurar"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        if (confirm('¿Eliminar este widget?')) {
                                            onWidgetRemove?.(widget.id);
                                        }
                                    }}
                                    className="p-1 text-gray-400 hover:text-red-500 transition-colors"
                                    title="Eliminar"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        )}
                    </div>

                    {/* Contenido del widget */}
                    <div className="flex-1 p-3 overflow-auto">
                        <DynamicWidgetRenderer
                            widget={widget}
                            data={data}
                            error={error}
                        />
                    </div>
                </div>
            </div>
        );
    }, [widgetData, widgetErrors, selectedWidgetId, isEditMode, onWidgetSelect, onWidgetRemove]);

    return (
        <div
            ref={drop}
            className={`
                min-h-full rounded-xl transition-colors
                ${isOver && canDrop
                    ? 'bg-green-50 border-2 border-dashed border-green-400'
                    : 'bg-transparent'
                }
            `}
        >
            {widgets.length === 0 ? (
                /* Estado vacío */
                <div className="flex items-center justify-center h-96 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50">
                    <div className="text-center">
                        <div className="text-4xl mb-4">📊</div>
                        <h3 className="text-lg font-medium text-gray-700 mb-2">
                            Canvas vacío
                        </h3>
                        <p className="text-sm text-gray-500 mb-4 max-w-xs">
                            Arrastra campos desde el panel izquierdo para crear widgets
                        </p>
                        {isOver && canDrop && (
                            <div className="animate-pulse text-green-600 font-medium">
                                ✨ Suelta aquí para crear widget
                            </div>
                        )}
                    </div>
                </div>
            ) : (
                <GridLayout
                    className="layout"
                    layout={layout}
                    cols={12}
                    rowHeight={100}
                    width={1200}
                    onLayoutChange={handleLayoutChange}
                    isDraggable={isEditMode}
                    isResizable={isEditMode}
                    draggableHandle=".widget-drag-handle"
                    compactType="vertical"
                    preventCollision={false}
                    useCSSTransforms={true}
                >
                    {widgets.map(renderWidget)}
                </GridLayout>
            )}
        </div>
    );
};

export default memo(DashboardCanvas);
