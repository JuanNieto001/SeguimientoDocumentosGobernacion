import React, { useState, useEffect, useCallback, useMemo } from 'react';

const TIPO_COLUMNAS = {
    text: 'texto',
    number: 'numero',
    date: 'fecha',
    datetime: 'fecha_hora',
    currency: 'pesos',
    percentage: 'porcentaje',
    boolean: 'booleano',
    status: 'estado',
    actions: 'acciones'
};

const ACCIONES_PREDEFINIDAS = {
    ver: {
        icono: '👁️',
        titulo: 'Ver Detalle',
        clase: 'action-view'
    },
    editar: {
        icono: '✏️',
        titulo: 'Editar',
        clase: 'action-edit'
    },
    eliminar: {
        icono: '🗑️',
        titulo: 'Eliminar',
        clase: 'action-delete'
    },
    aprobar: {
        icono: '✅',
        titulo: 'Aprobar',
        clase: 'action-approve'
    },
    rechazar: {
        icono: '❌',
        titulo: 'Rechazar',
        clase: 'action-reject'
    },
    comentar: {
        icono: '💬',
        titulo: 'Comentar',
        clase: 'action-comment'
    },
    descargar: {
        icono: '⬇️',
        titulo: 'Descargar',
        clase: 'action-download'
    }
};

