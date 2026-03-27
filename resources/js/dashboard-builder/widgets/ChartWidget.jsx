/**
 * Chart Widget
 *
 * Renderiza gráficas dinámicamente usando Recharts.
 * Soporta: bar, line, pie, doughnut, area, radar
 */

import React, { memo, useMemo } from 'react';
import {
    BarChart, Bar,
    LineChart, Line,
    PieChart, Pie, Cell,
    AreaChart, Area,
    RadarChart, Radar, PolarGrid, PolarAngleAxis, PolarRadiusAxis,
    XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer
} from 'recharts';

// Paleta de colores institucionales
const COLORS = [
    '#059669', // green-600
    '#0284c7', // sky-600
    '#7c3aed', // violet-600
    '#db2777', // pink-600
    '#ea580c', // orange-600
    '#ca8a04', // yellow-600
    '#2563eb', // blue-600
    '#dc2626', // red-600
    '#65a30d', // lime-600
    '#0891b2', // cyan-600
];

const ChartWidget = ({ widget, data, meta }) => {
    // Preparar datos para Recharts
    const chartData = useMemo(() => {
        if (!data || !data.labels || !data.values) return [];

        return data.labels.map((label, index) => ({
            name: label || 'N/A',
            value: data.values[index] || 0,
            fill: COLORS[index % COLORS.length],
        }));
    }, [data]);

    const chartType = widget.chartType || 'bar';

    // Renderizar según tipo
    const renderChart = () => {
        switch (chartType) {
            case 'bar':
                return (
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart data={chartData} margin={{ top: 5, right: 10, left: 0, bottom: 5 }}>
                            <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                            <XAxis
                                dataKey="name"
                                tick={{ fontSize: 10 }}
                                tickLine={false}
                                axisLine={{ stroke: '#e5e7eb' }}
                            />
                            <YAxis
                                tick={{ fontSize: 10 }}
                                tickLine={false}
                                axisLine={{ stroke: '#e5e7eb' }}
                                tickFormatter={formatAxisValue}
                            />
                            <Tooltip
                                formatter={formatTooltipValue}
                                contentStyle={tooltipStyle}
                            />
                            <Bar dataKey="value" radius={[4, 4, 0, 0]}>
                                {chartData.map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={entry.fill} />
                                ))}
                            </Bar>
                        </BarChart>
                    </ResponsiveContainer>
                );

            case 'line':
                return (
                    <ResponsiveContainer width="100%" height="100%">
                        <LineChart data={chartData} margin={{ top: 5, right: 10, left: 0, bottom: 5 }}>
                            <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                            <XAxis
                                dataKey="name"
                                tick={{ fontSize: 10 }}
                                tickLine={false}
                            />
                            <YAxis
                                tick={{ fontSize: 10 }}
                                tickLine={false}
                                tickFormatter={formatAxisValue}
                            />
                            <Tooltip
                                formatter={formatTooltipValue}
                                contentStyle={tooltipStyle}
                            />
                            <Line
                                type="monotone"
                                dataKey="value"
                                stroke={COLORS[0]}
                                strokeWidth={2}
                                dot={{ fill: COLORS[0], strokeWidth: 2, r: 4 }}
                                activeDot={{ r: 6 }}
                            />
                        </LineChart>
                    </ResponsiveContainer>
                );

            case 'area':
                return (
                    <ResponsiveContainer width="100%" height="100%">
                        <AreaChart data={chartData} margin={{ top: 5, right: 10, left: 0, bottom: 5 }}>
                            <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                            <XAxis dataKey="name" tick={{ fontSize: 10 }} />
                            <YAxis tick={{ fontSize: 10 }} tickFormatter={formatAxisValue} />
                            <Tooltip formatter={formatTooltipValue} contentStyle={tooltipStyle} />
                            <Area
                                type="monotone"
                                dataKey="value"
                                stroke={COLORS[0]}
                                fill={COLORS[0]}
                                fillOpacity={0.3}
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                );

            case 'pie':
            case 'doughnut':
                const innerRadius = chartType === 'doughnut' ? '50%' : 0;
                return (
                    <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                            <Pie
                                data={chartData}
                                cx="50%"
                                cy="50%"
                                innerRadius={innerRadius}
                                outerRadius="80%"
                                paddingAngle={2}
                                dataKey="value"
                                label={renderPieLabel}
                                labelLine={false}
                            >
                                {chartData.map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={entry.fill} />
                                ))}
                            </Pie>
                            <Tooltip formatter={formatTooltipValue} contentStyle={tooltipStyle} />
                        </PieChart>
                    </ResponsiveContainer>
                );

            case 'radar':
                return (
                    <ResponsiveContainer width="100%" height="100%">
                        <RadarChart data={chartData}>
                            <PolarGrid stroke="#e5e7eb" />
                            <PolarAngleAxis dataKey="name" tick={{ fontSize: 10 }} />
                            <PolarRadiusAxis tick={{ fontSize: 8 }} />
                            <Radar
                                name="Valor"
                                dataKey="value"
                                stroke={COLORS[0]}
                                fill={COLORS[0]}
                                fillOpacity={0.5}
                            />
                            <Tooltip formatter={formatTooltipValue} contentStyle={tooltipStyle} />
                        </RadarChart>
                    </ResponsiveContainer>
                );

            default:
                return (
                    <div className="flex items-center justify-center h-full text-gray-400">
                        Tipo de gráfica no soportado: {chartType}
                    </div>
                );
        }
    };

    return (
        <div className="h-full w-full">
            {renderChart()}
        </div>
    );
};

// Helpers
const tooltipStyle = {
    backgroundColor: 'white',
    border: '1px solid #e5e7eb',
    borderRadius: '8px',
    padding: '8px 12px',
    fontSize: '12px',
    boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
};

function formatAxisValue(value) {
    if (value >= 1000000) return (value / 1000000).toFixed(0) + 'M';
    if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
    return value;
}

function formatTooltipValue(value) {
    if (typeof value === 'number') {
        return value.toLocaleString('es-CO');
    }
    return value;
}

function renderPieLabel({ name, percent }) {
    if (percent < 0.05) return null; // No mostrar labels muy pequeños
    return `${(percent * 100).toFixed(0)}%`;
}

export default memo(ChartWidget);
