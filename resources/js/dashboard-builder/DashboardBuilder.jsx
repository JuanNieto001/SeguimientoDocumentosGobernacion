/**
 * DASHBOARD BUILDER - Motor Visual Dinámico
 *
 * Sistema de construcción de dashboards en tiempo real con:
 * - Panel izquierdo: Catálogo de entidades y campos
 * - Canvas central: Drag-and-drop de widgets
 * - Panel derecho: Propiedades del widget seleccionado
 *
 * Características:
 * - Queries dinámicas construidas en runtime
 * - Filtros automáticos por rol (scope_level)
 * - Renderizado dinámico de componentes
 * - Actualizaciones en tiempo real
 */

import React, { useState, useCallback, useEffect, useMemo } from 'react';
import { DndProvider } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';
import GridLayout from 'react-grid-layout';

import EntityCatalogPanel from './components/EntityCatalogPanel';
import DashboardCanvas from './components/DashboardCanvas';
import WidgetPropertiesPanel from './components/WidgetPropertiesPanel';
import DynamicWidgetRenderer from './widgets/DynamicWidgetRenderer';
import ScopeIndicator from './components/ScopeIndicator';

import { useDashboardBuilder } from './hooks/useDashboardBuilder';
import { useWidgetQuery } from './hooks/useWidgetQuery';
import { useRealtimeSync } from './hooks/useRealtimeSync';

import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';

