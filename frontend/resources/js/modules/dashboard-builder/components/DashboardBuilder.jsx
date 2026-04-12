/**
 * Archivo: frontend/resources/js/modules/dashboard-builder/components/DashboardBuilder.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// components/DashboardBuilder.jsx
import React, { useEffect } from 'react';
import { DndContext } from '@dnd-kit/core';
import useDashboardStore from '../store/dashboardStore.js';
import EntityPanel from './panels/EntityPanel.jsx';
import DashboardCanvas from './panels/DashboardCanvas.jsx';
import PropertiesPanel from './panels/PropertiesPanel.jsx';

export default function DashboardBuilder({ initialEntities, initialUser }) {
  const initialize = useDashboardStore(state => state.initialize);
  const widgets = useDashboardStore(state => state.widgets);
  const saveDashboard = useDashboardStore(state => state.saveDashboard);
  const clearDashboard = useDashboardStore(state => state.clearDashboard);

  // Inicializar store con datos del servidor
  useEffect(() => {
    initialize(initialEntities, initialUser);
  }, [initialize, initialEntities, initialUser]);

  const handleSave = async () => {
    if (widgets.length === 0) {
      alert('No hay widgets para guardar');
      return;
    }

    try {
      const result = await saveDashboard();
      alert(`Dashboard guardado: ${result.id}`);
    } catch (error) {
      alert('Error al guardar dashboard');
      console.error('Save error:', error);
    }
  };

  const handleClear = () => {
    if (widgets.length === 0) return;
    
    if (confirm('¿Estás seguro de que quieres limpiar todo el dashboard?')) {
      clearDashboard();
    }
  };

  return (
    <DndContext>
      <div className="dashboard-builder h-100">
        {/* Toolbar */}
        <div className="toolbar border-bottom bg-white p-3">
          <div className="d-flex justify-content-between align-items-center">
            <div className="d-flex align-items-center">
              <h5 className="fw-bold mb-0 me-4">
                <i className="fas fa-chart-line text-primary me-2"></i>
                Dashboard Builder
              </h5>
              <div className="d-flex align-items-center text-muted">
                <small className="me-3">
                  <i className="fas fa-user me-1"></i>
                  {initialUser?.name}
                </small>
                <small className="me-3">
                  <i className="fas fa-id-badge me-1"></i>
                  {initialUser?.role || 'Sin rol'}
                </small>
                <small>
                  <i className="fas fa-widgets me-1"></i>
                  {widgets.length} widget{widgets.length !== 1 ? 's' : ''}
                </small>
              </div>
            </div>
            
            <div className="d-flex gap-2">
              <button 
                className="btn btn-sm btn-outline-secondary"
                onClick={handleClear}
                disabled={widgets.length === 0}
                title="Limpiar dashboard"
              >
                <i className="fas fa-trash me-1"></i>
                Limpiar
              </button>
              <button 
                className="btn btn-sm btn-primary"
                onClick={handleSave}
                disabled={widgets.length === 0}
                title="Guardar dashboard"
              >
                <i className="fas fa-save me-1"></i>
                Guardar
              </button>
            </div>
          </div>
        </div>

        {/* Main Layout */}
        <div className="main-content h-100 d-flex">
          {/* Left Panel - Entities */}
          <div 
            className="left-panel bg-white border-end"
            style={{ width: '300px', minWidth: '300px' }}
          >
            <EntityPanel />
          </div>

          {/* Center Panel - Canvas */}
          <div className="center-panel flex-grow-1 bg-light">
            <DashboardCanvas />
          </div>

          {/* Right Panel - Properties */}
          <div 
            className="right-panel bg-white border-start"
            style={{ width: '350px', minWidth: '350px' }}
          >
            <PropertiesPanel />
          </div>
        </div>
      </div>
    </DndContext>
  );
}

// CSS adicional para el layout
const styles = `
  .dashboard-builder {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  }

  .main-content {
    height: calc(100vh - 120px); /* Ajustar según el header del sistema */
    min-height: 600px;
  }

  .left-panel,
  .right-panel {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 10;
  }

  .center-panel {
    position: relative;
    z-index: 1;
  }

  .toolbar {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    z-index: 20;
  }

  /* Responsive adjustments */
  @media (max-width: 1200px) {
    .left-panel {
      width: 250px !important;
      min-width: 250px !important;
    }
    
    .right-panel {
      width: 300px !important;
      min-width: 300px !important;
    }
  }

  @media (max-width: 992px) {
    .main-content {
      flex-direction: column;
    }
    
    .left-panel,
    .right-panel {
      width: 100% !important;
      min-width: auto !important;
      height: 200px;
    }
    
    .center-panel {
      flex-grow: 1;
    }
  }

  /* Scrollbar styling */
  .left-panel::-webkit-scrollbar,
  .right-panel::-webkit-scrollbar {
    width: 6px;
  }

  .left-panel::-webkit-scrollbar-track,
  .right-panel::-webkit-scrollbar-track {
    background: #f1f1f1;
  }

  .left-panel::-webkit-scrollbar-thumb,
  .right-panel::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
  }

  .left-panel::-webkit-scrollbar-thumb:hover,
  .right-panel::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }
`;

// Inyectar estilos
if (typeof document !== 'undefined') {
  const styleSheet = document.createElement('style');
  styleSheet.textContent = styles;
  document.head.appendChild(styleSheet);
}
