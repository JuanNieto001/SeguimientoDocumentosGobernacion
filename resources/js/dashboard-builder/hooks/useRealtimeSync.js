/**
 * Hook para sincronización en tiempo real
 *
 * Usa Laravel Echo + Pusher para recibir actualizaciones
 */

import { useState, useEffect, useCallback, useRef } from 'react';

export function useRealtimeSync(widgets, onUpdate) {
    const [isConnected, setIsConnected] = useState(false);
    const [lastUpdate, setLastUpdate] = useState(null);
    const channelRef = useRef(null);

    useEffect(() => {
        // Verificar si Echo está disponible
        if (typeof window === 'undefined' || !window.Echo) {
            return;
        }

        try {
            // Suscribirse al canal de dashboards
            channelRef.current = window.Echo.private('dashboard-updates')
                .listen('.widget.updated', (event) => {
                    setLastUpdate(new Date());

                    if (onUpdate && event.widgetId) {
                        onUpdate({
                            [event.widgetId]: event.data,
                        });
                    }
                })
                .listen('.dashboard.refreshed', () => {
                    setLastUpdate(new Date());
                    // Trigger refresh de todos los widgets
                    if (onUpdate) {
                        onUpdate({ _refresh: true });
                    }
                });

            setIsConnected(true);
        } catch (error) {
            console.warn('WebSocket no disponible:', error);
            setIsConnected(false);
        }

        return () => {
            if (channelRef.current) {
                window.Echo?.leave('dashboard-updates');
            }
        };
    }, [onUpdate]);

    // Broadcast cambio a otros usuarios
    const broadcastUpdate = useCallback((widgetId, data) => {
        if (channelRef.current && isConnected) {
            channelRef.current.whisper('widget.changed', {
                widgetId,
                data,
            });
        }
    }, [isConnected]);

    return {
        isConnected,
        lastUpdate,
        broadcastUpdate,
    };
}

export default useRealtimeSync;
