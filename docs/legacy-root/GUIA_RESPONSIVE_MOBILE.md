# 📱 GUÍA RESPONSIVE - OPTIMIZACIÓN PARA MOBILE

**Sistema de Seguimiento de Documentos Contractuales**
**Problema Identificado**: Interface no responsive en móvil
**Solución**: Tailwind CSS mejorado + Media queries

---

## 🔧 PASO 1: ACTUALIZAR tailwind.config.js

```javascript
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            // RESPONSIVE BREAKPOINTS
            screens: {
                'xs': '320px',   // Mobile pequeño
                'sm': '640px',   // Mobile
                'md': '768px',   // Tablet
                'lg': '1024px',  // Desktop
                'xl': '1280px',  // Desktop grande
                '2xl': '1536px', // Desktop muy grande
            },
            // ESPACIADO MEJORADO
            spacing: {
                'safe-l': '20px',   // Margen lateral seguro mobile
                'safe-r': '20px',   // Margen derecho seguro
            },
        },
    },

    plugins: [forms],
};
```

---

## 🎨 PASO 2: ACTUALIZAR resources/css/app.css

Añade al final:

```css
/* ═══════════════════════════════════════════════════════════ */
/* RESPONSIVE MOBILE FIRST */
/* ═══════════════════════════════════════════════════════════ */

/* Base mobile - 320px+ */
@media (max-width: 639px) {
    html, body {
        font-size: 14px;
        line-height: 1.4;
    }

    .container {
        padding: 12px;
        width: 100%;
        max-width: 100%;
    }

    .sidebar {
        position: fixed;
        left: -100%;
        transition: left 0.3s ease;
        z-index: 40;
        width: 80%;
        height: 100vh;
    }

    .sidebar.active {
        left: 0;
    }

    .main-content {
        width: 100%;
        padding: 8px;
    }

    .grid {
        grid-template-columns: 1fr !important;
    }

    button {
        min-height: 44px !important; /* Thumb-friendly */
        font-size: 14px;
    }

    input, select, textarea {
        font-size: 16px !important; /* Prevent zoom on iOS */
        min-height: 44px;
    }

    .card {
        padding: 12px !important;
        margin: 8px 0 !important;
    }

    .dashboard-widget {
        min-height: 200px;
        margin-bottom: 12px;
    }
}

/* Tablet - 640px+ */
@media (min-width: 640px) and (max-width: 1023px) {
    .container {
        padding: 16px;
    }

    .sidebar {
        width: 200px;
    }

    .grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Desktop - 1024px+ */
@media (min-width: 1024px) {
    .container {
        padding: 20px;
    }

    .sidebar {
        width: 250px;
    }

    .grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}

/* ═══════════════════════════════════════════════════════════ */
/* COMPONENTES RESPONSIVOS */
/* ═══════════════════════════════════════════════════════════ */

/* Header Responsive */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    gap: 8px;
    flex-wrap: wrap;
}

@media (max-width: 639px) {
    .header {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* Navigation Responsive */
.nav-menu {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

@media (max-width: 639px) {
    .nav-menu {
        flex-direction: column;
        gap: 4px;
    }

    .nav-menu > li {
        width: 100%;
    }
}

/* Tabla Responsive */
@media (max-width: 639px) {
    table {
        font-size: 12px !important;
    }

    th, td {
        padding: 8px 4px !important;
    }

    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}

/* Cards Responsive */
.card-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: 1fr;
}

@media (min-width: 640px) {
    .card-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .card-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Modal Responsive */
@media (max-width: 639px) {
    .modal {
        width: 95vw !important;
        max-width: 95vw !important;
    }
}

/* Formularios Responsive */
.form-group {
    margin-bottom: 16px;
}

@media (max-width: 639px) {
    .form-group {
        margin-bottom: 12px;
    }

    .form-inline {
        flex-direction: column;
    }
}

/* Touch Targets */
a, button, input[type="checkbox"], input[type="radio"] {
    min-width: 44px;
    min-height: 44px;
}

/* Safe Area (notches) */
@supports (padding: max(0px)) {
    body {
        padding-left: max(12px, env(safe-area-inset-left));
        padding-right: max(12px, env(safe-area-inset-right));
        padding-top: max(12px, env(safe-area-inset-top));
        padding-bottom: max(12px, env(safe-area-inset-bottom));
    }
}

/* Prevent zoom on iOS */
input, textarea, select {
    font-size: 16px;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
    -webkit-font-smoothing: antialiased;
}

/* Prevent text selection on long press */
body {
    -webkit-user-select: none;
    -webkit-touch-callout: none;
}

/* Text is selectable */
p, h1, h2, h3, h4, h5, h6 {
    -webkit-user-select: text;
}
```

