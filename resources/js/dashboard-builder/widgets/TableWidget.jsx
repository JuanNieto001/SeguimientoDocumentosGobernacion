/**
 * Table Widget
 *
 * Muestra datos en formato de tabla con ordenamiento y paginación básica.
 */

import React, { memo, useState, useMemo } from 'react';

const TableWidget = ({ widget, data, meta }) => {
    const [sortColumn, setSortColumn] = useState(null);
    const [sortDirection, setSortDirection] = useState('asc');
    const [currentPage, setCurrentPage] = useState(0);
    const rowsPerPage = 5;

    const columns = useMemo(() => {
        return data?.columns || [];
    }, [data?.columns]);

    const rows = useMemo(() => {
        let result = data?.rows || [];

        // Aplicar ordenamiento
        if (sortColumn) {
            result = [...result].sort((a, b) => {
                const aVal = a[sortColumn];
                const bVal = b[sortColumn];

                if (aVal === null || aVal === undefined) return 1;
                if (bVal === null || bVal === undefined) return -1;

                if (typeof aVal === 'number' && typeof bVal === 'number') {
                    return sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                }

                const aStr = String(aVal).toLowerCase();
                const bStr = String(bVal).toLowerCase();
                return sortDirection === 'asc'
                    ? aStr.localeCompare(bStr, 'es')
                    : bStr.localeCompare(aStr, 'es');
            });
        }

        return result;
    }, [data?.rows, sortColumn, sortDirection]);

    // Paginación
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    const paginatedRows = rows.slice(
        currentPage * rowsPerPage,
        (currentPage + 1) * rowsPerPage
    );

    // Manejar click en header para ordenar
    const handleSort = (columnKey) => {
        if (sortColumn === columnKey) {
            setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc');
        } else {
            setSortColumn(columnKey);
            setSortDirection('asc');
        }
    };

    // Formatear valor de celda
    const formatCellValue = (value, type) => {
        if (value === null || value === undefined) {
            return <span className="text-gray-300">—</span>;
        }

        if (type === 'datetime' && value) {
            try {
                return new Date(value).toLocaleDateString('es-CO', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                });
            } catch {
                return value;
            }
        }

        if (type === 'decimal' || (type === 'integer' && typeof value === 'number' && value > 1000)) {
            return value.toLocaleString('es-CO');
        }

        if (type === 'boolean') {
            return value
                ? <span className="text-green-600">✓</span>
                : <span className="text-gray-400">✗</span>;
        }

        if (typeof value === 'string' && value.length > 50) {
            return value.substring(0, 47) + '...';
        }

        return value;
    };

    if (columns.length === 0) {
        return (
            <div className="flex items-center justify-center h-full text-gray-400 text-sm">
                No hay columnas configuradas
            </div>
        );
    }

    return (
        <div className="flex flex-col h-full">
            {/* Tabla */}
            <div className="flex-1 overflow-auto">
                <table className="min-w-full text-sm">
                    <thead className="sticky top-0 bg-gray-50">
                        <tr>
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    onClick={() => handleSort(col.key)}
                                    className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
                                >
                                    <div className="flex items-center space-x-1">
                                        <span>{col.label}</span>
                                        {sortColumn === col.key && (
                                            <span className="text-green-600">
                                                {sortDirection === 'asc' ? '↑' : '↓'}
                                            </span>
                                        )}
                                    </div>
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {paginatedRows.map((row, rowIndex) => (
                            <tr
                                key={rowIndex}
                                className="hover:bg-gray-50 transition-colors"
                            >
                                {columns.map((col) => (
                                    <td
                                        key={col.key}
                                        className="px-3 py-2 whitespace-nowrap text-gray-700"
                                    >
                                        {formatCellValue(row[col.key], col.type)}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Paginación */}
            {totalPages > 1 && (
                <div className="flex items-center justify-between px-3 py-2 border-t border-gray-100 bg-gray-50 text-xs">
                    <span className="text-gray-500">
                        {rows.length} registros
                    </span>
                    <div className="flex items-center space-x-1">
                        <button
                            onClick={() => setCurrentPage(prev => Math.max(0, prev - 1))}
                            disabled={currentPage === 0}
                            className="px-2 py-1 rounded hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ←
                        </button>
                        <span className="px-2 text-gray-600">
                            {currentPage + 1} / {totalPages}
                        </span>
                        <button
                            onClick={() => setCurrentPage(prev => Math.min(totalPages - 1, prev + 1))}
                            disabled={currentPage === totalPages - 1}
                            className="px-2 py-1 rounded hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            →
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default memo(TableWidget);
