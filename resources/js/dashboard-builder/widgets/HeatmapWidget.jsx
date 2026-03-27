/**
 * Heatmap Widget
 *
 * Muestra una matriz de intensidad de dos dimensiones.
 */

import React, { memo, useMemo } from 'react';

const HeatmapWidget = ({ widget, data, meta }) => {
    const { xLabels, yLabels, matrix, maxValue, minValue } = useMemo(() => {
        if (!data || !data.matrix) {
            return { xLabels: [], yLabels: [], matrix: [], maxValue: 0, minValue: 0 };
        }

        const flatValues = data.matrix.flat().filter(v => typeof v === 'number');
        const max = Math.max(...flatValues, 0);
        const min = Math.min(...flatValues, 0);

        return {
            xLabels: data.xLabels || [],
            yLabels: data.yLabels || [],
            matrix: data.matrix || [],
            maxValue: max,
            minValue: min,
        };
    }, [data]);

    // Calcular color de celda basado en valor
    const getCellColor = (value) => {
        if (value === null || value === undefined || value === 0) {
            return 'bg-gray-100';
        }

        const range = maxValue - minValue || 1;
        const normalized = (value - minValue) / range;

        // Escala de verde
        if (normalized < 0.2) return 'bg-green-100';
        if (normalized < 0.4) return 'bg-green-200';
        if (normalized < 0.6) return 'bg-green-300';
        if (normalized < 0.8) return 'bg-green-400';
        return 'bg-green-500';
    };

    const getTextColor = (value) => {
        if (value === null || value === undefined || value === 0) {
            return 'text-gray-400';
        }

        const range = maxValue - minValue || 1;
        const normalized = (value - minValue) / range;

        return normalized > 0.6 ? 'text-white' : 'text-gray-700';
    };

    if (xLabels.length === 0 || yLabels.length === 0) {
        return (
            <div className="flex items-center justify-center h-full text-gray-400 text-sm">
                Configura xDimension y yDimension para el heatmap
            </div>
        );
    }

    return (
        <div className="h-full overflow-auto">
            <table className="min-w-full text-xs">
                <thead>
                    <tr>
                        <th className="sticky top-0 left-0 z-20 bg-gray-100 px-2 py-1"></th>
                        {xLabels.map((label, i) => (
                            <th
                                key={i}
                                className="sticky top-0 z-10 bg-gray-100 px-2 py-1 font-medium text-gray-600 whitespace-nowrap"
                            >
                                {truncateLabel(label)}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {yLabels.map((yLabel, yIndex) => (
                        <tr key={yIndex}>
                            <th className="sticky left-0 z-10 bg-gray-100 px-2 py-1 font-medium text-gray-600 text-left whitespace-nowrap">
                                {truncateLabel(yLabel)}
                            </th>
                            {xLabels.map((_, xIndex) => {
                                const value = matrix[yIndex]?.[xIndex] ?? 0;
                                return (
                                    <td
                                        key={xIndex}
                                        className={`px-2 py-1 text-center ${getCellColor(value)} ${getTextColor(value)} transition-colors`}
                                        title={`${yLabel} × ${xLabels[xIndex]}: ${value}`}
                                    >
                                        {value || '—'}
                                    </td>
                                );
                            })}
                        </tr>
                    ))}
                </tbody>
            </table>

            {/* Leyenda */}
            <div className="flex items-center justify-center space-x-2 py-2 border-t border-gray-100 mt-2">
                <span className="text-[10px] text-gray-400">Menos</span>
                <div className="flex space-x-1">
                    <div className="w-4 h-4 bg-gray-100 rounded"></div>
                    <div className="w-4 h-4 bg-green-100 rounded"></div>
                    <div className="w-4 h-4 bg-green-200 rounded"></div>
                    <div className="w-4 h-4 bg-green-300 rounded"></div>
                    <div className="w-4 h-4 bg-green-400 rounded"></div>
                    <div className="w-4 h-4 bg-green-500 rounded"></div>
                </div>
                <span className="text-[10px] text-gray-400">Más</span>
            </div>
        </div>
    );
};

function truncateLabel(label) {
    if (!label) return '—';
    const str = String(label);
    return str.length > 12 ? str.substring(0, 10) + '...' : str;
}

export default memo(HeatmapWidget);
