// components/widgets/Metric.jsx
import React from 'react';

export default function MetricWidget({ data, loading, error, title }) {
  if (loading) {
    return (
      <div className="h-100 d-flex align-items-center justify-content-center">
        <div className="text-center">
          <div className="spinner-border text-primary mb-2" role="status"></div>
          <div className="text-muted">Cargando...</div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="h-100 d-flex align-items-center justify-content-center">
        <div className="text-center text-danger">
          <i className="fas fa-exclamation-triangle fa-2x mb-2"></i>
          <div>Error al cargar datos</div>
          <small className="text-muted">{error}</small>
        </div>
      </div>
    );
  }

  // Extraer valor único
  let value = 0;
  if (data && data.length > 0) {
    const firstItem = data[0];
    const valueKey = Object.keys(firstItem).find(key => 
      key === 'value' || typeof firstItem[key] === 'number'
    );
    value = Number(firstItem[valueKey] || 0);
  }

  // Formatear valor
  const formatValue = (val) => {
    if (val >= 1000000) {
      return `${(val / 1000000).toFixed(1)}M`;
    } else if (val >= 1000) {
      return `${(val / 1000).toFixed(1)}K`;
    }
    return val.toLocaleString();
  };

  return (
    <div className="h-100 d-flex flex-column justify-content-center text-center p-3">
      <div className="mb-2">
        <h6 className="fw-bold text-muted text-uppercase small">{title}</h6>
      </div>
      <div className="flex-grow-1 d-flex align-items-center justify-content-center">
        <div>
          <div className="display-4 fw-bold text-primary mb-1">
            {formatValue(value)}
          </div>
          <div className="text-muted small">
            {data && data.length > 0 ? 'Total' : 'Sin datos'}
          </div>
        </div>
      </div>
    </div>
  );
}