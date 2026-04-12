/**
 * Archivo: frontend/resources/js/modules/dashboard-builder/components/widgets/WidgetRenderer.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// components/widgets/WidgetRenderer.jsx
import React from 'react';
import BarChartWidget from './BarChart.jsx';
import LineChartWidget from './LineChart.jsx';
import PieChartWidget from './PieChart.jsx';
import AreaChartWidget from './AreaChart.jsx';
import MetricWidget from './Metric.jsx';
import TableWidget from './Table.jsx';

// Mapa de componentes por tipo de widget
const WIDGET_COMPONENTS = {
  bar: BarChartWidget,
  line: LineChartWidget,
  pie: PieChartWidget,
  area: AreaChartWidget,
  metric: MetricWidget,
  table: TableWidget
};

export default function WidgetRenderer({ widget, data, loading, error }) {
  // Obtener el componente correspondiente
  const WidgetComponent = WIDGET_COMPONENTS[widget.type];

  // Si no existe el tipo de widget, mostrar error
  if (!WidgetComponent) {
    return (
      <div className="h-100 d-flex align-items-center justify-content-center">
        <div className="text-center text-warning">
          <i className="fas fa-question-circle fa-2x mb-2"></i>
          <div>Tipo de widget desconocido: {widget.type}</div>
        </div>
      </div>
    );
  }

  // Renderizar el componente correspondiente
  return (
    <WidgetComponent
      data={data}
      loading={loading}
      error={error}
      title={widget.title}
      config={widget.config}
    />
  );
}
