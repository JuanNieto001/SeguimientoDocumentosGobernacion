/**
 * KPI Widget
 *
 * Muestra un valor numérico destacado con formato y tendencia opcional.
 */

import React, { memo, useMemo } from 'react';

const KpiWidget = ({ widget, data, meta }) => {
    const { value, formatted } = useMemo(() => {
        if (!data) return { value: 0, formatted: '0' };

        let val = data.value;
        let fmt = data.formatted || formatNumber(val);

        // Aplicar formato según métrica
        if (widget.metrica === 'sum' && isLargeNumber(val)) {
            fmt = formatCurrency(val);
        }

        return { value: val, formatted: fmt };
    }, [data, widget.metrica]);

    // Determinar color según el contexto
    const colorClass = useMemo(() => {
        // Por defecto verde para valores positivos
        if (value > 0) return 'text-green-600';
        if (value === 0) return 'text-gray-600';
        return 'text-red-600';
    }, [value]);

    // Icono según tipo de métrica
    const icon = useMemo(() => {
        switch (widget.metrica) {
            case 'count': return '📊';
            case 'sum': return '💰';
            case 'avg': return '📈';
            case 'max': return '⬆️';
            case 'min': return '⬇️';
            default: return '📊';
        }
    }, [widget.metrica]);

    return (
        <div className="flex flex-col items-center justify-center h-full text-center p-2">
            {/* Icono */}
            <div className="text-2xl mb-2 opacity-70">
                {icon}
            </div>

            {/* Valor principal */}
            <div className={`text-3xl font-bold ${colorClass} tracking-tight leading-none`}>
                {formatted}
            </div>

            {/* Etiqueta de métrica */}
            <div className="text-xs text-gray-400 mt-2 uppercase tracking-wide">
                {getMetricaLabel(widget.metrica)}
            </div>

            {/* Indicador de scope si está disponible */}
            {meta?.scope && (
                <div className="mt-2 text-[10px] text-gray-300">
                    Scope: {meta.scope}
                </div>
            )}
        </div>
    );
};

// Helpers
function formatNumber(value) {
    if (value === null || value === undefined) return '0';
    if (typeof value !== 'number') return String(value);

    if (value >= 1000000000) {
        return (value / 1000000000).toFixed(1) + 'B';
    }
    if (value >= 1000000) {
        return (value / 1000000).toFixed(1) + 'M';
    }
    if (value >= 1000) {
        return (value / 1000).toFixed(1) + 'K';
    }
    return value.toLocaleString('es-CO');
}

function formatCurrency(value) {
    if (value >= 1000000000) {
        return '$' + (value / 1000000000).toFixed(1) + 'B';
    }
    if (value >= 1000000) {
        return '$' + (value / 1000000).toFixed(1) + 'M';
    }
    if (value >= 1000) {
        return '$' + (value / 1000).toFixed(0) + 'K';
    }
    return '$' + value.toLocaleString('es-CO');
}

function isLargeNumber(value) {
    return typeof value === 'number' && value >= 1000;
}

function getMetricaLabel(metrica) {
    const labels = {
        count: 'Total',
        sum: 'Suma',
        avg: 'Promedio',
        max: 'Máximo',
        min: 'Mínimo',
    };
    return labels[metrica] || metrica;
}

export default memo(KpiWidget);
