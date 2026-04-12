/**
 * Archivo: frontend/resources/js/modules/dashboard-builder/components/widgets/Table.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// components/widgets/Table.jsx
import React from 'react';

export default function TableWidget({ data, loading, error, title }) {
  if (loading) {
    return (
      <div className="h-100 d-flex align-items-center justify-content-center">
        <div className="text-center">
          <div className="spinner-border text-primary mb-2" role="status"></div>
          <div className="text-muted">Cargando datos...</div>
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

  if (!data || data.length === 0) {
    return (
      <div className="h-100 d-flex align-items-center justify-content-center">
        <div className="text-center text-muted">
          <i className="fas fa-table fa-2x mb-2"></i>
          <div>Sin datos para mostrar</div>
        </div>
      </div>
    );
  }

  // Obtener columnas de los datos
  const columns = data.length > 0 ? Object.keys(data[0]) : [];
  
  // Formatear nombres de columnas
  const formatColumnName = (name) => {
    return name
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  };

  // Formatear valores de celdas
  const formatCellValue = (value) => {
    if (value === null || value === undefined) return '-';
    if (typeof value === 'number') {
      return value.toLocaleString();
    }
    return String(value);
  };

  return (
    <div className="h-100 d-flex flex-column">
      <div className="mb-2">
        <h6 className="fw-bold text-truncate">{title}</h6>
      </div>
      <div className="flex-grow-1" style={{ overflow: 'auto' }}>
        <table className="table table-sm table-striped">
          <thead className="table-dark">
            <tr>
              {columns.map(column => (
                <th key={column} className="text-nowrap" style={{ fontSize: '0.8rem' }}>
                  {formatColumnName(column)}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {data.map((row, index) => (
              <tr key={index}>
                {columns.map(column => (
                  <td key={column} style={{ fontSize: '0.8rem' }}>
                    {formatCellValue(row[column])}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <div className="mt-2 text-muted small">
        Mostrando {data.length} registro{data.length !== 1 ? 's' : ''}
      </div>
    </div>
  );
}
