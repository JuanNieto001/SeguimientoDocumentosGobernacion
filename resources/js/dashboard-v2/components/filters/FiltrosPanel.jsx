import React, { useState, useEffect, useCallback, useMemo } from 'react';
import DateRangePicker from './DateRangePicker.jsx';
import SecretariaSelector from './SecretariaSelector.jsx';
import MultiSelectChips from './MultiSelectChips.jsx';

const FILTRO_PRESETS = {
    temporal: {
        hoy: { label: 'Hoy', value: 'today' },
        ayer: { label: 'Ayer', value: 'yesterday' },
        ultima_semana: { label: 'Última semana', value: 'last_week' },
        ultimo_mes: { label: 'Último mes', value: 'last_month' },
        ultimo_trimestre: { label: 'Último trimestre', value: 'last_quarter' },
        ultimo_año: { label: 'Último año', value: 'last_year' },
        personalizado: { label: 'Personalizado', value: 'custom' }
    },
    estados: [
        { value: 'borrador', label: 'Borrador', color: '#6b7280' },
        { value: 'en_curso', label: 'En curso', color: '#2563eb' },
        { value: 'pausado', label: 'Pausado', color: '#f59e0b' },
        { value: 'finalizado', label: 'Finalizado', color: '#16a34a' },
        { value: 'cancelado', label: 'Cancelado', color: '#dc2626' },
        { value: 'observaciones', label: 'Con observaciones', color: '#ea580c' }
    ],
    prioridades: [
        { value: 'baja', label: 'Baja', color: '#22c55e' },
        { value: 'media', label: 'Media', color: '#f59e0b' },
        { value: 'alta', label: 'Alta', color: '#ef4444' },
        { value: 'critica', label: 'Crítica', color: '#991b1b' }
    ]
};

