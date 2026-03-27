/**
 * Panel de Catálogo de Entidades
 *
 * Muestra las entidades y campos disponibles para arrastrar al canvas.
 * Cada entidad es expandible y muestra sus campos con iconos según tipo.
 */

import React, { useState, memo } from 'react';
import { useDrag } from 'react-dnd';

const FIELD_TYPE_ICONS = {
    integer: '🔢',
    decimal: '💰',
    string: '📝',
    datetime: '📅',
    enum: '🏷️',
    boolean: '✓',
    relation: '🔗',
};

const FIELD_TYPE_COLORS = {
    integer: 'bg-blue-100 text-blue-700',
    decimal: 'bg-green-100 text-green-700',
    string: 'bg-gray-100 text-gray-700',
    datetime: 'bg-purple-100 text-purple-700',
    enum: 'bg-yellow-100 text-yellow-700',
    boolean: 'bg-pink-100 text-pink-700',
    relation: 'bg-indigo-100 text-indigo-700',
};

// Componente de campo arrastrable
const DraggableField = memo(({ field, entity, entityConfig }) => {
    const [{ isDragging }, drag] = useDrag(() => ({
        type: 'FIELD',
        item: {
            entity,
            field: field.key,
            entityConfig,
            fieldConfig: field,
        },
        collect: (monitor) => ({
            isDragging: monitor.isDragging(),
        }),
    }), [field, entity, entityConfig]);

    const typeIcon = FIELD_TYPE_ICONS[field.type] || '📋';
    const typeColor = FIELD_TYPE_COLORS[field.type] || 'bg-gray-100 text-gray-700';

    return (
        <div
            ref={drag}
            className={`
                flex items-center px-3 py-2 rounded-lg cursor-grab
                transition-all duration-200 border border-transparent
                hover:border-green-300 hover:bg-green-50
                ${isDragging ? 'opacity-50 bg-green-100' : 'bg-white'}
            `}
        >
            <span className={`w-6 h-6 rounded flex items-center justify-center text-xs mr-2 ${typeColor}`}>
                {typeIcon}
            </span>
            <div className="flex-1 min-w-0">
                <div className="text-sm font-medium text-gray-700 truncate">
                    {field.label}
                </div>
                <div className="text-xs text-gray-400">
                    {field.key}
                </div>
            </div>
            {field.aggregatable && (
                <span className="ml-2 px-1.5 py-0.5 bg-blue-100 text-blue-600 text-[10px] rounded">
                    AGG
                </span>
            )}
        </div>
    );
});

DraggableField.displayName = 'DraggableField';

// Componente de entidad expandible
const EntitySection = memo(({ entityKey, entityConfig }) => {
    const [isExpanded, setIsExpanded] = useState(false);

    const fields = entityConfig.fields || [];

    return (
        <div className="border-b border-gray-100 last:border-b-0">
            <button
                onClick={() => setIsExpanded(!isExpanded)}
                className="w-full flex items-center px-4 py-3 hover:bg-gray-50 transition-colors"
            >
                <span className="text-lg mr-3">
                    {getEntityIcon(entityConfig.icon)}
                </span>
                <span className="flex-1 text-left font-medium text-gray-700">
                    {entityConfig.label}
                </span>
                <span className="text-xs text-gray-400 mr-2">
                    {fields.length} campos
                </span>
                <svg
                    className={`w-4 h-4 text-gray-400 transition-transform ${isExpanded ? 'rotate-180' : ''}`}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {isExpanded && (
                <div className="px-3 pb-3 space-y-1">
                    {fields.map((field) => (
                        <DraggableField
                            key={field.key}
                            field={field}
                            entity={entityKey}
                            entityConfig={entityConfig}
                        />
                    ))}
                </div>
            )}
        </div>
    );
});

EntitySection.displayName = 'EntitySection';

// Componente principal del panel
const EntityCatalogPanel = ({ catalog, onFieldDrag }) => {
    const [searchTerm, setSearchTerm] = useState('');

    if (!catalog || !catalog.entities) {
        return (
            <aside className="w-72 bg-white border-r border-gray-200 flex items-center justify-center">
                <div className="animate-pulse text-gray-400">
                    Cargando catálogo...
                </div>
            </aside>
        );
    }

    const entities = catalog.entities;

    // Filtrar por búsqueda
    const filteredEntities = Object.entries(entities).filter(([key, config]) => {
        if (!searchTerm) return true;
        const term = searchTerm.toLowerCase();
        return (
            config.label.toLowerCase().includes(term) ||
            key.toLowerCase().includes(term) ||
            config.fields.some(f =>
                f.label.toLowerCase().includes(term) ||
                f.key.toLowerCase().includes(term)
            )
        );
    });

    return (
        <aside className="w-72 bg-white border-r border-gray-200 flex flex-col">
            {/* Header */}
            <div className="p-4 border-b border-gray-200">
                <h2 className="font-bold text-gray-800 mb-3">📊 Entidades</h2>

                {/* Buscador */}
                <div className="relative">
                    <input
                        type="text"
                        placeholder="Buscar campos..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="w-full px-3 py-2 pl-9 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                    <svg
                        className="absolute left-3 top-2.5 w-4 h-4 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {/* Instrucciones */}
            <div className="px-4 py-2 bg-green-50 text-xs text-green-700 border-b border-green-100">
                <strong>Arrastra</strong> un campo al canvas para crear un widget
            </div>

            {/* Lista de entidades */}
            <div className="flex-1 overflow-y-auto">
                {filteredEntities.map(([key, config]) => (
                    <EntitySection
                        key={key}
                        entityKey={key}
                        entityConfig={config}
                    />
                ))}

                {filteredEntities.length === 0 && (
                    <div className="p-4 text-center text-gray-400 text-sm">
                        No se encontraron campos
                    </div>
                )}
            </div>

            {/* Footer con tipos */}
            <div className="p-3 border-t border-gray-200 bg-gray-50">
                <div className="text-xs text-gray-500 mb-2">Tipos de campo:</div>
                <div className="flex flex-wrap gap-1">
                    {Object.entries(FIELD_TYPE_ICONS).map(([type, icon]) => (
                        <span
                            key={type}
                            className={`px-2 py-1 rounded text-xs ${FIELD_TYPE_COLORS[type]}`}
                        >
                            {icon} {type}
                        </span>
                    ))}
                </div>
            </div>
        </aside>
    );
};

// Helper para iconos de entidad
function getEntityIcon(iconName) {
    const icons = {
        folder: '📁',
        'file-contract': '📄',
        'user-tie': '👤',
        'project-diagram': '🔀',
        users: '👥',
        bell: '🔔',
        'calendar-alt': '📅',
        history: '📜',
        building: '🏛️',
        sitemap: '🗂️',
    };
    return icons[iconName] || '📋';
}

export default memo(EntityCatalogPanel);
