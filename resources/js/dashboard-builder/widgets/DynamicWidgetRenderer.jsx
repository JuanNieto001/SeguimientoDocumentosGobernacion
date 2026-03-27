/**
 * DYNAMIC WIDGET RENDERER
 *
 * Renderiza componentes dinámicamente según el tipo de widget.
 * NO hay componentes fijos - todo se determina en runtime.
 */

import React, { memo, useMemo, Suspense, lazy } from 'react';

// Componentes de widgets cargados de forma lazy
const KpiWidget = lazy(() => import('./KpiWidget'));
const ChartWidget = lazy(() => import('./ChartWidget'));
const TableWidget = lazy(() => import('./TableWidget'));
const TimelineWidget = lazy(() => import('./TimelineWidget'));
const HeatmapWidget = lazy(() => import('./HeatmapWidget'));

// Registry de tipos de widget → componentes
const WIDGET_REGISTRY = {
    kpi: KpiWidget,
    chart: ChartWidget,
    table: TableWidget,
    timeline: TimelineWidget,
    heatmap: HeatmapWidget,
};

// Loading fallback
const WidgetLoadingFallback = () => (
    <div className="flex items-center justify-center h-full">
        <div className="animate-pulse flex flex-col items-center">
            <div className="w-8 h-8 bg-gray-200 rounded-full mb-2"></div>
            <div className="w-20 h-3 bg-gray-200 rounded"></div>
        </div>
    </div>
);

// Error fallback
const WidgetErrorFallback = ({ error, widget }) => (
    <div className="flex flex-col items-center justify-center h-full text-red-500 p-4">
        <svg className="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div className="text-sm font-medium text-center">{error}</div>
        <div className="text-xs text-gray-400 mt-1">
            {widget.entity} • {widget.tipo}
        </div>
    </div>
);

// Sin datos fallback
const NoDataFallback = ({ widget }) => (
    <div className="flex flex-col items-center justify-center h-full text-gray-400 p-4">
        <svg className="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <div className="text-sm font-medium">Sin datos</div>
        <div className="text-xs mt-1">
            Intenta modificar los filtros
        </div>
    </div>
);

// Cargando datos
const LoadingData = () => (
    <div className="flex items-center justify-center h-full">
        <div className="flex flex-col items-center">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mb-2"></div>
            <div className="text-xs text-gray-400">Cargando datos...</div>
        </div>
    </div>
);

/**
 * Componente principal que renderiza widgets dinámicamente
 */
const DynamicWidgetRenderer = ({ widget, data, error, isLoading }) => {
    // Determinar qué componente renderizar basado en el tipo
    const WidgetComponent = useMemo(() => {
        const tipo = widget?.tipo || 'kpi';
        return WIDGET_REGISTRY[tipo] || WIDGET_REGISTRY.kpi;
    }, [widget?.tipo]);

    // Estados de error y carga
    if (error) {
        return <WidgetErrorFallback error={error} widget={widget} />;
    }

    if (isLoading || !data) {
        return <LoadingData />;
    }

    // Verificar si hay datos
    const hasData = checkHasData(widget.tipo, data);
    if (!hasData) {
        return <NoDataFallback widget={widget} />;
    }

    // Renderizar componente dinámico
    return (
        <Suspense fallback={<WidgetLoadingFallback />}>
            <WidgetComponent
                widget={widget}
                data={data.data}
                meta={data.meta}
            />
        </Suspense>
    );
};

// Helper para verificar si hay datos
function checkHasData(tipo, data) {
    if (!data || !data.data) return false;

    switch (tipo) {
        case 'kpi':
            return data.data.value !== undefined && data.data.value !== null;
        case 'chart':
            return data.data.labels?.length > 0 || data.data.values?.length > 0;
        case 'table':
            return data.data.rows?.length > 0;
        case 'timeline':
            return data.data.timeline?.length > 0;
        case 'heatmap':
            return data.data.matrix?.length > 0;
        default:
            return true;
    }
}

/**
 * Factory function para crear widgets programáticamente
 */
export function createWidget(config) {
    return {
        id: config.id || `widget_${Date.now()}`,
        entity: config.entity,
        tipo: config.tipo || 'kpi',
        metrica: config.metrica || 'count',
        dimension: config.dimension || null,
        titulo: config.titulo || 'Widget',
        filters: config.filters || [],
        chartType: config.chartType || 'bar',
        columns: config.columns || [],
        limit: config.limit || 10,
        config: config.config || { ancho: 3, alto: 2 },
    };
}

/**
 * Registrar nuevo tipo de widget en runtime
 */
export function registerWidgetType(tipo, Component) {
    WIDGET_REGISTRY[tipo] = Component;
}

/**
 * Obtener tipos de widget disponibles
 */
export function getAvailableWidgetTypes() {
    return Object.keys(WIDGET_REGISTRY);
}

export default memo(DynamicWidgetRenderer);
