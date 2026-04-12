/**
 * Archivo: frontend/resources/js/modules/dashboard-builder/store/dashboardStore.js
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// store/dashboardStore.js
import { create } from 'zustand';
import { nanoid } from 'nanoid';

const useDashboardStore = create((set, get) => ({
  // State
  widgets: [],
  selectedWidget: null,
  draggedEntity: null,
  entities: [],
  user: null,

  // Inicializar store con datos del servidor
  initialize: (entities, user) => {
    set({ entities, user });
  },

  // Gestión de widgets
  addWidget: (entity, x = 0, y = 0) => {
    const newWidget = {
      id: nanoid(8),
      type: 'bar',
      title: `Nuevo Widget - ${entity.label}`,
      entity: entity.key,
      x,
      y,
      w: 6,
      h: 4,
      config: {
        aggregation: { type: 'count' },
        groupBy: [],
        filters: [],
        limit: 100
      }
    };

    set(state => ({
      widgets: [...state.widgets, newWidget],
      selectedWidget: newWidget.id
    }));

    return newWidget.id;
  },

  removeWidget: (widgetId) => {
    set(state => ({
      widgets: state.widgets.filter(w => w.id !== widgetId),
      selectedWidget: state.selectedWidget === widgetId ? null : state.selectedWidget
    }));
  },

  updateWidget: (widgetId, updates) => {
    set(state => ({
      widgets: state.widgets.map(widget => 
        widget.id === widgetId 
          ? { ...widget, ...updates }
          : widget
      )
    }));
  },

  updateWidgetConfig: (widgetId, configUpdates) => {
    set(state => ({
      widgets: state.widgets.map(widget => 
        widget.id === widgetId 
          ? { ...widget, config: { ...widget.config, ...configUpdates } }
          : widget
      )
    }));
  },

  // Gestión de selección
  selectWidget: (widgetId) => {
    set({ selectedWidget: widgetId });
  },

  deselectWidget: () => {
    set({ selectedWidget: null });
  },

  // Gestión de drag & drop
  setDraggedEntity: (entity) => {
    set({ draggedEntity: entity });
  },

  clearDraggedEntity: () => {
    set({ draggedEntity: null });
  },

  // Layout updates (para react-grid-layout)
  updateLayout: (layout) => {
    set(state => ({
      widgets: state.widgets.map(widget => {
        const layoutItem = layout.find(item => item.i === widget.id);
        if (layoutItem) {
          return {
            ...widget,
            x: layoutItem.x,
            y: layoutItem.y,
            w: layoutItem.w,
            h: layoutItem.h
          };
        }
        return widget;
      })
    }));
  },

  // Utilidades
  getWidget: (widgetId) => {
    return get().widgets.find(w => w.id === widgetId);
  },

  getSelectedWidget: () => {
    const { widgets, selectedWidget } = get();
    return widgets.find(w => w.id === selectedWidget);
  },

  getEntity: (entityKey) => {
    return get().entities.find(e => e.key === entityKey);
  },

  // Dashboard management (placeholder para futuras versiones)
  saveDashboard: async () => {
    const { widgets } = get();
    
    try {
      const response = await fetch('/dashboard/builder', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ widgets })
      });

      const result = await response.json();
      return result;
    } catch (error) {
      console.error('Error al guardar dashboard:', error);
      throw error;
    }
  },

  clearDashboard: () => {
    set({
      widgets: [],
      selectedWidget: null
    });
  }
}));

export default useDashboardStore;
