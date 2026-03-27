/**
 * Timeline Widget
 *
 * Muestra evolución temporal de datos.
 */

import React, { memo, useMemo } from 'react';
import {
    AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer
} from 'recharts';

const TimelineWidget = ({ widget, data, meta }) => {
    const timelineData = useMemo(() => {
        if (!data || !data.timeline) return [];

        return data.timeline.map(item => ({
            date: formatDate(item.date),
            fullDate: item.date,
            count: item.count,
        })).reverse(); // Ordenar cronológicamente
    }, [data]);

    const stats = useMemo(() => {
        if (!timelineData.length) return { total: 0, avg: 0, max: 0 };

        const total = timelineData.reduce((sum, d) => sum + d.count, 0);
        const avg = Math.round(total / timelineData.length);
        const max = Math.max(...timelineData.map(d => d.count));

        return { total, avg, max };
    }, [timelineData]);

    return (
        <div className="h-full flex flex-col">
            {/* Stats rápidos */}
            <div className="flex justify-around py-2 border-b border-gray-100">
                <div className="text-center">
                    <div className="text-lg font-bold text-green-600">{stats.total}</div>
                    <div className="text-[10px] text-gray-400 uppercase">Total</div>
                </div>
                <div className="text-center">
                    <div className="text-lg font-bold text-blue-600">{stats.avg}</div>
                    <div className="text-[10px] text-gray-400 uppercase">Promedio</div>
                </div>
                <div className="text-center">
                    <div className="text-lg font-bold text-purple-600">{stats.max}</div>
                    <div className="text-[10px] text-gray-400 uppercase">Máximo</div>
                </div>
            </div>

            {/* Gráfica */}
            <div className="flex-1 pt-2">
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={timelineData} margin={{ top: 5, right: 10, left: 0, bottom: 5 }}>
                        <defs>
                            <linearGradient id="colorCount" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor="#059669" stopOpacity={0.3}/>
                                <stop offset="95%" stopColor="#059669" stopOpacity={0}/>
                            </linearGradient>
                        </defs>
                        <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                        <XAxis
                            dataKey="date"
                            tick={{ fontSize: 9 }}
                            tickLine={false}
                            axisLine={{ stroke: '#e5e7eb' }}
                        />
                        <YAxis
                            tick={{ fontSize: 9 }}
                            tickLine={false}
                            axisLine={{ stroke: '#e5e7eb' }}
                            width={30}
                        />
                        <Tooltip
                            content={({ active, payload }) => {
                                if (!active || !payload?.length) return null;
                                const data = payload[0].payload;
                                return (
                                    <div className="bg-white border border-gray-200 rounded-lg p-2 shadow-lg text-xs">
                                        <div className="font-medium text-gray-700">{data.fullDate}</div>
                                        <div className="text-green-600">{data.count} registros</div>
                                    </div>
                                );
                            }}
                        />
                        <Area
                            type="monotone"
                            dataKey="count"
                            stroke="#059669"
                            strokeWidth={2}
                            fill="url(#colorCount)"
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

function formatDate(dateStr) {
    if (!dateStr) return '';
    try {
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-CO', { month: 'short', day: 'numeric' });
    } catch {
        return dateStr;
    }
}

export default memo(TimelineWidget);