const TableWidget = ({
    widget,
    data = null,
    filtros = {},
    modoEdicion = false,
    responsive = {},
    onUpdate,
    onAction
}) => {
    const { isMobile, isTablet } = responsive;

    // Estados locales
    const [datosTabla, setDatosTabla] = useState([]);
    const [orderBy, setOrderBy] = useState(null);
    const [orderDirection, setOrderDirection] = useState('asc');
    const [paginaActual, setPaginaActual] = useState(1);
    const [filasPorPagina, setFilasPorPagina] = useState(10);
    const [filasSeleccionadas, setFilasSeleccionadas] = useState(new Set());
    const [filtroLocal, setFiltroLocal] = useState('');
    const [columnasVisibles, setColumnasVisibles] = useState(new Set());
    const [cargando, setCargando] = useState(false);

    // Configuración del widget
    const configuracion = widget.configuracion || {};
    const columnas = configuracion.columnas || [];
    const accionesDisponibles = configuracion.acciones || [];
    const seleccionMultiple = configuracion.seleccion_multiple || false;
    const exportable = configuracion.exportable || false;
    const filterable = configuracion.filterable || false;
    const paginable = configuracion.paginable !== false;

    // Inicializar columnas visibles
    useEffect(() => {
        const columnasIniciales = new Set(
            columnas
                .filter(col => col.visible !== false)
                .map(col => col.key)
        );
        setColumnasVisibles(columnasIniciales);
    }, [columnas]);

    // Actualizar datos cuando cambien los props
    useEffect(() => {
        if (data) {
            setDatosTabla(Array.isArray(data) ? data : data.datos || []);
        }
    }, [data]);

    // Formatear valor según tipo de columna
    const formatearValor = useCallback((valor, tipo, opciones = {}) => {
        if (valor === null || valor === undefined) return '-';

        switch (tipo) {
            case 'numero':
                return new Intl.NumberFormat('es-CO').format(valor);

            case 'pesos':
                return new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    minimumFractionDigits: 0
                }).format(valor);

            case 'porcentaje':
                return `${(valor * 100).toFixed(1)}%`;

            case 'fecha':
                return new Date(valor).toLocaleDateString('es-CO');

            case 'fecha_hora':
                return new Date(valor).toLocaleString('es-CO');

            case 'booleano':
                return valor ? '✅ Sí' : '❌ No';

            case 'estado':
                const estados = {
                    borrador: { texto: 'Borrador', clase: 'status-draft' },
                    en_curso: { texto: 'En Curso', clase: 'status-active' },
                    pausado: { texto: 'Pausado', clase: 'status-paused' },
                    finalizado: { texto: 'Finalizado', clase: 'status-completed' },
                    cancelado: { texto: 'Cancelado', clase: 'status-cancelled' }
                };
                const estado = estados[valor] || { texto: valor, clase: 'status-default' };
                return (
                    <span className={`status-badge ${estado.clase}`}>
                        {estado.texto}
                    </span>
                );

            default:
                return String(valor);
        }
    }, []);

    // Datos filtrados y ordenados
    const datosOrganizados = useMemo(() => {
        let datos = [...datosTabla];

        // Filtro local
        if (filtroLocal) {
            const termino = filtroLocal.toLowerCase();
            datos = datos.filter(fila =>
                Object.values(fila).some(valor =>
                    String(valor).toLowerCase().includes(termino)
                )
            );
        }

        // Ordenamiento
        if (orderBy) {
            datos.sort((a, b) => {
                const aVal = a[orderBy];
                const bVal = b[orderBy];

                if (aVal === null || aVal === undefined) return 1;
                if (bVal === null || bVal === undefined) return -1;

                let comparison = 0;
                if (typeof aVal === 'string') {
                    comparison = aVal.localeCompare(bVal, 'es-CO');
                } else if (typeof aVal === 'number') {
                    comparison = aVal - bVal;
                } else if (aVal instanceof Date) {
                    comparison = aVal.getTime() - bVal.getTime();
                } else {
                    comparison = String(aVal).localeCompare(String(bVal), 'es-CO');
                }

                return orderDirection === 'asc' ? comparison : -comparison;
            });
        }

        return datos;
    }, [datosTabla, filtroLocal, orderBy, orderDirection]);

    // Datos paginados
    const datosPaginados = useMemo(() => {
        if (!paginable) return datosOrganizados;

        const inicio = (paginaActual - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;
        return datosOrganizados.slice(inicio, fin);
    }, [datosOrganizados, paginaActual, filasPorPagina, paginable]);

    // Información de paginación
    const infoPaginacion = useMemo(() => ({
        totalFilas: datosOrganizados.length,
        totalPaginas: Math.ceil(datosOrganizados.length / filasPorPagina),
        filasInicio: Math.min((paginaActual - 1) * filasPorPagina + 1, datosOrganizados.length),
        filasFin: Math.min(paginaActual * filasPorPagina, datosOrganizados.length)
    }), [datosOrganizados.length, paginaActual, filasPorPagina]);

    // Manejar ordenamiento
    const handleOrder = useCallback((columnaKey) => {
        if (orderBy === columnaKey) {
            setOrderDirection(prev => prev === 'asc' ? 'desc' : 'asc');
        } else {
            setOrderBy(columnaKey);
            setOrderDirection('asc');
        }
    }, [orderBy]);

    // Manejar selección
    const handleSeleccion = useCallback((filaId, seleccionar = null) => {
        setFilasSeleccionadas(prev => {
            const nuevaSeleccion = new Set(prev);

            if (seleccionar === null) {
                // Toggle
                if (nuevaSeleccion.has(filaId)) {
                    nuevaSeleccion.delete(filaId);
                } else {
                    nuevaSeleccion.add(filaId);
                }
            } else if (seleccionar) {
                nuevaSeleccion.add(filaId);
            } else {
                nuevaSeleccion.delete(filaId);
            }

            return nuevaSeleccion;
        });
    }, []);

    // Seleccionar todas las filas visibles
    const handleSeleccionarTodas = useCallback(() => {
        const todasSeleccionadas = datosPaginados.every(fila =>
            filasSeleccionadas.has(fila.id)
        );

        if (todasSeleccionadas) {
            // Deseleccionar todas
            setFilasSeleccionadas(prev => {
                const nuevaSeleccion = new Set(prev);
                datosPaginados.forEach(fila => nuevaSeleccion.delete(fila.id));
                return nuevaSeleccion;
            });
        } else {
            // Seleccionar todas
            setFilasSeleccionadas(prev => {
                const nuevaSeleccion = new Set(prev);
                datosPaginados.forEach(fila => nuevaSeleccion.add(fila.id));
                return nuevaSeleccion;
            });
        }
    }, [datosPaginados, filasSeleccionadas]);

    // Manejar acción
    const handleAccion = useCallback((accion, fila) => {
        if (onAction) {
            onAction(accion, fila, filasSeleccionadas);
        }
    }, [onAction, filasSeleccionadas]);

    // Exportar datos
    const handleExportar = useCallback((formato = 'csv') => {
        const datosExportar = datosOrganizados.map(fila => {
            const filaExportable = {};
            columnas
                .filter(col => columnasVisibles.has(col.key) && col.tipo !== 'actions')
                .forEach(col => {
                    filaExportable[col.titulo] = formatearValor(fila[col.key], col.tipo);
                });
            return filaExportable;
        });

        if (formato === 'csv') {
            const csv = [
                Object.keys(datosExportar[0]).join(','),
                ...datosExportar.map(fila => Object.values(fila).join(','))
            ].join('\n');

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${widget.titulo || 'tabla'}_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
            window.URL.revokeObjectURL(url);
        }
    }, [datosOrganizados, columnas, columnasVisibles, formatearValor, widget.titulo]);

    // Renderizar celda
    const renderCelda = useCallback((fila, columna) => {
        if (columna.tipo === 'actions') {
            return (
                <div className="cell-actions">
                    {accionesDisponibles.map(accionKey => {
                        const accion = ACCIONES_PREDEFINIDAS[accionKey];
                        if (!accion) return null;

                        return (
                            <button
                                key={accionKey}
                                className={`btn-action ${accion.clase}`}
                                onClick={() => handleAccion(accionKey, fila)}
                                title={accion.titulo}
                            >
                                {accion.icono}
                            </button>
                        );
                    })}
                </div>
            );
        }

        const valor = fila[columna.key];
        return (
            <div className={`cell-content cell-${columna.tipo}`}>
                {formatearValor(valor, columna.tipo, columna.opciones)}
            </div>
        );
    }, [accionesDisponibles, handleAccion, formatearValor]);

    // Vista móvil como cards
    if (isMobile) {
        return (
            <div className="table-widget mobile-cards">
                {/* Header móvil */}
                <div className="mobile-header">
                    <h3 className="widget-title">{widget.titulo}</h3>
                    {filterable && (
                        <input
                            type="text"
                            placeholder="Buscar..."
                            value={filtroLocal}
                            onChange={(e) => setFiltroLocal(e.target.value)}
                            className="mobile-search"
                        />
                    )}
                </div>

                {/* Cards */}
                <div className="mobile-cards-container">
                    {datosPaginados.map(fila => (
                        <div key={fila.id} className="mobile-card">
                            {columnas
                                .filter(col => columnasVisibles.has(col.key) && col.tipo !== 'actions')
                                .map(columna => (
                                    <div key={columna.key} className="card-field">
                                        <label className="field-label">{columna.titulo}</label>
                                        <div className="field-value">
                                            {formatearValor(fila[columna.key], columna.tipo)}
                                        </div>
                                    </div>
                                ))}

                            {accionesDisponibles.length > 0 && (
                                <div className="card-actions">
                                    {accionesDisponibles.map(accionKey => {
                                        const accion = ACCIONES_PREDEFINIDAS[accionKey];
                                        return (
                                            <button
                                                key={accionKey}
                                                className={`btn-action ${accion.clase}`}
                                                onClick={() => handleAccion(accionKey, fila)}
                                            >
                                                {accion.icono} {accion.titulo}
                                            </button>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    ))}
                </div>

                {/* Paginación móvil */}
                {paginable && infoPaginacion.totalPaginas > 1 && (
                    <div className="mobile-pagination">
                        <button
                            onClick={() => setPaginaActual(prev => Math.max(1, prev - 1))}
                            disabled={paginaActual === 1}
                            className="btn-page"
                        >
                            ‹ Anterior
                        </button>

                        <span className="page-info">
                            {paginaActual} de {infoPaginacion.totalPaginas}
                        </span>

                        <button
                            onClick={() => setPaginaActual(prev => Math.min(infoPaginacion.totalPaginas, prev + 1))}
                            disabled={paginaActual === infoPaginacion.totalPaginas}
                            className="btn-page"
                        >
                            Siguiente ›
                        </button>
                    </div>
                )}
            </div>
        );
    }

    // Vista desktop como tabla
    return (
        <div className="table-widget desktop-table">
            {/* Toolbar */}
            <div className="table-toolbar">
                <div className="toolbar-left">
                    <h3 className="widget-title">{widget.titulo}</h3>
                    {filasSeleccionadas.size > 0 && (
                        <span className="selection-info">
                            {filasSeleccionadas.size} seleccionadas
                        </span>
                    )}
                </div>

                <div className="toolbar-right">
                    {filterable && (
                        <input
                            type="text"
                            placeholder="Buscar en la tabla..."
                            value={filtroLocal}
                            onChange={(e) => setFiltroLocal(e.target.value)}
                            className="table-search"
                        />
                    )}

                    {exportable && (
                        <button
                            onClick={() => handleExportar('csv')}
                            className="btn-export"
                            title="Exportar a CSV"
                        >
                            📥 Exportar
                        </button>
                    )}
                </div>
            </div>

            {/* Tabla */}
            <div className="table-container">
                <table className="data-table">
                    <thead>
                        <tr>
                            {seleccionMultiple && (
                                <th className="col-checkbox">
                                    <input
                                        type="checkbox"
                                        checked={datosPaginados.length > 0 && datosPaginados.every(fila => filasSeleccionadas.has(fila.id))}
                                        onChange={handleSeleccionarTodas}
                                    />
                                </th>
                            )}

                            {columnas
                                .filter(col => columnasVisibles.has(col.key))
                                .map(columna => (
                                    <th
                                        key={columna.key}
                                        className={`col-${columna.tipo} ${columna.sortable ? 'sortable' : ''}`}
                                        onClick={() => columna.sortable && handleOrder(columna.key)}
                                    >
                                        <div className="th-content">
                                            <span>{columna.titulo}</span>
                                            {columna.sortable && (
                                                <span className="sort-indicator">
                                                    {orderBy === columna.key ? (
                                                        orderDirection === 'asc' ? '▲' : '▼'
                                                    ) : '⇅'}
                                                </span>
                                            )}
                                        </div>
                                    </th>
                                ))}
                        </tr>
                    </thead>

                    <tbody>
                        {datosPaginados.map(fila => (
                            <tr
                                key={fila.id}
                                className={`table-row ${filasSeleccionadas.has(fila.id) ? 'selected' : ''}`}
                            >
                                {seleccionMultiple && (
                                    <td className="col-checkbox">
                                        <input
                                            type="checkbox"
                                            checked={filasSeleccionadas.has(fila.id)}
                                            onChange={() => handleSeleccion(fila.id)}
                                        />
                                    </td>
                                )}

                                {columnas
                                    .filter(col => columnasVisibles.has(col.key))
                                    .map(columna => (
                                        <td
                                            key={`${fila.id}-${columna.key}`}
                                            className={`col-${columna.tipo}`}
                                        >
                                            {renderCelda(fila, columna)}
                                        </td>
                                    ))}
                            </tr>
                        ))}
                    </tbody>
                </table>

                {datosOrganizados.length === 0 && (
                    <div className="table-empty">
                        <p>No hay datos disponibles</p>
                    </div>
                )}
            </div>

            {/* Paginación */}
            {paginable && infoPaginacion.totalPaginas > 1 && (
                <div className="table-pagination">
                    <div className="pagination-info">
                        Mostrando {infoPaginacion.filasInicio} a {infoPaginacion.filasFin} de {infoPaginacion.totalFilas} registros
                    </div>

                    <div className="pagination-controls">
                        <select
                            value={filasPorPagina}
                            onChange={(e) => setFilasPorPagina(Number(e.target.value))}
                            className="page-size-selector"
                        >
                            <option value={10}>10</option>
                            <option value={25}>25</option>
                            <option value={50}>50</option>
                            <option value={100}>100</option>
                        </select>

                        <div className="page-buttons">
                            <button
                                onClick={() => setPaginaActual(1)}
                                disabled={paginaActual === 1}
                                className="btn-page"
                            >
                                ⟪
                            </button>
                            <button
                                onClick={() => setPaginaActual(prev => Math.max(1, prev - 1))}
                                disabled={paginaActual === 1}
                                className="btn-page"
                            >
                                ‹
                            </button>

                            {Array.from(
                                { length: Math.min(5, infoPaginacion.totalPaginas) },
                                (_, i) => {
                                    const inicio = Math.max(1, paginaActual - 2);
                                    const pagina = inicio + i;
                                    if (pagina > infoPaginacion.totalPaginas) return null;

                                    return (
                                        <button
                                            key={pagina}
                                            onClick={() => setPaginaActual(pagina)}
                                            className={`btn-page ${paginaActual === pagina ? 'active' : ''}`}
                                        >
                                            {pagina}
                                        </button>
                                    );
                                }
                            )}

                            <button
                                onClick={() => setPaginaActual(prev => Math.min(infoPaginacion.totalPaginas, prev + 1))}
                                disabled={paginaActual === infoPaginacion.totalPaginas}
                                className="btn-page"
                            >
                                ›
                            </button>
                            <button
                                onClick={() => setPaginaActual(infoPaginacion.totalPaginas)}
                                disabled={paginaActual === infoPaginacion.totalPaginas}
                                className="btn-page"
                            >
                                ⟫
                            </button>
                        </div>
                    </div>
                </div>
            )}

            <style jsx>{`
                .table-widget {
                    height: 100%;
                    display: flex;
                    flex-direction: column;
                    background: white;
                    border-radius: 8px;
                    overflow: hidden;
                }

                .table-toolbar {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 16px;
                    border-bottom: 1px solid #e5e7eb;
                    background: #f8fafc;
                }

                .toolbar-left {
                    display: flex;
                    align-items: center;
                    gap: 16px;
                }

                .widget-title {
                    font-size: 16px;
                    font-weight: 600;
                    color: #1f2937;
                    margin: 0;
                }

                .selection-info {
                    background: #dbeafe;
                    color: #1e40af;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    font-weight: 500;
                }

                .toolbar-right {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                .table-search {
                    padding: 6px 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 6px;
                    font-size: 14px;
                    width: 200px;
                }

                .btn-export {
                    padding: 6px 12px;
                    border: 1px solid #d1d5db;
                    background: white;
                    border-radius: 6px;
                    font-size: 12px;
                    cursor: pointer;
                    transition: all 0.2s;
                }

                .btn-export:hover {
                    border-color: #3b82f6;
                    background: #eff6ff;
                }

                .table-container {
                    flex: 1;
                    overflow: auto;
                }

                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .data-table th {
                    background: #f9fafb;
                    border-bottom: 1px solid #e5e7eb;
                    padding: 12px 8px;
                    text-align: left;
                    font-weight: 600;
                    font-size: 12px;
                    color: #374151;
                    white-space: nowrap;
                }

                .data-table th.sortable {
                    cursor: pointer;
                    user-select: none;
                }

                .data-table th.sortable:hover {
                    background: #f3f4f6;
                }

                .th-content {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .sort-indicator {
                    font-size: 10px;
                    margin-left: 4px;
                    opacity: 0.6;
                }

                .data-table td {
                    border-bottom: 1px solid #f3f4f6;
                    padding: 12px 8px;
                    font-size: 13px;
                    vertical-align: middle;
                }

                .table-row:hover {
                    background: #f9fafb;
                }

                .table-row.selected {
                    background: #eff6ff;
                }

                .col-checkbox {
                    width: 40px;
                    text-align: center;
                }

                .cell-actions {
                    display: flex;
                    gap: 4px;
                }

                .btn-action {
                    width: 28px;
                    height: 28px;
                    border: none;
                    background: transparent;
                    border-radius: 4px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    transition: all 0.2s;
                }

                .btn-action:hover {
                    background: #f3f4f6;
                }

                .btn-action.action-edit:hover {
                    background: #fef3c7;
                }

                .btn-action.action-delete:hover {
                    background: #fef2f2;
                }

                .btn-action.action-approve:hover {
                    background: #dcfce7;
                }

                .status-badge {
                    padding: 3px 8px;
                    border-radius: 12px;
                    font-size: 11px;
                    font-weight: 500;
                    text-transform: uppercase;
                }

                .status-draft {
                    background: #f3f4f6;
                    color: #6b7280;
                }

                .status-active {
                    background: #dbeafe;
                    color: #1e40af;
                }

                .status-paused {
                    background: #fef3c7;
                    color: #92400e;
                }

                .status-completed {
                    background: #dcfce7;
                    color: #166534;
                }

                .status-cancelled {
                    background: #fef2f2;
                    color: #dc2626;
                }

                .table-empty {
                    padding: 40px;
                    text-align: center;
                    color: #6b7280;
                }

                .table-pagination {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 16px;
                    border-top: 1px solid #e5e7eb;
                    background: #f8fafc;
                }

                .pagination-info {
                    font-size: 12px;
                    color: #6b7280;
                }

                .pagination-controls {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                .page-size-selector {
                    padding: 4px 8px;
                    border: 1px solid #d1d5db;
                    border-radius: 4px;
                    font-size: 12px;
                }

                .page-buttons {
                    display: flex;
                    gap: 2px;
                }

                .btn-page {
                    width: 32px;
                    height: 32px;
                    border: 1px solid #d1d5db;
                    background: white;
                    border-radius: 4px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    transition: all 0.2s;
                }

                .btn-page:hover:not(:disabled) {
                    background: #f3f4f6;
                }

                .btn-page:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }

                .btn-page.active {
                    background: #3b82f6;
                    color: white;
                    border-color: #3b82f6;
                }

                /* Estilos móviles */
                .mobile-header {
                    padding: 16px;
                    border-bottom: 1px solid #e5e7eb;
                }

                .mobile-search {
                    width: 100%;
                    margin-top: 12px;
                    padding: 8px 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 6px;
                }

                .mobile-cards-container {
                    padding: 16px;
                    gap: 16px;
                    display: flex;
                    flex-direction: column;
                }

                .mobile-card {
                    background: #f9fafb;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    padding: 16px;
                }

                .card-field {
                    margin-bottom: 12px;
                }

                .field-label {
                    display: block;
                    font-size: 11px;
                    font-weight: 600;
                    color: #6b7280;
                    text-transform: uppercase;
                    margin-bottom: 4px;
                }

                .field-value {
                    font-size: 14px;
                    color: #1f2937;
                }

                .card-actions {
                    margin-top: 16px;
                    padding-top: 16px;
                    border-top: 1px solid #e5e7eb;
                    display: flex;
                    gap: 8px;
                    flex-wrap: wrap;
                }

                .mobile-card .btn-action {
                    padding: 6px 12px;
                    background: white;
                    border: 1px solid #d1d5db;
                    border-radius: 6px;
                    font-size: 12px;
                    width: auto;
                    height: auto;
                    gap: 4px;
                }

                .mobile-pagination {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 16px;
                    border-top: 1px solid #e5e7eb;
                }

                .page-info {
                    font-size: 12px;
                    color: #6b7280;
                }
            `}</style>
        </div>
    );
};

export default TableWidget;