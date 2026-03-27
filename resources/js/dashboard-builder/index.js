/**
 * Dashboard Builder - Index
 *
 * Exporta todos los componentes y hooks del módulo
 */

// Componente principal
export { default as DashboardBuilder } from './DashboardBuilder';

// Componentes
export { default as EntityCatalogPanel } from './components/EntityCatalogPanel';
export { default as DashboardCanvas } from './components/DashboardCanvas';
export { default as WidgetPropertiesPanel } from './components/WidgetPropertiesPanel';
export { default as ScopeIndicator } from './components/ScopeIndicator';

// Widgets
export { default as DynamicWidgetRenderer } from './widgets/DynamicWidgetRenderer';
export { default as KpiWidget } from './widgets/KpiWidget';
export { default as ChartWidget } from './widgets/ChartWidget';
export { default as TableWidget } from './widgets/TableWidget';
export { default as TimelineWidget } from './widgets/TimelineWidget';
export { default as HeatmapWidget } from './widgets/HeatmapWidget';

// Hooks
export { useDashboardBuilder } from './hooks/useDashboardBuilder';
export { useWidgetQuery } from './hooks/useWidgetQuery';
export { useRealtimeSync } from './hooks/useRealtimeSync';

// Utilidades del renderer
export {
    createWidget,
    registerWidgetType,
    getAvailableWidgetTypes,
} from './widgets/DynamicWidgetRenderer';
