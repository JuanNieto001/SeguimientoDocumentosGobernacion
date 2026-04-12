/**
 * Archivo: frontend/resources/js/modules/dashboard-builder/components/panels/PropertiesPanel.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// components/panels/PropertiesPanel.jsx
import React from 'react';
import useDashboardStore from '../../store/dashboardStore.js';

export default function PropertiesPanel() {
  const selectedWidget = useDashboardStore(state => state.selectedWidget);
  const widgets = useDashboardStore(state => state.widgets);
  const entities = useDashboardStore(state => state.entities);
  const updateWidget = useDashboardStore(state => state.updateWidget);
  const updateWidgetConfig = useDashboardStore(state => state.updateWidgetConfig);
  const removeWidget = useDashboardStore(state => state.removeWidget);

  const widget = widgets.find(w => w.id === selectedWidget);

  if (!widget) {
    return (
      <div className="h-100 d-flex flex-column">
        <div className="p-3 border-bottom bg-light">
          <h6 className="fw-bold mb-1">
            <i className="fas fa-cog text-primary me-2"></i>
            Propiedades
          </h6>
          <p className="text-muted small mb-0">
            Selecciona un widget para configurar
          </p>
        </div>
        
        <div className="flex-grow-1 d-flex align-items-center justify-content-center">
          <div className="text-center text-muted">
            <i className="fas fa-mouse-pointer fa-3x mb-3"></i>
            <div>Ningún widget seleccionado</div>
            <small>Haz clic en un widget del lienzo para configurarlo</small>
          </div>
        </div>
      </div>
    );
  }

  const entity = entities.find(e => e.key === widget.entity);
  if (!entity) {
    return (
      <div className="h-100 d-flex align-items-center justify-content-center">
        <div className="text-center text-danger">
          <i className="fas fa-exclamation-triangle fa-2x mb-2"></i>
          <div>Error: Entidad no encontrada</div>
        </div>
      </div>
    );
  }

  const handleTitleChange = (e) => {
    updateWidget(widget.id, { title: e.target.value });
  };

  const handleTypeChange = (e) => {
    updateWidget(widget.id, { type: e.target.value });
  };

  const handleAggregationTypeChange = (e) => {
    updateWidgetConfig(widget.id, {
      aggregation: { ...widget.config.aggregation, type: e.target.value }
    });
  };

  const handleAggregationFieldChange = (e) => {
    updateWidgetConfig(widget.id, {
      aggregation: { ...widget.config.aggregation, field: e.target.value || undefined }
    });
  };

  const handleGroupByChange = (fieldKey, checked) => {
    const currentGroupBy = widget.config.groupBy || [];
    const newGroupBy = checked 
      ? [...currentGroupBy, fieldKey]
      : currentGroupBy.filter(f => f !== fieldKey);
    
    updateWidgetConfig(widget.id, { groupBy: newGroupBy });
  };

  const handleLimitChange = (e) => {
    const value = parseInt(e.target.value) || 100;
    updateWidgetConfig(widget.id, { limit: value });
  };

  const handleDeleteWidget = () => {
    if (confirm('¿Estás seguro de que quieres eliminar este widget?')) {
      removeWidget(widget.id);
    }
  };

  const numberFields = Object.entries(entity.fields).filter(
    ([key, field]) => field.type === 'number'
  );

  return (
    <div className="h-100 d-flex flex-column">
      {/* Header */}
      <div className="p-3 border-bottom bg-light">
        <div className="d-flex justify-content-between align-items-center">
          <div>
            <h6 className="fw-bold mb-1">
              <i className="fas fa-cog text-primary me-2"></i>
              Propiedades
            </h6>
            <p className="text-muted small mb-0">
              Configurando: {entity.label}
            </p>
          </div>
          <button 
            className="btn btn-sm btn-outline-danger"
            onClick={handleDeleteWidget}
            title="Eliminar widget"
          >
            <i className="fas fa-trash"></i>
          </button>
        </div>
      </div>

      {/* Properties Form */}
      <div className="flex-grow-1 p-3" style={{ overflowY: 'auto' }}>
        <form>
          {/* Título */}
          <div className="mb-3">
            <label className="form-label fw-bold">Título del Widget</label>
            <input 
              type="text" 
              className="form-control"
              value={widget.title}
              onChange={handleTitleChange}
              placeholder="Ingresa un título"
            />
          </div>

          {/* Tipo de Widget */}
          <div className="mb-3">
            <label className="form-label fw-bold">Tipo de Gráfico</label>
            <select 
              className="form-select"
              value={widget.type}
              onChange={handleTypeChange}
            >
              <option value="bar">Barras</option>
              <option value="line">Líneas</option>
              <option value="pie">Circular</option>
              <option value="area">Área</option>
              <option value="metric">Métrica</option>
              <option value="table">Tabla</option>
            </select>
          </div>

          {/* Agregación */}
          <div className="mb-3">
            <label className="form-label fw-bold">Función de Agregación</label>
            <select 
              className="form-select mb-2"
              value={widget.config.aggregation.type}
              onChange={handleAggregationTypeChange}
            >
              <option value="count">Contar registros</option>
              <option value="sum">Sumar valores</option>
              <option value="avg">Promedio</option>
              <option value="count_distinct">Contar únicos</option>
            </select>

            {/* Campo para agregación */}
            {(widget.config.aggregation.type === 'sum' || 
              widget.config.aggregation.type === 'avg' ||
              widget.config.aggregation.type === 'count_distinct') && (
              <select 
                className="form-select"
                value={widget.config.aggregation.field || ''}
                onChange={handleAggregationFieldChange}
              >
                <option value="">Selecciona un campo</option>
                {numberFields.map(([fieldKey, field]) => (
                  <option key={fieldKey} value={fieldKey}>
                    {field.label}
                  </option>
                ))}
              </select>
            )}
          </div>

          {/* Agrupar por */}
          <div className="mb-3">
            <label className="form-label fw-bold">Agrupar por Campos</label>
            <div className="border rounded p-2" style={{ maxHeight: '150px', overflowY: 'auto' }}>
              {Object.entries(entity.fields).map(([fieldKey, field]) => (
                <div key={fieldKey} className="form-check">
                  <input 
                    className="form-check-input"
                    type="checkbox"
                    checked={(widget.config.groupBy || []).includes(fieldKey)}
                    onChange={(e) => handleGroupByChange(fieldKey, e.target.checked)}
                  />
                  <label className="form-check-label">
                    {field.label}
                    <small className="text-muted ms-1">({field.type})</small>
                  </label>
                </div>
              ))}
            </div>
          </div>

          {/* Límite de registros */}
          <div className="mb-3">
            <label className="form-label fw-bold">Límite de Registros</label>
            <input 
              type="number" 
              className="form-control"
              min="1"
              max="10000"
              value={widget.config.limit || 100}
              onChange={handleLimitChange}
            />
            <small className="form-text text-muted">
              Máximo 10,000 registros por consulta
            </small>
          </div>
        </form>
      </div>

      {/* Footer */}
      <div className="p-3 border-top bg-light">
        <small className="text-muted">
          <i className="fas fa-shield-alt me-1"></i>
          Los datos se filtran automáticamente según tu rol
        </small>
      </div>
    </div>
  );
}
