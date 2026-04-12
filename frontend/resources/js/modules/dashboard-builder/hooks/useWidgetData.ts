/**
 * Archivo: frontend/resources/js/modules/dashboard-builder/hooks/useWidgetData.ts
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// hooks/useWidgetData.ts
import { useState, useEffect } from 'react';
import type { WidgetData, Widget } from '../types/dashboard';

interface UseWidgetDataOptions {
  refreshInterval?: number;
  enabled?: boolean;
}

export function useWidgetData(widget: Widget, options: UseWidgetDataOptions = {}) {
  const [data, setData] = useState<WidgetData | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const { refreshInterval = 30000, enabled = true } = options;

  const fetchData = async () => {
    if (!enabled || !widget) return;
    
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/dashboard-builder/widget/query', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
          entity: widget.entity,
          widget_type: widget.type,
          aggregation: widget.config.aggregation,
          group_by: widget.config.groupBy || [],
          filters: widget.config.filters || [],
          limit: widget.config.limit || 100
        })
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `HTTP ${response.status}`);
      }

      const result = await response.json();
      setData(result);
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Error desconocido';
      setError(errorMessage);
      console.error('Error fetching widget data:', err);
    } finally {
      setLoading(false);
    }
  };

  // Fetch inicial y cuando cambian las dependencias
  useEffect(() => {
    fetchData();
  }, [
    widget?.entity,
    widget?.type,
    JSON.stringify(widget?.config)
  ]);

  // Auto-refresh
  useEffect(() => {
    if (!enabled || refreshInterval <= 0) return;

    const interval = setInterval(fetchData, refreshInterval);
    return () => clearInterval(interval);
  }, [refreshInterval, enabled, widget?.entity, JSON.stringify(widget?.config)]);

  return {
    data: data?.data || [],
    metadata: data ? {
      count: data.count,
      applied_filters: data.applied_filters,
      entity: data.entity,
      widget_type: data.widget_type
    } : null,
    loading,
    error,
    refetch: fetchData
  };
}
