/**
 * Archivo: frontend/resources/js/modules/dashboard-builder/components/panels/DashboardCanvas.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// components/panels/DashboardCanvas.jsx
import React from 'react';
import { Responsive } from 'react-grid-layout';
import useDashboardStore from '../../store/dashboardStore.js';
import { useWidgetData } from '../../hooks/useWidgetData.ts';
import WidgetRenderer from '../widgets/WidgetRenderer.jsx';

// Grid layout responsivo - usar directamente Responsive
const ResponsiveGridLayout = Responsive;

export default function DashboardCanvas() {
  const widgets = useDashboardStore(state => state.widgets);
  const selectedWidget = useDashboardStore(state => state.selectedWidget);
  const draggedEntity = useDashboardStore(state => state.draggedEntity);
  const addWidget = useDashboardStore(state => state.addWidget);
  const updateLayout = useDashboardStore(state => state.updateLayout);
  const selectWidget = useDashboardStore(state => state.selectWidget);
  const deselectWidget = useDashboardStore(state => state.deselectWidget);
  const clearDraggedEntity = useDashboardStore(state => state.clearDraggedEntity);

  // Preparar layout para react-grid-layout
  const layout = widgets.map(widget => ({
    i: widget.id,
    x: widget.x,
    y: widget.y,
    w: widget.w,
    h: widget.h
  }));

  const handleLayoutChange = (newLayout) => {
    updateLayout(newLayout);
  };

  const handleDrop = (e) => {
    e.preventDefault();
    
    if (!draggedEntity) return;

    // Calcular posición relativa en el grid
    const rect = e.currentTarget.getBoundingClientRect();
    const x = Math.floor((e.clientX - rect.left) / 200); // 200px aprox por columna
    const y = Math.floor((e.clientY - rect.top) / 150);  // 150px aprox por fila

    addWidget(draggedEntity, x, y);
    clearDraggedEntity();
  };

  const handleDragOver = (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  };

  const handleCanvasClick = () => {
    deselectWidget();
  };

  return (
    <div className="h-100 d-flex flex-column">
      {/* Header */}
      <div className="p-3 border-bottom bg-light">
        <div className="d-flex justify-content-between align-items-center">
          <div>
            <h6 className="fw-bold mb-1">
              <i className="fas fa-chart-bar text-primary me-2"></i>
              Lienzo del Dashboard
            </h6>
            <p className="text-muted small mb-0">
              {widgets.length} widget{widgets.length !== 1 ? 's' : ''} en el dashboard
            </p>
          </div>
          <div className="text-end">
            {draggedEntity && (
              <small className="text-primary">
                <i className="fas fa-hand-point-right me-1"></i>
                Suelta aquí para crear widget
              </small>
            )}
          </div>
        </div>
      </div>

      {/* Canvas */}
      <div 
        className="flex-grow-1 p-3" 
        style={{ 
          overflowY: 'auto',
          backgroundColor: '#f8f9fc',
          minHeight: '500px'
        }}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onClick={handleCanvasClick}
      >
        {widgets.length === 0 ? (
          <div className="h-100 d-flex align-items-center justify-content-center">
            <div className="text-center text-muted">
              <i className="fas fa-plus-circle fa-3x mb-3"></i>
              <h5>Lienzo vacío</h5>
              <p>Arrastra una entidad desde el panel izquierdo para comenzar</p>
              {draggedEntity && (
                <div className="alert alert-info mt-3">
                  <i className="fas fa-info-circle me-2"></i>
                  Suelta aquí para crear un widget con "{draggedEntity.label}"
                </div>
              )}
            </div>
          </div>
        ) : (
          <ResponsiveGridLayout
            className="layout"
            layouts={{ lg: layout }}
            breakpoints={{ lg: 1200, md: 996, sm: 768, xs: 480, xxs: 0 }}
            cols={{ lg: 12, md: 10, sm: 6, xs: 4, xxs: 2 }}
            rowHeight={120}
            onLayoutChange={handleLayoutChange}
            isDraggable={true}
            isResizable={true}
          >
            {widgets.map(widget => (
              <div 
                key={widget.id}
                className={`widget-container ${selectedWidget === widget.id ? 'selected' : ''}`}
                onClick={(e) => {
                  e.stopPropagation();
                  selectWidget(widget.id);
                }}
              >
                <WidgetCard widget={widget} />
              </div>
            ))}
          </ResponsiveGridLayout>
        )}
      </div>
    </div>
  );
}

// Componente individual de widget
function WidgetCard({ widget }) {
  const { data, loading, error } = useWidgetData(widget);

  return (
    <div className="widget-card h-100 bg-white rounded shadow-sm border p-3">
      <WidgetRenderer 
        widget={widget}
        data={data}
        loading={loading}
        error={error}
      />
    </div>
  );
}

// CSS adicional
const styles = `
  .widget-container {
    transition: all 0.2s ease;
  }

  .widget-container.selected .widget-card {
    border: 2px solid #3B82F6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
  }

  .widget-card {
    border: 1px solid #e5e7eb;
    cursor: pointer;
  }

  .widget-card:hover {
    border-color: #9CA3AF;
  }

  .react-grid-item.react-grid-placeholder {
    background: rgba(59, 130, 246, 0.2) !important;
    border: 2px dashed #3B82F6 !important;
  }

  .react-grid-item > .react-resizable-handle::after {
    border-right: 3px solid #3B82F6;
    border-bottom: 3px solid #3B82F6;
  }
`;

// Inyectar estilos
if (typeof document !== 'undefined') {
  const styleSheet = document.createElement('style');
  styleSheet.textContent = styles;
  document.head.appendChild(styleSheet);
}
