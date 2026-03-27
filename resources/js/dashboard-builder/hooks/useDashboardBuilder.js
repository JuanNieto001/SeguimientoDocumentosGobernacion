/**
 * Hook principal del Dashboard Builder
 *
 * Maneja:
 * - Estado de widgets
 * - Layout del grid
 * - Catálogo de entidades
 * - Scope del usuario
 * - Persistencia
 */

import { useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';

const API_BASE = '/api/dashboard-builder';

export function useDashboardBuilder(initialConfig = null) {
    // Estado
    const [widgets, setWidgets] = useState([]);
    const [layout, setLayout] = useState([]);
    const [selectedWidgetId, setSelectedWidgetId] = useState(null);
    const [catalog, setCatalog] = useState(null);
    const [userScope, setUserScope] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    // Ref para evitar re-renders innecesarios
    const isInitialized = useRef(false);

    // Cargar catálogo y scope al montar
    useEffect(() => {
        if (isInitialized.current) return;
        isInitialized.current = true;

        const initialize = async () => {
            setIsLoading(true);
            setError(null);

            try {
                // Cargar en paralelo
                const [catalogRes, scopeRes, savedRes] = await Promise.all([
                    axios.get(`${API_BASE}/catalog`),
                    axios.get(`${API_BASE}/user-scope`),
                    initialConfig ? Promise.resolve({ data: { success: true, data: initialConfig } }) : axios.get(`${API_BASE}/load`),
                ]);

                if (catalogRes.data.success) {
                    setCatalog(catalogRes.data.data);
                }

                if (scopeRes.data.success) {
                    setUserScope(scopeRes.data.data);
                }

                // Cargar dashboard guardado si existe
                if (savedRes.data.success && savedRes.data.data) {
                    const saved = savedRes.data.data;
                    setWidgets(saved.widgets || []);
                    setLayout(generateLayoutFromWidgets(saved.widgets || [], saved.layout));
                }
            } catch (err) {
                console.error('Error inicializando Dashboard Builder:', err);
                setError('Error cargando el Dashboard Builder. Por favor recargue la página.');
            } finally {
                setIsLoading(false);
            }
        };

        initialize();
    }, [initialConfig]);

    // Añadir widget
    const addWidget = useCallback((widgetConfig, position = null) => {
        const newWidget = {
            ...widgetConfig,
            id: widgetConfig.id || `widget_${Date.now()}`,
        };

        setWidgets(prev => [...prev, newWidget]);

        // Agregar al layout
        const layoutItem = {
            i: newWidget.id,
            x: position?.x ?? (widgets.length * 3) % 12,
            y: position?.y ?? Infinity, // Se coloca al final
            w: newWidget.config?.ancho || 3,
            h: newWidget.config?.alto || 2,
            minW: 2,
            minH: 1,
        };

        setLayout(prev => [...prev, layoutItem]);

        // Seleccionar el nuevo widget
        setSelectedWidgetId(newWidget.id);

        return newWidget;
    }, [widgets.length]);

    // Actualizar widget
    const updateWidget = useCallback((widgetId, updates) => {
        setWidgets(prev => prev.map(widget =>
            widget.id === widgetId
                ? { ...widget, ...updates }
                : widget
        ));
    }, []);

    // Eliminar widget
    const removeWidget = useCallback((widgetId) => {
        setWidgets(prev => prev.filter(w => w.id !== widgetId));
        setLayout(prev => prev.filter(l => l.i !== widgetId));

        if (selectedWidgetId === widgetId) {
            setSelectedWidgetId(null);
        }
    }, [selectedWidgetId]);

    // Seleccionar widget
    const selectWidget = useCallback((widgetId) => {
        setSelectedWidgetId(widgetId);
    }, []);

    // Actualizar layout
    const updateLayout = useCallback((newLayout) => {
        setLayout(newLayout);

        // Sincronizar tamaños con los widgets
        setWidgets(prev => prev.map(widget => {
            const layoutItem = newLayout.find(l => l.i === widget.id);
            if (layoutItem) {
                return {
                    ...widget,
                    config: {
                        ...widget.config,
                        ancho: layoutItem.w,
                        alto: layoutItem.h,
                    },
                };
            }
            return widget;
        }));
    }, []);

    // Guardar dashboard
    const saveDashboard = useCallback(async (name, description = '') => {
        try {
            const response = await axios.post(`${API_BASE}/save`, {
                name,
                description,
                widgets,
                layout,
            });

            if (response.data.success) {
                return response.data.data;
            }

            throw new Error(response.data.error || 'Error guardando');
        } catch (err) {
            console.error('Error guardando dashboard:', err);
            throw err;
        }
    }, [widgets, layout]);

    // Cargar dashboard
    const loadDashboard = useCallback(async () => {
        try {
            const response = await axios.get(`${API_BASE}/load`);

            if (response.data.success && response.data.data) {
                const data = response.data.data;
                setWidgets(data.widgets || []);
                setLayout(generateLayoutFromWidgets(data.widgets || [], data.layout));
                return data;
            }

            return null;
        } catch (err) {
            console.error('Error cargando dashboard:', err);
            throw err;
        }
    }, []);

    return {
        widgets,
        layout,
        selectedWidgetId,
        catalog,
        userScope,
        isLoading,
        error,
        addWidget,
        updateWidget,
        removeWidget,
        selectWidget,
        updateLayout,
        saveDashboard,
        loadDashboard,
    };
}

// Generar layout desde widgets
function generateLayoutFromWidgets(widgets, savedLayout = []) {
    const layoutMap = new Map(savedLayout.map(l => [l.i, l]));

    return widgets.map((widget, index) => {
        const saved = layoutMap.get(widget.id);
        if (saved) return saved;

        return {
            i: widget.id,
            x: (index * 3) % 12,
            y: Math.floor(index / 4) * 2,
            w: widget.config?.ancho || 3,
            h: widget.config?.alto || 2,
            minW: 2,
            minH: 1,
        };
    });
}

export default useDashboardBuilder;
