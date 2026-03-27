/**
 * Dashboard Builder - Entry Point
 *
 * Este archivo monta el componente React en el DOM
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import DashboardBuilder from './DashboardBuilder';

// Montar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('dashboard-builder-root');

    if (container) {
        const root = createRoot(container);

        // Obtener configuración inicial del data attribute si existe
        const initialConfigAttr = container.dataset.initialConfig;
        const readOnly = container.dataset.readOnly === 'true';

        let initialConfig = null;
        if (initialConfigAttr) {
            try {
                initialConfig = JSON.parse(initialConfigAttr);
            } catch (e) {
                console.warn('Error parsing initial config:', e);
            }
        }

        root.render(
            <React.StrictMode>
                <DashboardBuilder
                    initialConfig={initialConfig}
                    readOnly={readOnly}
                />
            </React.StrictMode>
        );
    }
});