const DashboardBuilder = ({ initialConfig = null, readOnly = false }) => {
    // Estado principal del builder
    const {
        widgets,
        layout,
        selectedWidgetId,
        catalog,
        userScope,
        isLoading,
        error,
        addWidget,
        updateWidget,
        removeWidget,
        selectWidget,
        updateLayout,
        saveDashboard,
        loadDashboard,
    } = useDashboardBuilder(initialConfig);

    // Hook para ejecutar queries de widgets
    const { executeWidget, executeAllWidgets, widgetData, widgetErrors } = useWidgetQuery();

    // Hook para sincronización en tiempo real
    const { isConnected, lastUpdate } = useRealtimeSync(widgets, (updatedData) => {
        // Callback cuando llegan actualizaciones en tiempo real
        Object.keys(updatedData).forEach(widgetId => {
            if (widgets.find(w => w.id === widgetId)) {
                // Actualizar datos del widget
            }
        });
    });

    // Estado UI
    const [isEditMode, setIsEditMode] = useState(!readOnly);
    const [showSaveModal, setShowSaveModal] = useState(false);
    const [dashboardName, setDashboardName] = useState('Mi Dashboard');

    // Cargar datos iniciales de todos los widgets
    useEffect(() => {
        if (widgets.length > 0) {
            executeAllWidgets(widgets);
        }
    }, [widgets.length]);

    // Manejar drop de campo desde el catálogo
    const handleFieldDrop = useCallback((fieldInfo, position) => {
        const { entity, field, entityConfig, fieldConfig } = fieldInfo;

        // Determinar tipo de widget automáticamente
        const widgetType = inferWidgetType(fieldConfig);
        const metrica = inferMetrica(fieldConfig);

        const newWidget = {
            id: `widget_${Date.now()}`,
            entity,
            tipo: widgetType,
            metrica,
            dimension: fieldConfig.aggregatable ? field : null,
            titulo: `${entityConfig.label} - ${fieldConfig.label}`,
            filters: [],
            chartType: widgetType === 'chart' ? 'bar' : null,
            columns: widgetType === 'table' ? [field, 'id', 'created_at'] : null,
            config: {
                ancho: widgetType === 'table' ? 6 : 3,
                alto: widgetType === 'table' ? 4 : 2,
            },
        };

        addWidget(newWidget, position);

        // Ejecutar query inmediatamente
        executeWidget(newWidget);
    }, [addWidget, executeWidget]);

    // Manejar cambios en propiedades del widget
    const handleWidgetConfigChange = useCallback((widgetId, newConfig) => {
        updateWidget(widgetId, newConfig);

        // Re-ejecutar query con nueva configuración
        const updatedWidget = { ...widgets.find(w => w.id === widgetId), ...newConfig };
        executeWidget(updatedWidget);
    }, [widgets, updateWidget, executeWidget]);

    // Manejar resize/move de widgets
    const handleLayoutChange = useCallback((newLayout) => {
        updateLayout(newLayout);
    }, [updateLayout]);

    // Guardar dashboard
    const handleSave = async () => {
        try {
            await saveDashboard(dashboardName);
            setShowSaveModal(false);
        } catch (err) {
            console.error('Error guardando dashboard:', err);
        }
    };

    // Widget seleccionado
    const selectedWidget = useMemo(() =>
        widgets.find(w => w.id === selectedWidgetId),
        [widgets, selectedWidgetId]
    );

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen bg-gray-100">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-green-600 mx-auto"></div>
                    <p className="mt-4 text-gray-600">Cargando Dashboard Builder...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen bg-red-50">
                <div className="text-center text-red-600">
                    <svg className="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p className="mt-4">{error}</p>
                </div>
            </div>
        );
    }

    return (
        <DndProvider backend={HTML5Backend}>
            <div className="h-screen flex flex-col bg-gray-100">
                {/* Header */}
                <header className="bg-white shadow-sm border-b border-gray-200 px-4 py-3">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-4">
                            <h1 className="text-xl font-bold text-gray-800">
                                Dashboard Builder
                            </h1>
                            <ScopeIndicator scope={userScope} />
                            {isConnected && (
                                <span className="flex items-center text-xs text-green-600">
                                    <span className="w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                                    Tiempo real
                                </span>
                            )}
                        </div>

                        <div className="flex items-center space-x-3">
                            {/* Toggle modo edición */}
                            {!readOnly && (
                                <button
                                    onClick={() => setIsEditMode(!isEditMode)}
                                    className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${
                                        isEditMode
                                            ? 'bg-blue-100 text-blue-700'
                                            : 'bg-gray-100 text-gray-600'
                                    }`}
                                >
                                    {isEditMode ? '🎨 Editando' : '👁️ Visualizando'}
                                </button>
                            )}

                            {/* Botón guardar */}
                            {isEditMode && (
                                <button
                                    onClick={() => setShowSaveModal(true)}
                                    className="px-4 py-1.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors"
                                >
                                    💾 Guardar
                                </button>
                            )}

                            {/* Botón refrescar */}
                            <button
                                onClick={() => executeAllWidgets(widgets)}
                                className="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition-colors"
                            >
                                🔄 Refrescar
                            </button>
                        </div>
                    </div>
                </header>

                {/* Main content */}
                <div className="flex-1 flex overflow-hidden">
                    {/* Panel izquierdo: Catálogo de entidades */}
                    {isEditMode && (
                        <EntityCatalogPanel
                            catalog={catalog}
                            onFieldDrag={(fieldInfo) => fieldInfo}
                        />
                    )}

                    {/* Canvas central */}
                    <main className={`flex-1 overflow-auto p-4 ${isEditMode ? '' : 'mx-auto max-w-7xl'}`}>
                        <DashboardCanvas
                            widgets={widgets}
                            layout={layout}
                            widgetData={widgetData}
                            widgetErrors={widgetErrors}
                            selectedWidgetId={selectedWidgetId}
                            isEditMode={isEditMode}
                            onLayoutChange={handleLayoutChange}
                            onWidgetSelect={selectWidget}
                            onWidgetRemove={removeWidget}
                            onFieldDrop={handleFieldDrop}
                        />
                    </main>

                    {/* Panel derecho: Propiedades del widget */}
                    {isEditMode && selectedWidget && (
                        <WidgetPropertiesPanel
                            widget={selectedWidget}
                            catalog={catalog}
                            onChange={handleWidgetConfigChange}
                            onClose={() => selectWidget(null)}
                        />
                    )}
                </div>

                {/* Modal de guardado */}
                {showSaveModal && (
                    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div className="bg-white rounded-xl shadow-xl p-6 w-96">
                            <h3 className="text-lg font-bold text-gray-800 mb-4">
                                Guardar Dashboard
                            </h3>
                            <input
                                type="text"
                                value={dashboardName}
                                onChange={(e) => setDashboardName(e.target.value)}
                                placeholder="Nombre del dashboard"
                                className="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-green-500"
                            />
                            <div className="flex justify-end space-x-3">
                                <button
                                    onClick={() => setShowSaveModal(false)}
                                    className="px-4 py-2 text-gray-600 hover:text-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    onClick={handleSave}
                                    className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                                >
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </DndProvider>
    );
};

// Helpers para inferir tipo de widget automáticamente
function inferWidgetType(fieldConfig) {
    if (fieldConfig.type === 'datetime') return 'timeline';
    if (fieldConfig.type === 'enum' || fieldConfig.type === 'relation') return 'chart';
    if (fieldConfig.type === 'decimal' || fieldConfig.type === 'integer') return 'kpi';
    if (fieldConfig.type === 'boolean') return 'kpi';
    return 'table';
}

function inferMetrica(fieldConfig) {
    if (fieldConfig.format === 'currency') return 'sum';
    if (fieldConfig.type === 'integer' || fieldConfig.type === 'decimal') return 'sum';
    return 'count';
}

export default DashboardBuilder;
