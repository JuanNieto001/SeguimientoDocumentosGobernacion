/**
 * Archivo: frontend/resources/js/motor-flujos.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
import React from 'react';
import { createRoot } from 'react-dom/client';
import WorkflowApp from './WorkflowApp';

const container = document.getElementById('motor-flujos-app');
if (container) {
    createRoot(container).render(<WorkflowApp />);
}

