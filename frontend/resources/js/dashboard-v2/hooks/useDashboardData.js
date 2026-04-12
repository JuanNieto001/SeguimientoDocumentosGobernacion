/**
 * Archivo: frontend/resources/js/dashboard-v2/hooks/useDashboardData.js
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// hooks/useDashboardData.js
import { useState, useCallback } from 'react';

export const useDashboardData = (urls) => {
    const [widgetData, setWidgetData] = useState({});
    const [cargando, setCargando] = useState(false);
    const [error, setError] = useState(null);

    // Función para hacer peticiones API con manejo de errores
    const apiCall = useCallback(async (url, options = {}) => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Error desconocido' }));
            throw new Error(errorData.message || `Error ${response.status}`);
        }

        return response.json();
    }, []);

    // Cargar datos para un widget específico
    const cargarDatosWidget = useCallback(async (widget, filtros = {}) => {
        try {
            setCargando(true);
            setError(null);

            const params = new URLSearchParams({
                widget_id: widget.id,
                metrica: widget.metrica,
                ...filtros
            });

            const data = await apiCall(`${urls.widget_data}?${params}`);

            setWidgetData(prev => ({
                ...prev,
                [widget.id]: {
                    ...data.data,
                    meta: data.meta,
                    ultima_actualizacion: new Date()
                }
            }));

            return data.data;
        } catch (err) {
            setError(err.message);
            return null;
        } finally {
            setCargando(false);
        }
    }, [urls.widget_data, apiCall]);

    // Actualizar configuración de un widget
    const actualizarWidget = useCallback(async (widgetId, configuracion) => {
        try {
            const data = await apiCall(urls.update_widget, {
                method: 'PUT',
                body: JSON.stringify({
                    widget_id: widgetId,
                    configuracion
                })
            });

            // Recargar datos del widget si la configuración cambió la métrica
            const widget = { id: widgetId, metrica: configuracion.metrica };
            if (configuracion.metrica) {
                await cargarDatosWidget(widget);
            }

            return data;
        } catch (err) {
            setError(err.message);
            throw err;
        }
    }, [urls.update_widget, apiCall, cargarDatosWidget]);

    // Cargar dashboard completo
    const cargarDashboard = useCallback(async (usuarioId, filtros = {}) => {
        try {
            setCargando(true);
            setError(null);

            const params = new URLSearchParams({
                usuario_id: usuarioId,
                ...filtros
            });

            const data = await apiCall(`${urls.load_dashboard}?${params}`);
            return data;
        } catch (err) {
            setError(err.message);
            return null;
        } finally {
            setCargando(false);
        }
    }, [urls.load_dashboard, apiCall]);

    // Guardar configuración completa del dashboard
    const guardarConfiguracion = useCallback(async (configuracion, usuarioId) => {
        try {
            setCargando(true);
            setError(null);

            const data = await apiCall(urls.save_dashboard, {
                method: 'POST',
                body: JSON.stringify({
                    usuario_id: usuarioId,
                    configuracion
                })
            });

            return data;
        } catch (err) {
            setError(err.message);
            throw err;
        } finally {
            setCargando(false);
        }
    }, [urls.save_dashboard, apiCall]);

    // Exportar dashboard
    const exportarDashboard = useCallback(async (formato, filtros = {}) => {
        try {
            setCargando(true);
            const params = new URLSearchParams({
                formato,
                ...filtros
            });

            const response = await fetch(`${urls.export_dashboard}?${params}`, {
                method: 'GET',
                credentials: 'same-origin'
            });

            if (!response.ok) throw new Error('Error al exportar');

            // Descargar el archivo
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `dashboard_${format}_${new Date().toISOString().slice(0,10)}.${formato}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

        } catch (err) {
            setError(err.message);
            throw err;
        } finally {
            setCargando(false);
        }
    }, [urls.export_dashboard]);

    return {
        widgetData,
        cargando,
        error,
        cargarDatosWidget,
        actualizarWidget,
        cargarDashboard,
        guardarConfiguracion,
        exportarDashboard
    };
};
