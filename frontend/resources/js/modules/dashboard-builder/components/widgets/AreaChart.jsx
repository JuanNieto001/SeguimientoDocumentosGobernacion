/**
 * Archivo: frontend/resources/js/modules/dashboard-builder/components/widgets/AreaChart.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// components/widgets/AreaChart.jsx
import React from 'react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

export default function AreaChartWidget({ data, loading, error, title }) {
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
          <i className="fas fa-chart-area fa-2x mb-2"></i>
          <div>Sin datos para mostrar</div>
        </div>
      </div>
    );
  }

  // Procesar datos para el gráfico
  const chartData = data.map(item => {
    const keys = Object.keys(item);
    const valueKey = keys.find(key => key === 'value' || typeof item[key] === 'number');
    const labelKey = keys.find(key => key !== valueKey);
    
    return {
      name: item[labelKey] || 'Sin etiqueta',
      value: Number(item[valueKey] || 0)
    };
  });

  return (
    <div className="h-100">
      <div className="mb-2">
        <h6 className="fw-bold text-truncate">{title}</h6>
      </div>
      <div style={{ width: '100%', height: 'calc(100% - 40px)' }}>
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={chartData} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
            <defs>
              <linearGradient id="colorValue" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#8B5CF6" stopOpacity={0.8}/>
                <stop offset="95%" stopColor="#8B5CF6" stopOpacity={0}/>
              </linearGradient>
            </defs>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis 
              dataKey="name" 
              tick={{ fontSize: 12 }}
              interval={0}
              angle={-45}
              textAnchor="end"
              height={60}
            />
            <YAxis tick={{ fontSize: 12 }} />
            <Tooltip 
              formatter={(value) => [value, 'Valor']}
              labelStyle={{ color: '#333' }}
            />
            <Area 
              type="monotone" 
              dataKey="value" 
              stroke="#8B5CF6" 
              fillOpacity={1} 
              fill="url(#colorValue)" 
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