const FiltrosPanel = ({
    filtros,
    onChange,
    usuario,
    responsive,
    collapsed = false,
    contexto = 'dashboard' // dashboard, widget, reporte
}) => {
    const { isMobile, isTablet, isDesktop } = responsive;
    const [panelAbierto, setPanelAbierto] = useState(!isMobile && !collapsed);
    const [seccionAbierta, setSeccionAbierta] = useState({
        temporal: true,
        organizational: !isMobile,
        financial: false,
        contextual: false
    });

    // Configuración dinámica de filtros basada en permisos del usuario
    const filtrosDisponibles = useMemo(() => {
        const permisos = usuario.permisos || [];
        const config = {
            temporal: {
                disponible: true,
                opciones: FILTRO_PRESETS.temporal
            },
            organizational: {
                disponible: true,
                secretaria: permisos.includes('ver_todas_secretarias') || usuario.secretaria_id,
                unidad: permisos.includes('ver_todas_unidades') || usuario.unidad_id,
                roles: permisos.includes('administrar_usuarios'),
                usuarios: permisos.includes('ver_equipo') || permisos.includes('administrar_usuarios')
            },
            financial: {
                disponible: permisos.includes('ver_datos_financieros'),
                rangos: permisos.includes('ver_todos_montos'),
                fuentes: permisos.includes('ver_fuentes_presupuestales')
            },
            contextual: {
                disponible: true,
                estados: true,
                prioridades: permisos.includes('gestionar_prioridades')
            }
        };

        return config;
    }, [usuario]);

    // Manejar cambio específico de filtro
    const handleFiltroChange = useCallback((categoria, tipo, valor) => {
        onChange(categoria, { ...filtros[categoria], [tipo]: valor });
    }, [filtros, onChange]);

    // Limpiar filtros
    const limpiarFiltros = useCallback(() => {
        const filtrosLimpios = {
            periodo: { inicio: null, fin: null, preset: 'ultimo_mes' },
            secretaria: [],
            unidad: [],
            estado: [],
            usuario: [],
            prioridad: 'todas',
            rango_valor: { min: null, max: null },
            fuente_presupuestal: []
        };

        Object.keys(filtrosLimpios).forEach(key => {
            onChange(key, filtrosLimpios[key]);
        });
    }, [onChange]);

    // Aplicar filtros rápidos
    const aplicarFiltroRapido = useCallback((preset) => {
        const ahora = new Date();
        let inicio, fin;

        switch (preset) {
            case 'today':
                inicio = fin = new Date(ahora.setHours(0, 0, 0, 0));
                break;
            case 'yesterday':
                fin = new Date(ahora.setDate(ahora.getDate() - 1));
                inicio = new Date(fin);
                break;
            case 'last_week':
                fin = new Date();
                inicio = new Date(ahora.setDate(ahora.getDate() - 7));
                break;
            case 'last_month':
                fin = new Date();
                inicio = new Date(ahora.setMonth(ahora.getMonth() - 1));
                break;
            case 'last_quarter':
                fin = new Date();
                inicio = new Date(ahora.setMonth(ahora.getMonth() - 3));
                break;
            case 'last_year':
                fin = new Date();
                inicio = new Date(ahora.setFullYear(ahora.getFullYear() - 1));
                break;
        }

        if (inicio && fin) {
            onChange('periodo', { inicio, fin, preset });
        }
    }, [onChange]);

    // Toggle sección expandible
    const toggleSeccion = useCallback((seccion) => {
        setSeccionAbierta(prev => ({
            ...prev,
            [seccion]: !prev[seccion]
        }));
    }, []);

    // Contador de filtros activos
    const filtrosActivos = useMemo(() => {
        let count = 0;

        if (filtros.periodo?.preset !== 'ultimo_mes') count++;
        if (filtros.secretaria?.length > 0) count++;
        if (filtros.unidad?.length > 0) count++;
        if (filtros.estado?.length > 0) count++;
        if (filtros.usuario?.length > 0) count++;
        if (filtros.prioridad !== 'todas') count++;
        if (filtros.rango_valor?.min || filtros.rango_valor?.max) count++;
        if (filtros.fuente_presupuestal?.length > 0) count++;

        return count;
    }, [filtros]);

    // Renderizar filtros temporales
    const renderFiltrosTemporal = () => (
        <div className="filtro-section">
            <div
                className="filtro-header"
                onClick={() => toggleSeccion('temporal')}
            >
                <h4>⏰ Filtros Temporales</h4>
                <span className={`toggle-icon ${seccionAbierta.temporal ? 'open' : ''}`}>
                    ▼
                </span>
            </div>

            {seccionAbierta.temporal && (
                <div className="filtro-content">
                    {/* Filtros rápidos */}
                    <div className="filtros-rapidos">
                        <label className="filtro-label">Período rápido:</label>
                        <div className="button-group">
                            {Object.entries(FILTRO_PRESETS.temporal).map(([key, preset]) => (
                                <button
                                    key={key}
                                    onClick={() => aplicarFiltroRapido(preset.value)}
                                    className={`btn-preset ${filtros.periodo?.preset === preset.value ? 'active' : ''}`}
                                >
                                    {preset.label}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Selector de rango personalizado */}
                    <DateRangePicker
                        value={{
                            inicio: filtros.periodo?.inicio,
                            fin: filtros.periodo?.fin
                        }}
                        onChange={(rango) => onChange('periodo', {
                            ...rango,
                            preset: 'personalizado'
                        })}
                        maxRange="2_años"
                        placeholder="Seleccionar rango personalizado"
                    />

                    {/* Toggle comparación */}
                    <div className="filtro-comparacion">
                        <label className="checkbox-container">
                            <input
                                type="checkbox"
                                checked={filtros.comparacion?.habilitada || false}
                                onChange={(e) => onChange('comparacion', {
                                    habilitada: e.target.checked,
                                    tipo: 'periodo_anterior'
                                })}
                            />
                            <span className="checkmark"></span>
                            Comparar con período anterior
                        </label>
                    </div>
                </div>
            )}
        </div>
    );

    // Renderizar filtros organizacionales
    const renderFiltrosOrganizacional = () => (
        <div className="filtro-section">
            <div
                className="filtro-header"
                onClick={() => toggleSeccion('organizational')}
            >
                <h4>🏢 Filtros Organizacionales</h4>
                <span className={`toggle-icon ${seccionAbierta.organizational ? 'open' : ''}`}>
                    ▼
                </span>
            </div>

            {seccionAbierta.organizational && (
                <div className="filtro-content">
                    {/* Selector de secretarías */}
                    {filtrosDisponibles.organizational.secretaria && (
                        <SecretariaSelector
                            value={filtros.secretaria || []}
                            onChange={(value) => onChange('secretaria', value)}
                            multiple={true}
                            usuario={usuario}
                            placeholder="Seleccionar secretarías"
                        />
                    )}

                    {/* Selector de unidades */}
                    {filtrosDisponibles.organizational.unidad && (
                        <div className="filtro-group">
                            <label className="filtro-label">Unidades:</label>
                            <MultiSelectChips
                                value={filtros.unidad || []}
                                onChange={(value) => onChange('unidad', value)}
                                options={[]} // Se cargaría dinámicamente basado en secretarías seleccionadas
                                placeholder="Seleccionar unidades"
                                searchable={true}
                                groupBy="secretaria"
                            />
                        </div>
                    )}

                    {/* Selector de usuarios */}
                    {filtrosDisponibles.organizational.usuarios && (
                        <div className="filtro-group">
                            <label className="filtro-label">Usuarios:</label>
                            <MultiSelectChips
                                value={filtros.usuario || []}
                                onChange={(value) => onChange('usuario', value)}
                                options={[]} // Se cargaría dinámicamente
                                placeholder="Buscar usuarios"
                                searchable={true}
                                showAvatars={true}
                            />
                        </div>
                    )}
                </div>
            )}
        </div>
    );

    // Renderizar filtros contextuales
    const renderFiltrosContextual = () => (
        <div className="filtro-section">
            <div
                className="filtro-header"
                onClick={() => toggleSeccion('contextual')}
            >
                <h4>🎯 Filtros de Contexto</h4>
                <span className={`toggle-icon ${seccionAbierta.contextual ? 'open' : ''}`}>
                    ▼
                </span>
            </div>

            {seccionAbierta.contextual && (
                <div className="filtro-content">
                    {/* Estados de proceso */}
                    <div className="filtro-group">
                        <label className="filtro-label">Estados:</label>
                        <div className="estado-buttons">
                            {FILTRO_PRESETS.estados.map(estado => (
                                <button
                                    key={estado.value}
                                    onClick={() => {
                                        const estadosActuales = filtros.estado || [];
                                        const nuevoEstado = estadosActuales.includes(estado.value)
                                            ? estadosActuales.filter(e => e !== estado.value)
                                            : [...estadosActuales, estado.value];
                                        onChange('estado', nuevoEstado);
                                    }}
                                    className={`btn-estado ${(filtros.estado || []).includes(estado.value) ? 'active' : ''}`}
                                    style={{
                                        '--estado-color': estado.color
                                    }}
                                >
                                    <span
                                        className="estado-indicator"
                                        style={{ backgroundColor: estado.color }}
                                    ></span>
                                    {estado.label}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Prioridades */}
                    {filtrosDisponibles.contextual.prioridades && (
                        <div className="filtro-group">
                            <label className="filtro-label">Prioridad:</label>
                            <div className="radio-group">
                                <label className="radio-item">
                                    <input
                                        type="radio"
                                        name="prioridad"
                                        value="todas"
                                        checked={filtros.prioridad === 'todas'}
                                        onChange={(e) => onChange('prioridad', e.target.value)}
                                    />
                                    <span>Todas</span>
                                </label>
                                {FILTRO_PRESETS.prioridades.map(prioridad => (
                                    <label key={prioridad.value} className="radio-item">
                                        <input
                                            type="radio"
                                            name="prioridad"
                                            value={prioridad.value}
                                            checked={filtros.prioridad === prioridad.value}
                                            onChange={(e) => onChange('prioridad', e.target.value)}
                                        />
                                        <span style={{ color: prioridad.color }}>
                                            {prioridad.label}
                                        </span>
                                    </label>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );

    // Panel móvil como drawer
    if (isMobile) {
        return (
            <>
                {/* Botón trigger móvil */}
                <button
                    className="filtros-trigger-mobile"
                    onClick={() => setPanelAbierto(true)}
                >
                    🔍 Filtros {filtrosActivos > 0 && (
                        <span className="filtros-badge">{filtrosActivos}</span>
                    )}
                </button>

                {/* Overlay */}
                {panelAbierto && (
                    <div
                        className="filtros-overlay"
                        onClick={() => setPanelAbierto(false)}
                    />
                )}

                {/* Drawer móvil */}
                <div className={`filtros-drawer ${panelAbierto ? 'open' : ''}`}>
                    <div className="drawer-header">
                        <h3>Filtros de Dashboard</h3>
                        <button
                            className="btn-close"
                            onClick={() => setPanelAbierto(false)}
                        >
                            ✕
                        </button>
                    </div>

                    <div className="drawer-content">
                        {renderFiltrosTemporal()}
                        {renderFiltrosOrganizacional()}
                        {renderFiltrosContextual()}
                    </div>

                    <div className="drawer-footer">
                        <button
                            className="btn-clear"
                            onClick={limpiarFiltros}
                        >
                            Limpiar Filtros
                        </button>
                        <button
                            className="btn-apply"
                            onClick={() => setPanelAbierto(false)}
                        >
                            Aplicar
                        </button>
                    </div>
                </div>
            </>
        );
    }

    // Panel desktop/tablet
    return (
        <div className={`filtros-panel desktop ${panelAbierto ? 'open' : 'collapsed'}`}>
            <div className="panel-header">
                <h3>
                    Filtros {filtrosActivos > 0 && (
                        <span className="filtros-badge">{filtrosActivos}</span>
                    )}
                </h3>
                <button
                    className="btn-toggle"
                    onClick={() => setPanelAbierto(!panelAbierto)}
                >
                    {panelAbierto ? '◄' : '►'}
                </button>
            </div>

            {panelAbierto && (
                <div className="panel-content">
                    {renderFiltrosTemporal()}
                    {renderFiltrosOrganizacional()}
                    {renderFiltrosContextual()}

                    <div className="panel-actions">
                        <button
                            className="btn-clear secondary"
                            onClick={limpiarFiltros}
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            )}

            {/* Estilos CSS específicos */}
            <style jsx>{`
                .filtros-panel {
                    background: white;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    overflow: hidden;
                }

                .filtros-panel.desktop {
                    width: ${panelAbierto ? '350px' : '60px'};
                    transition: width 0.3s ease;
                }

                .panel-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 12px 16px;
                    border-bottom: 1px solid #e5e7eb;
                    background: #f9fafb;
                }

                .panel-header h3 {
                    font-size: 14px;
                    font-weight: 600;
                    color: #374151;
                    margin: 0;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .filtros-badge {
                    background: #ef4444;
                    color: white;
                    font-size: 11px;
                    font-weight: 700;
                    padding: 2px 6px;
                    border-radius: 10px;
                    min-width: 16px;
                    text-align: center;
                }

                .panel-content {
                    padding: 16px;
                    max-height: 70vh;
                    overflow-y: auto;
                }

                .filtro-section {
                    margin-bottom: 20px;
                    border: 1px solid #e5e7eb;
                    border-radius: 6px;
                    overflow: hidden;
                }

                .filtro-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 10px 12px;
                    background: #f8fafc;
                    cursor: pointer;
                    transition: background 0.2s;
                }

                .filtro-header:hover {
                    background: #f1f5f9;
                }

                .filtro-header h4 {
                    font-size: 13px;
                    font-weight: 600;
                    color: #374151;
                    margin: 0;
                }

                .toggle-icon {
                    font-size: 10px;
                    color: #6b7280;
                    transition: transform 0.2s;
                }

                .toggle-icon.open {
                    transform: rotate(180deg);
                }

                .filtro-content {
                    padding: 16px 12px;
                }

                .filtro-group {
                    margin-bottom: 16px;
                }

                .filtro-label {
                    display: block;
                    font-size: 12px;
                    font-weight: 500;
                    color: #374151;
                    margin-bottom: 6px;
                }

                .button-group {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 4px;
                }

                .btn-preset {
                    padding: 4px 8px;
                    border: 1px solid #d1d5db;
                    background: white;
                    border-radius: 4px;
                    font-size: 11px;
                    cursor: pointer;
                    transition: all 0.2s;
                }

                .btn-preset:hover {
                    border-color: #3b82f6;
                }

                .btn-preset.active {
                    background: #3b82f6;
                    color: white;
                    border-color: #3b82f6;
                }

                .estado-buttons {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                }

                .btn-estado {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    padding: 6px 10px;
                    border: 1px solid #d1d5db;
                    background: white;
                    border-radius: 20px;
                    font-size: 11px;
                    cursor: pointer;
                    transition: all 0.2s;
                }

                .btn-estado:hover {
                    border-color: var(--estado-color);
                }

                .btn-estado.active {
                    background: var(--estado-color);
                    color: white;
                    border-color: var(--estado-color);
                }

                .estado-indicator {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                }

                .radio-group {
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                }

                .radio-item {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    font-size: 12px;
                    cursor: pointer;
                }

                /* Estilos móviles */
                .filtros-trigger-mobile {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #3b82f6;
                    color: white;
                    border: none;
                    border-radius: 25px;
                    padding: 10px 16px;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
                    z-index: 100;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .filtros-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 200;
                }

                .filtros-drawer {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    width: 100vw;
                    max-height: 90vh;
                    background: white;
                    border-radius: 16px 16px 0 0;
                    z-index: 300;
                    transform: translateY(100%);
                    transition: transform 0.3s ease;
                }

                .filtros-drawer.open {
                    transform: translateY(0);
                }

                .drawer-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 16px 20px;
                    border-bottom: 1px solid #e5e7eb;
                }

                .drawer-content {
                    padding: 20px;
                    max-height: 60vh;
                    overflow-y: auto;
                }

                .drawer-footer {
                    display: flex;
                    gap: 12px;
                    padding: 16px 20px;
                    border-top: 1px solid #e5e7eb;
                }

                .btn-clear {
                    flex: 1;
                    padding: 12px;
                    border: 1px solid #d1d5db;
                    background: white;
                    color: #374151;
                    border-radius: 8px;
                    font-weight: 500;
                    cursor: pointer;
                }

                .btn-apply {
                    flex: 2;
                    padding: 12px;
                    border: none;
                    background: #3b82f6;
                    color: white;
                    border-radius: 8px;
                    font-weight: 500;
                    cursor: pointer;
                }
            `}</style>
        </div>
    );
};

export default FiltrosPanel;