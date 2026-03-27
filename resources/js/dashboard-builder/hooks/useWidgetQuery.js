/**
 * Hook para ejecutar queries de widgets
 *
 * Maneja:
 * - Ejecución de queries individuales
 * - Ejecución batch de múltiples widgets
 * - Cache de resultados
 * - Manejo de errores
 */

import { useState, useCallback, useRef } from 'react';
import axios from 'axios';

const API_BASE = '/api/dashboard-builder';

export function useWidgetQuery() {
    const [widgetData, setWidgetData] = useState({});
    const [widgetErrors, setWidgetErrors] = useState({});
    const [isExecuting, setIsExecuting] = useState({});

    // Cache para evitar queries duplicadas
    const queryCache = useRef(new Map());
    const pendingQueries = useRef(new Map());

    // Generar key de cache basada en configuración
    const getCacheKey = useCallback((widgetConfig) => {
        return JSON.stringify({
            entity: widgetConfig.entity,
            tipo: widgetConfig.tipo,
            metrica: widgetConfig.metrica,
            dimension: widgetConfig.dimension,
            filters: widgetConfig.filters,
            chartType: widgetConfig.chartType,
            columns: widgetConfig.columns,
            limit: widgetConfig.limit,
        });
    }, []);

    // Ejecutar query de un widget individual
    const executeWidget = useCallback(async (widgetConfig, forceRefresh = false) => {
        const widgetId = widgetConfig.id;
        const cacheKey = getCacheKey(widgetConfig);

        // Verificar cache (si no es refresh forzado)
        if (!forceRefresh && queryCache.current.has(cacheKey)) {
            const cached = queryCache.current.get(cacheKey);
            // Cache válido por 30 segundos
            if (Date.now() - cached.timestamp < 30000) {
                setWidgetData(prev => ({ ...prev, [widgetId]: cached.data }));
                return cached.data;
            }
        }

        // Evitar queries duplicadas en vuelo
        if (pendingQueries.current.has(cacheKey)) {
            return pendingQueries.current.get(cacheKey);
        }

        // Marcar como ejecutando
        setIsExecuting(prev => ({ ...prev, [widgetId]: true }));
        setWidgetErrors(prev => {
            const next = { ...prev };
            delete next[widgetId];
            return next;
        });

        // Crear promesa de query
        const queryPromise = axios.post(`${API_BASE}/execute-widget`, widgetConfig)
            .then(response => {
                if (response.data.success) {
                    const data = response.data.data;
                    const meta = response.data.meta;

                    // Guardar en cache
                    queryCache.current.set(cacheKey, {
                        data: { data, meta },
                        timestamp: Date.now(),
                    });

                    // Actualizar estado
                    setWidgetData(prev => ({ ...prev, [widgetId]: { data, meta } }));

                    return { data, meta };
                } else {
                    throw new Error(response.data.error || 'Error desconocido');
                }
            })
            .catch(error => {
                const errorMsg = error.response?.data?.error || error.message || 'Error ejecutando query';
                setWidgetErrors(prev => ({ ...prev, [widgetId]: errorMsg }));
                throw error;
            })
            .finally(() => {
                setIsExecuting(prev => ({ ...prev, [widgetId]: false }));
                pendingQueries.current.delete(cacheKey);
            });

        pendingQueries.current.set(cacheKey, queryPromise);

        return queryPromise;
    }, [getCacheKey]);

    // Ejecutar queries de múltiples widgets
    const executeAllWidgets = useCallback(async (widgets, forceRefresh = false) => {
        if (!widgets || widgets.length === 0) return;

        // Para refresh forzado, limpiar cache
        if (forceRefresh) {
            queryCache.current.clear();
        }

        // Filtrar widgets válidos
        const validWidgets = widgets.filter(w => w.entity && w.tipo);

        if (validWidgets.length === 0) return;

        // Marcar todos como ejecutando
        const executingState = {};
        validWidgets.forEach(w => { executingState[w.id] = true; });
        setIsExecuting(prev => ({ ...prev, ...executingState }));

        try {
            const response = await axios.post(`${API_BASE}/execute-dashboard`, {
                widgets: validWidgets,
            });

            if (response.data.success) {
                const results = response.data.widgets;
                const newData = {};
                const newErrors = {};

                Object.entries(results).forEach(([widgetId, result]) => {
                    if (result.success) {
                        newData[widgetId] = {
                            data: result.data,
                            meta: result.meta,
                        };
                    } else {
                        newErrors[widgetId] = result.error || 'Error desconocido';
                    }
                });

                setWidgetData(prev => ({ ...prev, ...newData }));
                setWidgetErrors(prev => ({ ...prev, ...newErrors }));

                return results;
            }
        } catch (error) {
            console.error('Error ejecutando widgets:', error);
            // Marcar todos con error
            const errors = {};
            validWidgets.forEach(w => {
                errors[w.id] = 'Error de conexión';
            });
            setWidgetErrors(prev => ({ ...prev, ...errors }));
        } finally {
            // Limpiar estados de ejecución
            const notExecuting = {};
            validWidgets.forEach(w => { notExecuting[w.id] = false; });
            setIsExecuting(prev => ({ ...prev, ...notExecuting }));
        }
    }, []);

    // Limpiar cache
    const clearCache = useCallback(() => {
        queryCache.current.clear();
    }, []);

    // Invalidar widget específico
    const invalidateWidget = useCallback((widgetId) => {
        setWidgetData(prev => {
            const next = { ...prev };
            delete next[widgetId];
            return next;
        });
    }, []);

    return {
        widgetData,
        widgetErrors,
        isExecuting,
        executeWidget,
        executeAllWidgets,
        clearCache,
        invalidateWidget,
    };
}

export default useWidgetQuery;
