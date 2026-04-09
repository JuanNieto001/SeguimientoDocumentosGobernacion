import React from 'react';
import { createRoot } from 'react-dom/client';
import WorkflowApp from './WorkflowApp';

const container = document.getElementById('motor-flujos-app');
if (container) {
    createRoot(container).render(<WorkflowApp />);
}
