/**
 * Panel de Propiedades del Widget
 *
 * Permite configurar las propiedades del widget seleccionado:
 * - Título
 * - Tipo de widget
 * - Métrica
 * - Dimensión
 * - Filtros
 * - Tipo de gráfica
 * - Columnas (para tablas)
 */

import React, { useState, useCallback, memo, useEffect } from 'react';

const WidgetPropertiesPanel = ({ widget, catalog, onChange, onClose }) => {
    const [localConfig, setLocalConfig] = useState(widget);

    // Sincronizar con widget externo
    useEffect(() => {
        setLocalConfig(widget);
    }, [widget]);

    // Actualizar configuración local
    const handleChange = useCallback((key, value) => {
        setLocalConfig(prev => {
            const updated = { ...prev, [key]: value };
            // Notificar cambio al padre (debounced internamente)
            onChange?.(widget.id, { [key]: value });
            return updated;
        });
    }, [widget.id, onChange]);

    // Agregar filtro
    const handleAddFilter = useCallback(() => {
        const currentFilters = localConfig.filters || [];
        handleChange('filters', [
            ...currentFilters,
            { field: '', operator: '=', value: '' }
        ]);
    }, [localConfig.filters, handleChange]);

    // Actualizar filtro
    const handleUpdateFilter = useCallback((index, updates) => {
        const currentFilters = [...(localConfig.filters || [])];
        currentFilters[index] = { ...currentFilters[index], ...updates };
        handleChange('filters', currentFilters);
    }, [localConfig.filters, handleChange]);

    // Eliminar filtro
    const handleRemoveFilter = useCallback((index) => {
        const currentFilters = [...(localConfig.filters || [])];
        currentFilters.splice(index, 1);
        handleChange('filters', currentFilters);
    }, [localConfig.filters, handleChange]);

    // Obtener entidad actual
    const entityConfig = catalog?.entities?.[localConfig.entity];
    const fields = entityConfig?.fields || [];
    const aggregatableFields = fields.filter(f => f.aggregatable);

    // Tipos de widget
    const widgetTypes = catalog?.widgetTypes || [];
    const chartTypes = catalog?.chartTypes || [];
    const operators = catalog?.operators || [];
    const aggregations = catalog?.aggregations || [];

    return (
        <aside className="w-80 bg-white border-l border-gray-200 flex flex-col overflow-hidden">
            {/* Header */}
            <div className="flex items-center justify-between p-4 border-b border-gray-200">
                <h2 className="font-bold text-gray-800">⚙️ Propiedades</h2>
                <button
                    onClick={onClose}
                    className="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {/* Contenido */}
            <div className="flex-1 overflow-y-auto p-4 space-y-4">
                {/* Entidad (solo lectura) */}
                <div>
                    <label className="block text-xs font-medium text-gray-500 mb-1">
                        Entidad
                    </label>
                    <div className="px-3 py-2 bg-gray-100 rounded-lg text-sm text-gray-700">
                        {entityConfig?.label || localConfig.entity}
                    </div>
                </div>

                {/* Título */}
                <div>
                    <label className="block text-xs font-medium text-gray-500 mb-1">
                        Título del Widget
                    </label>
                    <input
                        type="text"
                        value={localConfig.titulo || ''}
                        onChange={(e) => handleChange('titulo', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Título..."
                    />
                </div>

                {/* Tipo de Widget */}
                <div>
                    <label className="block text-xs font-medium text-gray-500 mb-1">
                        Tipo de Widget
                    </label>
                    <select
                        value={localConfig.tipo || 'kpi'}
                        onChange={(e) => handleChange('tipo', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        {widgetTypes.map(type => (
                            <option key={type.value} value={type.value}>
                                {type.label}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Métrica / Agregación */}
                <div>
                    <label className="block text-xs font-medium text-gray-500 mb-1">
                        Métrica
                    </label>
                    <select
                        value={localConfig.metrica || 'count'}
                        onChange={(e) => handleChange('metrica', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        {aggregations.map(agg => (
                            <option key={agg.value} value={agg.value}>
                                {agg.label}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Dimensión (para gráficas y timelines) */}
                {['chart', 'timeline', 'heatmap'].includes(localConfig.tipo) && (
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">
                            Agrupar por (Dimensión)
                        </label>
                        <select
                            value={localConfig.dimension || ''}
                            onChange={(e) => handleChange('dimension', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option value="">Seleccionar campo...</option>
                            {aggregatableFields.map(field => (
                                <option key={field.key} value={field.key}>
                                    {field.label}
                                </option>
                            ))}
                        </select>
                    </div>
                )}

                {/* Tipo de Gráfica (para charts) */}
                {localConfig.tipo === 'chart' && (
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">
                            Tipo de Gráfica
                        </label>
                        <div className="grid grid-cols-4 gap-2">
                            {chartTypes.map(type => (
                                <button
                                    key={type.value}
                                    onClick={() => handleChange('chartType', type.value)}
                                    className={`
                                        p-2 rounded-lg border text-center transition-colors
                                        ${localConfig.chartType === type.value
                                            ? 'border-green-500 bg-green-50 text-green-700'
                                            : 'border-gray-200 hover:border-gray-300'
                                        }
                                    `}
                                    title={type.label}
                                >
                                    <span className="text-lg">
                                        {getChartIcon(type.value)}
                                    </span>
                                </button>
                            ))}
                        </div>
                    </div>
                )}

                {/* Columnas (para tablas) */}
                {localConfig.tipo === 'table' && (
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">
                            Columnas a mostrar
                        </label>
                        <div className="space-y-1 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-2">
                            {fields.map(field => (
                                <label
                                    key={field.key}
                                    className="flex items-center text-sm cursor-pointer hover:bg-gray-50 p-1 rounded"
                                >
                                    <input
                                        type="checkbox"
                                        checked={(localConfig.columns || []).includes(field.key)}
                                        onChange={(e) => {
                                            const current = localConfig.columns || [];
                                            const updated = e.target.checked
                                                ? [...current, field.key]
                                                : current.filter(c => c !== field.key);
                                            handleChange('columns', updated);
                                        }}
                                        className="mr-2 text-green-600 focus:ring-green-500"
                                    />
                                    {field.label}
                                </label>
                            ))}
                        </div>
                    </div>
                )}

                {/* Límite de registros */}
                {['table', 'chart', 'timeline'].includes(localConfig.tipo) && (
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">
                            Límite de registros
                        </label>
                        <input
                            type="number"
                            value={localConfig.limit || 10}
                            onChange={(e) => handleChange('limit', parseInt(e.target.value) || 10)}
                            min={1}
                            max={1000}
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>
                )}

                {/* Sección de Filtros */}
                <div className="pt-4 border-t border-gray-200">
                    <div className="flex items-center justify-between mb-2">
                        <label className="text-xs font-medium text-gray-500">
                            Filtros
                        </label>
                        <button
                            onClick={handleAddFilter}
                            className="text-xs text-green-600 hover:text-green-700 font-medium"
                        >
                            + Agregar filtro
                        </button>
                    </div>

                    <div className="space-y-2">
                        {(localConfig.filters || []).map((filter, index) => (
                            <FilterRow
                                key={index}
                                filter={filter}
                                fields={fields}
                                operators={operators}
                                onUpdate={(updates) => handleUpdateFilter(index, updates)}
                                onRemove={() => handleRemoveFilter(index)}
                            />
                        ))}

                        {(!localConfig.filters || localConfig.filters.length === 0) && (
                            <div className="text-xs text-gray-400 text-center py-2 bg-gray-50 rounded">
                                Sin filtros adicionales
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Footer */}
            <div className="p-4 border-t border-gray-200 bg-gray-50">
                <div className="text-xs text-gray-500 text-center">
                    Los cambios se aplican automáticamente
                </div>
            </div>
        </aside>
    );
};

// Componente de fila de filtro
const FilterRow = memo(({ filter, fields, operators, onUpdate, onRemove }) => {
    const selectedField = fields.find(f => f.key === filter.field);
    const applicableOperators = operators.filter(op =>
        !selectedField || op.types.includes(selectedField.type)
    );

    return (
        <div className="flex items-center space-x-1 bg-gray-50 p-2 rounded-lg">
            {/* Campo */}
            <select
                value={filter.field}
                onChange={(e) => onUpdate({ field: e.target.value })}
                className="flex-1 px-2 py-1 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-green-500"
            >
                <option value="">Campo...</option>
                {fields.map(f => (
                    <option key={f.key} value={f.key}>{f.label}</option>
                ))}
            </select>

            {/* Operador */}
            <select
                value={filter.operator}
                onChange={(e) => onUpdate({ operator: e.target.value })}
                className="w-20 px-2 py-1 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-green-500"
            >
                {applicableOperators.map(op => (
                    <option key={op.value} value={op.value}>{op.label}</option>
                ))}
            </select>

            {/* Valor */}
            {!['is_null', 'is_not_null', 'this_month', 'this_year'].includes(filter.operator) && (
                <input
                    type="text"
                    value={filter.value || ''}
                    onChange={(e) => onUpdate({ value: e.target.value })}
                    placeholder="Valor..."
                    className="flex-1 px-2 py-1 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-green-500"
                />
            )}

            {/* Eliminar */}
            <button
                onClick={onRemove}
                className="p-1 text-gray-400 hover:text-red-500"
            >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    );
});

FilterRow.displayName = 'FilterRow';

// Helper para iconos de gráficas
function getChartIcon(chartType) {
    const icons = {
        bar: '📊',
        line: '📈',
        pie: '🥧',
        doughnut: '🍩',
        area: '📉',
        polarArea: '🧭',
        radar: '🕸️',
    };
    return icons[chartType] || '📊';
}

export default memo(WidgetPropertiesPanel);
