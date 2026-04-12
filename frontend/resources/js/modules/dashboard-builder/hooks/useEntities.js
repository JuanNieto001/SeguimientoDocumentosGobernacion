/**
 * Archivo: frontend/resources/js/modules/dashboard-builder/hooks/useEntities.js
 * Proposito: Modulo frontend documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */
// hooks/useEntities.js
import { useState, useEffect } from 'react';

export function useEntities() {
  const [entities, setEntities] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchEntities = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/dashboard-builder/entities', {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const result = await response.json();
      setEntities(result.entities || []);
    } catch (err) {
      setError(err.message);
      console.error('Error fetching entities:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEntities();
  }, []);

  return {
    entities,
    loading,
    error,
    refetch: fetchEntities
  };
}

export function useEntityFields(entityKey) {
  const [fields, setFields] = useState({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!entityKey) {
      setFields({});
      return;
    }

    const fetchFields = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(`/api/dashboard-builder/entities/${entityKey}/fields`, {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();
        setFields(result.fields || {});
      } catch (err) {
        setError(err.message);
        console.error('Error fetching entity fields:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchFields();
  }, [entityKey]);

  return {
    fields,
    loading,
    error
  };
}

export function useEntityStats(entityKey) {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!entityKey) {
      setStats(null);
      return;
    }

    const fetchStats = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(`/api/dashboard-builder/entity/${entityKey}/stats`, {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();
        setStats(result);
      } catch (err) {
        setError(err.message);
        console.error('Error fetching entity stats:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, [entityKey]);

  return {
    stats,
    loading,
    error
  };
}
