// components/widgets/PieChart.jsx
import React from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';

const COLORS = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'];

export default function PieChartWidget({ data, loading, error, title }) {
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
          <i className="fas fa-chart-pie fa-2x mb-2"></i>
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

  const totalValue = chartData.reduce((sum, item) => sum + item.value, 0);

  return (
    <div className="h-100">
      <div className="mb-2">
        <h6 className="fw-bold text-truncate">{title}</h6>
      </div>
      <div style={{ width: '100%', height: 'calc(100% - 40px)' }}>
        <ResponsiveContainer width="100%" height="100%">
          <PieChart>
            <Pie
              data={chartData}
              cx="50%"
              cy="50%"
              labelLine={false}
              label={({ percent }) => percent > 5 ? `${(percent * 100).toFixed(0)}%` : ''}
              outerRadius={80}
              fill="#8884d8"
              dataKey="value"
            >
              {chartData.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
              ))}
            </Pie>
            <Tooltip 
              formatter={(value) => [value, 'Cantidad']}
              labelStyle={{ color: '#333' }}
            />
            <Legend 
              wrapperStyle={{ fontSize: '12px' }}
              formatter={(value) => value.length > 15 ? `${value.substring(0, 15)}...` : value}
            />
          </PieChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}