/**
 * Archivo: frontend/resources/js/dashboard-v2/hooks/useRealtimeUpdates.js
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// hooks/useRealtimeUpdates.js
import { useState, useEffect, useCallback, useRef } from 'react';

export const useRealtimeUpdates = (habilitado = false, usuario = null) => {
    const [conectado, setConectado] = useState(false);
    const [estadoConexion, setEstadoConexion] = useState('desconectado'); // desconectado, conectando, conectado, error
    const [subscripciones, setSubscripciones] = useState(new Map());
    const echoRef = useRef(null);
    const canalesRef = useRef(new Map());

    // Inicializar conexión Echo/Pusher
    useEffect(() => {
        if (!habilitado || !window.Echo) return;

        try {
            setEstadoConexion('conectando');
            echoRef.current = window.Echo;

            // Event listeners para estado de conexión
            if (window.Echo.connector?.pusher) {
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    setConectado(true);
                    setEstadoConexion('conectado');
                });

                window.Echo.connector.pusher.connection.bind('disconnected', () => {
                    setConectado(false);
                    setEstadoConexion('desconectado');
                });

                window.Echo.connector.pusher.connection.bind('error', (err) => {
                    setConectado(false);
                    setEstadoConexion('error');
                    console.error('Error de conexión WebSocket:', err);
                });
            }

            // Configurar canales básicos
            configurarCanalesBasicos();

        } catch (error) {
            console.error('Error al inicializar Echo:', error);
            setEstadoConexion('error');
        }

        return () => {
            // Cleanup: desconectar todos los canales
            canalesRef.current.forEach((canal, nombre) => {
                try {
                    echo.leave(nombre);
                } catch (e) {
                    console.warn('Error al desconectar canal:', nombre, e);
                }
            });
            canalesRef.current.clear();
        };
    }, [habilitado]);

    // Configurar canales básicos del sistema
    const configurarCanalesBasicos = useCallback(() => {
        if (!echoRef.current || !usuario) return;

        // Canal global de alertas críticas
        const canalAlertas = echoRef.current.channel('alertas-globales');
        canalAlertas.listen('AlertaCriticaCreada', (event) => {
            notificarSubscriptores('alertas-globales', event.data);
        });
        canalesRef.current.set('alertas-globales', canalAlertas);

        // Canal privado del usuario
        const canalUsuario = echoRef.current.private(`user.${usuario.id}`);
        canalUsuario
            .listen('ProcesoAsignado', (event) => {
                notificarSubscriptores('procesos-usuario', event.data);
            })
            .listen('DocumentoAprobado', (event) => {
                notificarSubscriptores('documentos-usuario', event.data);
            })
            .listen('DashboardActualizado', (event) => {
                notificarSubscriptores('dashboard-usuario', event.data);
            });
        canalesRef.current.set(`user.${usuario.id}`, canalUsuario);

        // Canal de presencia por secretaría (si aplica)
        if (usuario.secretaria_id) {
            const canalSecretaria = echoRef.current.join(`secretaria.${usuario.secretaria_id}`);
            canalSecretaria
                .here((users) => {
                    notificarSubscriptores('usuarios-secretaria', { tipo: 'presencia', usuarios: users });
                })
                .joining((user) => {
                    notificarSubscriptores('usuarios-secretaria', { tipo: 'joining', usuario: user });
                })
                .leaving((user) => {
                    notificarSubscriptores('usuarios-secretaria', { tipo: 'leaving', usuario: user });
                })
                .listen('ProcesoActualizado', (event) => {
                    notificarSubscriptores('procesos-secretaria', event.data);
                })
                .listen('MetricaActualizada', (event) => {
                    notificarSubscriptores('metricas-secretaria', event.data);
                });
            canalesRef.current.set(`secretaria.${usuario.secretaria_id}`, canalSecretaria);
        }
    }, [usuario]);

    // Notificar a todos los suscriptores de un canal
    const notificarSubscriptores = useCallback((canal, data) => {
        const suscriptoresCanal = subscripciones.get(canal);
        if (suscriptoresCanal) {
            suscriptoresCanal.forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error('Error en callback de suscripción:', error);
                }
            });
        }
    }, [subscripciones]);

    // Suscribir un widget a actualizaciones de tiempo real
    const suscribirWidget = useCallback((widgetId, canales, callback) => {
        if (!habilitado || !conectado) return () => {};

        setSubscripciones(prev => {
            const nuevasSubscripciones = new Map(prev);

            canales.forEach(canal => {
                if (!nuevasSubscripciones.has(canal)) {
                    nuevasSubscripciones.set(canal, new Map());
                }
                nuevasSubscripciones.get(canal).set(widgetId, callback);
            });

            return nuevasSubscripciones;
        });

        // Retornar función de cleanup
        return () => {
            setSubscripciones(prev => {
                const nuevasSubscripciones = new Map(prev);

                canales.forEach(canal => {
                    const canalSubs = nuevasSubscripciones.get(canal);
                    if (canalSubs) {
                        canalSubs.delete(widgetId);
                        if (canalSubs.size === 0) {
                            nuevasSubscripciones.delete(canal);
                        }
                    }
                });

                return nuevasSubscripciones;
            });
        };
    }, [habilitado, conectado]);

    // Suscribir a eventos específicos para métricas
    const suscribirMetrica = useCallback((metrica, callback, scope = 'usuario') => {
        if (!habilitado || !conectado) return () => {};

        // Mapear métricas a canales apropiados
        const canalMap = {
            'procesos_en_curso': [`procesos-${scope}`],
            'alertas_altas': ['alertas-globales'],
            'contratos_vigentes': [`contratos-${scope}`],
            'presupuesto_ejecutado': [`presupuesto-${scope}`],
            'documentos_pendientes': [`documentos-${scope}`]
        };

        const canales = canalMap[metrica] || [`metrica-${metrica}-${scope}`];

        return suscribirWidget(`metrica-${metrica}`, canales, callback);
    }, [suscribirWidget, habilitado, conectado]);

    // Forzar reconexión
    const reconectar = useCallback(() => {
        if (!echoRef.current) return;

        setEstadoConexion('conectando');

        try {
            if (window.Echo.connector?.pusher) {
                window.Echo.connector.pusher.disconnect();
                setTimeout(() => {
                    window.Echo.connector.pusher.connect();
                }, 1000);
            }
        } catch (error) {
            console.error('Error al reconectar:', error);
            setEstadoConexion('error');
        }
    }, []);

    // Enviar evento personalizado (si el backend lo soporta)
    const enviarEvento = useCallback((evento, datos) => {
        if (!echoRef.current || !conectado) {
            console.warn('No hay conexión WebSocket para enviar evento');
            return false;
        }

        try {
            // Esto dependería del backend, ejemplo conceptual
            echoRef.current.emit(evento, datos);
            return true;
        } catch (error) {
            console.error('Error al enviar evento:', error);
            return false;
        }
    }, [conectado]);

    // Obtener estadísticas de conexión
    const estadisticas = useCallback(() => {
        return {
            conectado,
            estadoConexion,
            canalesActivos: canalesRef.current.size,
            subscripcionesActivas: Array.from(subscripciones.values())
                .reduce((total, canal) => total + canal.size, 0)
        };
    }, [conectado, estadoConexion, subscripciones]);

    return {
        conectado,
        estadoConexion,
        suscribirWidget,
        suscribirMetrica,
        reconectar,
        enviarEvento,
        estadisticas
    };
};
