/**
 * Archivo: frontend/resources/js/dashboard-builder.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// dashboard-builder.jsx - Entry Point
import { createRoot } from 'react-dom/client';
import React from 'react';
import DashboardBuilder from './modules/dashboard-builder/components/DashboardBuilder.jsx';

// Import react-grid-layout styles
import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';

// Función para inicializar la app React
function initializeDashboardBuilder() {
  const container = document.getElementById('dashboard-builder-root');
  
  if (!container) {
    console.error('Dashboard Builder: Container element not found');
    return;
  }

  try {
    // Obtener datos del DOM
    const userData = container.dataset.user ? JSON.parse(container.dataset.user) : null;
    const entitiesData = container.dataset.entities ? JSON.parse(container.dataset.entities) : [];
    const csrfToken = container.dataset.csrfToken || '';

    // Configurar CSRF token para requests
    if (csrfToken) {
      const metaTag = document.querySelector('meta[name="csrf-token"]') || 
                     document.createElement('meta');
      metaTag.setAttribute('name', 'csrf-token');
      metaTag.setAttribute('content', csrfToken);
      if (!document.querySelector('meta[name="csrf-token"]')) {
        document.head.appendChild(metaTag);
      }
    }

    // Validar datos requeridos
    if (!userData) {
      throw new Error('User data is required');
    }

    if (!entitiesData || entitiesData.length === 0) {
      console.warn('Dashboard Builder: No entities data provided');
    }

    // Crear root y renderizar
    const root = createRoot(container);
    
    root.render(
      <React.StrictMode>
        <DashboardBuilder 
          initialUser={userData}
          initialEntities={entitiesData}
        />
      </React.StrictMode>
    );

    console.log('Dashboard Builder initialized successfully', {
      user: userData.name,
      role: userData.role,
      entities: entitiesData.length
    });

  } catch (error) {
    console.error('Dashboard Builder initialization failed:', error);
    
    // Mostrar error en el contenedor
    container.innerHTML = `
      <div class="h-100 d-flex align-items-center justify-content-center">
        <div class="text-center text-danger">
          <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
          <h5>Error al cargar Dashboard Builder</h5>
          <p class="text-muted">${error.message}</p>
          <button class="btn btn-outline-primary" onclick="window.location.reload()">
            <i class="fas fa-redo me-2"></i>Recargar página
          </button>
        </div>
      </div>
    `;
  }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeDashboardBuilder);
} else {
  initializeDashboardBuilder();
}

// Para debugging en desarrollo
if (import.meta.env.DEV) {
  window.DashboardBuilder = {
    initialize: initializeDashboardBuilder,
    version: '1.0.0'
  };
}