---

## ⚛️ PASO 3: COMPONENTE RESPONSIVE BASE

Crea: `resources/js/components/ResponsiveLayout.jsx`

```jsx
import React, { useState } from 'react';

export function ResponsiveLayout({ children }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="flex h-screen overflow-hidden bg-gray-100">
            {/* Sidebar - Mobile */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 z-20 bg-gray-600 bg-opacity-75 md:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* Sidebar - Desktop */}
            <aside
                className={`
                    fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg
                    transform transition-transform duration-300
                    ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
                    md:static md:translate-x-0 md:inset-auto
                    overflow-y-auto
                `}
            >
                {/* Sidebar content */}
                {children.sidebar}
            </aside>

            {/* Main Content */}
            <div className="flex-1 flex flex-col overflow-hidden">
                {/* Header */}
                <header className="bg-white shadow-sm p-4 md:p-6">
                    <div className="flex items-center justify-between">
                        {/* Mobile menu button */}
                        <button
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="md:hidden p-2 rounded-lg bg-gray-100 hover:bg-gray-200"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        {children.header}
                    </div>
                </header>

                {/* Main content area */}
                <main className="flex-1 overflow-y-auto p-4 md:p-6">
                    {children.main}
                </main>
            </div>
        </div>
    );
}
```

---

## 🎯 PASO 4: DASHBOARD MOTOR RESPONSIVO

Reemplaza en `dashboard-motor.jsx`:

```jsx
// En la función render principal, cambia:

return (
    <div className="min-h-screen bg-gray-50">
        {/* Header responsivo */}
        <div className="bg-white shadow sticky top-0 z-10">
            <div className="container mx-auto px-4 md:px-6 py-4">
                <div className="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                    <h1 className="text-2xl md:text-3xl font-bold">Dashboard Motor</h1>

                    {/* Controls responsivos */}
                    <div className="w-full md:w-auto flex flex-col md:flex-row gap-2">
                        <select
                            className="w-full md:w-auto px-4 py-2 border rounded-lg text-sm"
                            value={scope}
                            onChange={(e) => setScope(e.target.value)}
                        >
                            <option>Global</option>
                            <option>Secretaría</option>
                            <option>Unidad</option>
                        </select>

                        <input
                            type="text"
                            placeholder="Buscar..."
                            className="w-full md:w-auto px-4 py-2 border rounded-lg text-sm"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                        />
                    </div>
                </div>
            </div>
        </div>

        {/* Content responsivo */}
        <div className="container mx-auto px-4 md:px-6 py-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                {/* Widgets */}
                {widgets.map((widget) => (
                    <div
                        key={widget.id}
                        className="
                            bg-white rounded-lg shadow-md p-4
                            hover:shadow-lg transition-shadow
                            min-h-[200px]
                        "
                    >
                        {renderWidget(widget)}
                    </div>
                ))}
            </div>
        </div>
    </div>
);
```

---

## 🧪 PASO 5: TESTING RESPONSIVO

Ejecuta:

```bash
# Tests en diferentes viewports
npm run test:mobile       # 375x667
npm run test:desktop      # 1920x1080
npm run test:tablet       # 768x1024

# O modo interactivo
npm run cypress:open
# Selecciona viewport en DevTools
```

---

## 📱 BREAKPOINTS A USAR

```
xs: 320px   (iPhone SE, pequeños)
sm: 640px   (iPhone, tablets pequeña)
md: 768px   (iPad)
lg: 1024px  (iPad Pro, laptops)
xl: 1280px  (Desktop)
2xl: 1536px (Desktop grande)
```

---

## ✅ CHECKLIST RESPONSIVE

```
[ ] Header responsivo en móvil
[ ] Sidebar oculto en móvil, show en desktop
[ ] Botones 44x44px (touch-friendly)
[ ] Input font-size 16px (previene zoom iOS)
[ ] Formularios full-width en móvil
[ ] Tablas scrollables en móvil
[ ] Imágenes max-width 100%
[ ] Padding/margin ajustado por viewport
[ ] No overflow horizontal
[ ] Notches/safe areas consideradas
```

---

## 🚀 COMANDO PARA APLICAR TODO:

```bash
# 1. Actualiza tailwind.config.js
# 2. Actualiza resources/css/app.css
# 3. Ejecuta npm run build
# 4. Prueba en móvil: npm run test:mobile

npm run dev
```

---

**Esto hará que tu dashboard sea 100% responsive en todos los dispositivos** ✅

