/**
 * Indicador de Scope del Usuario
 *
 * Muestra el nivel de acceso del usuario actual
 */

import React, { memo } from 'react';

const SCOPE_COLORS = {
    global: 'bg-purple-100 text-purple-700 border-purple-200',
    secretaria: 'bg-blue-100 text-blue-700 border-blue-200',
    unidad: 'bg-green-100 text-green-700 border-green-200',
    usuario: 'bg-orange-100 text-orange-700 border-orange-200',
};

const SCOPE_ICONS = {
    global: '🌐',
    secretaria: '🏛️',
    unidad: '🏢',
    usuario: '👤',
};

const SCOPE_LABELS = {
    global: 'Acceso Global',
    secretaria: 'Mi Secretaría',
    unidad: 'Mi Unidad',
    usuario: 'Mis Datos',
};

const ScopeIndicator = ({ scope }) => {
    if (!scope) return null;

    const scopeLevel = scope.scope_level || 'usuario';
    const colorClass = SCOPE_COLORS[scopeLevel] || SCOPE_COLORS.usuario;
    const icon = SCOPE_ICONS[scopeLevel] || '👤';
    const label = SCOPE_LABELS[scopeLevel] || 'Restringido';

    return (
        <div
            className={`inline-flex items-center px-3 py-1 rounded-full border text-xs font-medium ${colorClass}`}
            title={scope.description || ''}
        >
            <span className="mr-1.5">{icon}</span>
            <span>{label}</span>
            {scope.secretaria_nombre && scopeLevel !== 'global' && (
                <span className="ml-1.5 opacity-75">
                    • {scope.secretaria_nombre}
                </span>
            )}
            {scope.unidad_nombre && scopeLevel === 'unidad' && (
                <span className="ml-1 opacity-75">
                    / {scope.unidad_nombre}
                </span>
            )}
        </div>
    );
};

export default memo(ScopeIndicator);
