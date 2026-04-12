/**
 * Archivo: frontend/resources/js/components/ResponsiveComponents.jsx
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
import React, { useState } from 'react';

/**
 * Componente Layout Responsivo
 * Estructura principal para todas las vistas
 * Mobile-first, responsive a todos los tamaños
 */
export function ResponsiveContainer({ children, title }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-gray-50">
            {/* HEADER - Responsivo */}
            <header className="bg-white shadow-sm sticky top-0 z-20">
                <div className="container mx-auto px-4 md:px-6">
                    <div className="flex items-center justify-between h-16 md:h-20">
                        {/* Mobile Menu Button */}
                        <button
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="md:hidden p-2 -m-2 rounded-lg hover:bg-gray-100"
                            aria-label="Abrir menú"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        {/* Title */}
                        <h1 className="text-lg md:text-2xl font-bold text-gray-900 truncate">
                            {title || 'Dashboard'}
                        </h1>

                        {/* Actions */}
                        <div className="flex items-center gap-2 md:gap-4">
                            {children.headerActions}
                        </div>
                    </div>
                </div>
            </header>

            {/* MAIN LAYOUT */}
            <div className="flex flex-col md:flex-row min-h-screen pt-16 md:pt-0">
                {/* SIDEBAR - Mobile Overlay + Desktop Drawer */}
                {sidebarOpen && (
                    <div
                        className="fixed inset-0 bg-black/50 z-10 md:hidden"
                        onClick={() => setSidebarOpen(false)}
                    />
                )}

                <aside
                    className={`
                        fixed md:static inset-y-0 left-0 z-20
                        w-64 bg-white shadow-lg md:shadow-none
                        transform md:transform-none transition-transform
                        duration-300 ease-in-out
                        ${sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'}
                        overflow-y-auto
                        mt-16 md:mt-0
                    `}
                >
                    {children.sidebar}
                </aside>

                {/* MAIN CONTENT */}
                <main className="flex-1 overflow-y-auto">
                    <div className="container mx-auto px-4 md:px-6 py-6 md:py-8">
                        {children.main}
                    </div>
                </main>
            </div>

            {/* FOOTER - Responsivo */}
            {children.footer && (
                <footer className="bg-white border-t border-gray-200 mt-8 md:mt-12">
                    <div className="container mx-auto px-4 md:px-6 py-6">
                        {children.footer}
                    </div>
                </footer>
            )}
        </div>
    );
}

/**
 * Grid Responsivo para Widgets
 * Auto-ajusta columnas según viewport
 */
export function ResponsiveGrid({ children, cols = 3 }) {
    return (
        <div className={`
            grid gap-4 md:gap-6
            grid-cols-1
            sm:grid-cols-2
            md:grid-cols-${cols === 3 ? '3' : cols === 2 ? '2' : '1'}
            lg:grid-cols-${cols}
            xl:grid-cols-${cols}
        `}>
            {children}
        </div>
    );
}

/**
 * Card Responsivo
 * Padding y espacio ajustado automáticamente
 */
export function ResponsiveCard({ children, title, footer }) {
    return (
        <div className="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-100">
            {/* Header */}
            {title && (
                <div className="px-4 md:px-6 py-3 md:py-4 border-b border-gray-100">
                    <h3 className="text-base md:text-lg font-semibold text-gray-900">
                        {title}
                    </h3>
                </div>
            )}

            {/* Content */}
            <div className="px-4 md:px-6 py-4 md:py-6">
                {children}
            </div>

            {/* Footer */}
            {footer && (
                <div className="px-4 md:px-6 py-3 border-t border-gray-100 bg-gray-50">
                    {footer}
                </div>
            )}
        </div>
    );
}

/**
 * Button Responsivo
 * Touch-friendly, accesible
 */
export function ResponsiveButton({ children, onClick, variant = 'primary', size = 'md', fullWidth = false, ...props }) {
    const baseClasses = `
        font-medium rounded-lg
        transition-colors duration-200
        min-h-[44px] md:min-h-[40px]
        px-4 md:px-6
        flex items-center justify-center gap-2
        text-sm md:text-base
        ${fullWidth ? 'w-full' : ''}
        ${size === 'sm' ? 'py-2 text-xs md:text-sm' : 'py-3 md:py-2'}
    `;

    const variants = {
        primary: 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800',
        secondary: 'bg-gray-200 text-gray-900 hover:bg-gray-300 active:bg-gray-400',
        danger: 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800',
        success: 'bg-green-600 text-white hover:bg-green-700 active:bg-green-800',
    };

    return (
        <button
            onClick={onClick}
            className={`${baseClasses} ${variants[variant]}`}
            {...props}
        >
            {children}
        </button>
    );
}

/**
 * Input Responsivo
 * Previene zoom en iOS, touch-friendly
 */
export function ResponsiveInput({ label, type = 'text', ...props }) {
    return (
        <div className="w-full">
            {label && (
                <label className="block text-sm md:text-base font-medium text-gray-700 mb-2">
                    {label}
                </label>
            )}
            <input
                type={type}
                className={`
                    w-full
                    px-4 py-3 md:py-2
                    text-base md:text-sm
                    border border-gray-300 rounded-lg
                    focus:outline-none focus:ring-2 focus:ring-blue-500
                    disabled:bg-gray-100 disabled:cursor-not-allowed
                    min-h-[44px] md:min-h-[40px]
                `}
                {...props}
            />
        </div>
    );
}

export default ResponsiveContainer;

