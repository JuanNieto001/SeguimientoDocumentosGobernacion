// components/panels/EntityPanel.jsx
import React from 'react';
import useDashboardStore from '../../store/dashboardStore.js';

export default function EntityPanel() {
  const entities = useDashboardStore(state => state.entities);
  const setDraggedEntity = useDashboardStore(state => state.setDraggedEntity);

  const handleDragStart = (e, entity) => {
    setDraggedEntity(entity);
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('text/plain', entity.key);
  };

  const handleDragEnd = () => {
    setDraggedEntity(null);
  };

  return (
    <div className="h-100 d-flex flex-column">
      <div className="p-3 border-bottom bg-light">
        <h6 className="fw-bold mb-1">
          <i className="fas fa-database text-primary me-2"></i>
          Entidades de Datos
        </h6>
        <p className="text-muted small mb-0">
          Arrastra una entidad al lienzo para crear un widget
        </p>
      </div>
      
      <div className="flex-grow-1 p-3" style={{ overflowY: 'auto' }}>
        {entities.length === 0 ? (
          <div className="text-center text-muted">
            <i className="fas fa-spinner fa-spin fa-2x mb-2"></i>
            <div>Cargando entidades...</div>
          </div>
        ) : (
          <div className="d-flex flex-column gap-2">
            {entities.map(entity => (
              <div
                key={entity.key}
                className="card cursor-pointer entity-card"
                draggable
                onDragStart={(e) => handleDragStart(e, entity)}
                onDragEnd={handleDragEnd}
                style={{ 
                  cursor: 'grab',
                  transition: 'all 0.2s ease'
                }}
              >
                <div className="card-body p-3">
                  <div className="d-flex align-items-center">
                    <div className="me-3">
                      <i className="fas fa-table text-primary fa-lg"></i>
                    </div>
                    <div className="flex-grow-1">
                      <h6 className="card-title mb-1 fw-bold">
                        {entity.label}
                      </h6>
                      <p className="card-text text-muted small mb-1">
                        {Object.keys(entity.fields).length} campos disponibles
                      </p>
                      <div className="d-flex flex-wrap gap-1">
                        {Object.entries(entity.fields)
                          .slice(0, 3)
                          .map(([fieldKey, field]) => (
                            <span 
                              key={fieldKey}
                              className="badge bg-secondary"
                              style={{ fontSize: '0.7rem' }}
                            >
                              {field.label}
                            </span>
                          ))
                        }
                        {Object.keys(entity.fields).length > 3 && (
                          <span 
                            className="badge bg-light text-muted"
                            style={{ fontSize: '0.7rem' }}
                          >
                            +{Object.keys(entity.fields).length - 3} más
                          </span>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="p-3 border-top bg-light">
        <small className="text-muted">
          <i className="fas fa-info-circle me-1"></i>
          Los datos se filtran automáticamente según tu rol
        </small>
      </div>
    </div>
  );
}

// CSS adicional para las animaciones
const styles = `
  .entity-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
  }

  .entity-card:active {
    cursor: grabbing !important;
    transform: scale(0.98);
  }

  .cursor-pointer {
    cursor: pointer;
  }
`;

// Inyectar estilos
if (typeof document !== 'undefined') {
  const styleSheet = document.createElement('style');
  styleSheet.textContent = styles;
  document.head.appendChild(styleSheet);
}