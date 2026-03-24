import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';

const data = window.__DASHBOARD_MOTOR_DATA__ || {
    plantillas: [], roles: [], secretarias: [], unidades: [], usuarios: [], chartTypeOptions: {}, widgetLibrary: [], dataScopeOptions: [], historial: [], urls: {}
};

async function apiPost(url, payload) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify(payload),
    });

    const body = await res.json().catch(() => ({}));
    if (!res.ok || body.success === false) {
        throw new Error(body.message || 'No se pudo guardar la configuracion.');
    }
    return body;
}

function Badge({ text, color = '#334155', bg = '#f1f5f9', border = '#cbd5e1' }) {
    return <span style={{ fontSize: 10, fontWeight: 700, color, background: bg, border: `1px solid ${border}`, borderRadius: 999, padding: '2px 8px' }}>{text}</span>;
}

function WidgetPreview({ widget }) {
    if (widget.tipo === 'kpi') {
        return (
            <div style={{ border: '1px solid #dbeafe', borderRadius: 10, background: 'linear-gradient(135deg,#eff6ff,#f8fafc)', padding: 8, marginTop: 8 }}>
                <div style={{ fontSize: 10, color: '#64748b', textTransform: 'uppercase', fontWeight: 700 }}>Vista KPI</div>
                <div style={{ marginTop: 4, fontSize: 20, fontWeight: 800, color: '#0f172a' }}>128</div>
                <svg viewBox="0 0 120 24" width="100%" height="24" aria-hidden="true">
                    <polyline
                        fill="none"
                        stroke="#2563eb"
                        strokeWidth="2"
                        points="2,20 20,16 38,18 56,10 74,12 92,7 110,9"
                    />
                </svg>
            </div>
        );
    }

    if (widget.metrica === 'procesos_por_estado') {
        return (
            <div style={{ border: '1px solid #dbeafe', borderRadius: 10, background: '#ffffff', padding: 8, marginTop: 8, display: 'flex', justifyContent: 'center' }}>
                <svg viewBox="0 0 120 60" width="100%" height="60" aria-hidden="true">
                    <circle cx="45" cy="30" r="20" fill="#dbeafe" />
                    <path d="M45 10 A20 20 0 0 1 63 38 L45 30 Z" fill="#2563eb" />
                    <path d="M45 30 L63 38 A20 20 0 0 1 30 44 Z" fill="#16a34a" />
                    <path d="M45 30 L30 44 A20 20 0 0 1 25 20 Z" fill="#f59e0b" />
                    <path d="M45 30 L25 20 A20 20 0 0 1 45 10 Z" fill="#ef4444" />
                </svg>
            </div>
        );
    }

    if (widget.metrica === 'contratos_por_mes') {
        return (
            <div style={{ border: '1px solid #dbeafe', borderRadius: 10, background: '#ffffff', padding: 8, marginTop: 8 }}>
                <svg viewBox="0 0 120 60" width="100%" height="60" aria-hidden="true">
                    <polyline fill="none" stroke="#2563eb" strokeWidth="2.5" points="5,46 25,34 45,36 65,22 85,26 105,14" />
                    <circle cx="5" cy="46" r="2" fill="#2563eb" />
                    <circle cx="25" cy="34" r="2" fill="#2563eb" />
                    <circle cx="45" cy="36" r="2" fill="#2563eb" />
                    <circle cx="65" cy="22" r="2" fill="#2563eb" />
                    <circle cx="85" cy="26" r="2" fill="#2563eb" />
                    <circle cx="105" cy="14" r="2" fill="#2563eb" />
                </svg>
            </div>
        );
    }

    return (
        <div style={{ border: '1px solid #dbeafe', borderRadius: 10, background: '#ffffff', padding: 8, marginTop: 8 }}>
            <svg viewBox="0 0 120 60" width="100%" height="60" aria-hidden="true">
                <rect x="8" y="32" width="16" height="20" rx="2" fill="#93c5fd" />
                <rect x="30" y="24" width="16" height="28" rx="2" fill="#60a5fa" />
                <rect x="52" y="16" width="16" height="36" rx="2" fill="#3b82f6" />
                <rect x="74" y="28" width="16" height="24" rx="2" fill="#2563eb" />
                <rect x="96" y="20" width="16" height="32" rx="2" fill="#1d4ed8" />
            </svg>
        </div>
    );
}

function DashboardMotorApp() {
    const [scope, setScope] = useState('secretaria');
    const [query, setQuery] = useState('');
    const [savingKey, setSavingKey] = useState('');
    const [toast, setToast] = useState(null);
    const [selectedTargetKey, setSelectedTargetKey] = useState(null);
    const [selectedWidgetId, setSelectedWidgetId] = useState(null);
    const [viewportWidth, setViewportWidth] = useState(() => window.innerWidth || 1280);

    const [secretarias, setSecretarias] = useState(data.secretarias || []);
    const [unidades, setUnidades] = useState(data.unidades || []);
    const [roles, setRoles] = useState(data.roles || []);
    const [usuarios, setUsuarios] = useState(data.usuarios || []);

    const chartOptions = data.chartTypeOptions || {};
    const widgetLibrary = data.widgetLibrary || [];
    const dataScopeOptions = data.dataScopeOptions || ['usuario', 'unidad', 'secretaria', 'global'];

    const filteredUsuarios = useMemo(() => {
        const q = query.trim().toLowerCase();
        if (!q) return usuarios;
        return usuarios.filter((u) => {
            const text = `${u.name} ${u.email} ${(u.roles || []).join(' ')}`.toLowerCase();
            return text.includes(q);
        });
    }, [usuarios, query]);

    const isMobile = viewportWidth < 960;

    const showToast = (msg, type = 'success') => {
        setToast({ msg, type });
        window.setTimeout(() => setToast(null), 2500);
    };

    const beginDragWidget = (event, widget) => {
        event.dataTransfer.setData('text/dashboard-widget', JSON.stringify(widget));
        event.dataTransfer.effectAllowed = 'copy';
    };

    const updateTargetState = (targetType, targetId, updater) => {
        if (targetType === 'secretaria') {
            setSecretarias((prev) => prev.map((s) => (s.id === targetId ? updater(s) : s)));
            return;
        }
        if (targetType === 'rol') {
            setRoles((prev) => prev.map((r) => (r.name === targetId ? updater(r) : r)));
            return;
        }
        if (targetType === 'unidad') {
            setUnidades((prev) => prev.map((u) => (u.id === targetId ? updater(u) : u)));
            return;
        }
        if (targetType === 'usuario') {
            setUsuarios((prev) => prev.map((u) => (u.id === targetId ? updater(u) : u)));
        }
    };

    const updateDataScope = (targetType, targetId, value) => {
        updateTargetState(targetType, targetId, (obj) => ({ ...obj, dataScope: value || 'usuario' }));
    };

    const updateChartType = (targetType, targetId, metric, value) => {
        updateTargetState(targetType, targetId, (obj) => {
            const current = obj.chartTypes || {};
            return { ...obj, chartTypes: { ...current, [metric]: value || '' } };
        });
    };

    const addWidgetToTarget = (targetType, targetId, widgetDefinition) => {
        const widgetId = `${widgetDefinition.metrica}_${Date.now()}_${Math.floor(Math.random() * 999)}`;

        updateTargetState(targetType, targetId, (obj) => {
            const current = Array.isArray(obj.customWidgets) ? obj.customWidgets : [];
            const nextOrder = current.length + 1;
            const nextWidget = {
                id: widgetId,
                titulo: widgetDefinition.titulo,
                tipo: widgetDefinition.tipo,
                metrica: widgetDefinition.metrica,
                orden: nextOrder,
                ancho: widgetDefinition.tipo === 'kpi' ? 3 : 6,
                alto: widgetDefinition.tipo === 'kpi' ? 1 : 2,
            };
            return {
                ...obj,
                templateId: null,
                customWidgets: [...current, nextWidget],
            };
        });
        setSelectedWidgetId(widgetId);
    };

    const removeWidgetFromTarget = (targetType, targetId, widgetId) => {
        updateTargetState(targetType, targetId, (obj) => {
            const current = Array.isArray(obj.customWidgets) ? obj.customWidgets : [];
            const without = current.filter((w) => String(w.id) !== String(widgetId));
            const normalized = without.map((w, idx) => ({ ...w, orden: idx + 1 }));
            return { ...obj, customWidgets: normalized };
        });

        if (String(selectedWidgetId) === String(widgetId)) {
            setSelectedWidgetId(null);
        }
    };

    const moveWidget = (targetType, targetId, widgetId, direction) => {
        updateTargetState(targetType, targetId, (obj) => {
            const current = Array.isArray(obj.customWidgets) ? [...obj.customWidgets] : [];
            const index = current.findIndex((w) => String(w.id) === String(widgetId));
            if (index < 0) return obj;
            const swapWith = direction === 'up' ? index - 1 : index + 1;
            if (swapWith < 0 || swapWith >= current.length) return obj;
            const temp = current[index];
            current[index] = current[swapWith];
            current[swapWith] = temp;
            const normalized = current.map((w, idx) => ({ ...w, orden: idx + 1 }));
            return { ...obj, customWidgets: normalized };
        });
    };

    const updateWidgetTitle = (targetType, targetId, widgetId, title) => {
        updateTargetState(targetType, targetId, (obj) => {
            const current = Array.isArray(obj.customWidgets) ? obj.customWidgets : [];
            return {
                ...obj,
                customWidgets: current.map((w) =>
                    String(w.id) === String(widgetId) ? { ...w, titulo: title } : w
                ),
            };
        });
    };

    const updateWidgetLayout = (targetType, targetId, widgetId, field, value) => {
        const parsed = Number.parseInt(String(value), 10);
        if (Number.isNaN(parsed)) return;

        const normalized = field === 'ancho'
            ? Math.max(2, Math.min(12, parsed))
            : Math.max(1, Math.min(4, parsed));

        updateTargetState(targetType, targetId, (obj) => {
            const current = Array.isArray(obj.customWidgets) ? obj.customWidgets : [];
            return {
                ...obj,
                customWidgets: current.map((w) =>
                    String(w.id) === String(widgetId) ? { ...w, [field]: normalized } : w
                ),
            };
        });
    };

    const saveTarget = async (targetType, target) => {
        const key = `${targetType}-${target.id || target.name}`;
        setSavingKey(key);
        try {
            let response = null;
            if (targetType === 'secretaria') {
                response = await apiPost(data.urls.assignSecretaria, {
                    secretaria_id: target.id,
                    dashboard_plantilla_id: null,
                    chart_types_secretaria: target.chartTypes || {},
                    custom_widgets_secretaria: target.customWidgets || [],
                    data_scope_secretaria: target.dataScope || 'secretaria',
                });
            }

            if (targetType === 'rol') {
                response = await apiPost(data.urls.assignRole, {
                    asignaciones: { [target.id]: null },
                    chart_types_role: { [target.id]: target.chartTypes || {} },
                    custom_widgets_role: { [target.id]: target.customWidgets || [] },
                    data_scope_role: { [target.id]: target.dataScope || 'usuario' },
                });
            }

            if (targetType === 'unidad') {
                response = await apiPost(data.urls.assignUnidad, {
                    unidad_id: target.id,
                    dashboard_plantilla_id: null,
                    chart_types_unidad: target.chartTypes || {},
                    custom_widgets_unidad: target.customWidgets || [],
                    data_scope_unidad: target.dataScope || 'unidad',
                });
            }

            if (targetType === 'usuario') {
                response = await apiPost(data.urls.assignUser, {
                    user_id: target.id,
                    dashboard_plantilla_id: null,
                    chart_types_usuario: target.chartTypes || {},
                    custom_widgets_usuario: target.customWidgets || [],
                    data_scope_usuario: target.dataScope || 'usuario',
                });
            }

            showToast(response?.message || 'Dashboard guardado correctamente.');
        } catch (error) {
            showToast(error.message || 'Error al guardar.', 'error');
        } finally {
            setSavingKey('');
        }
    };

    const targetCards = scope === 'secretaria'
        ? secretarias.map((s) => ({
            key: `sec-${s.id}`,
            id: s.id,
            title: s.nombre,
            subtitle: 'Secretaria',
            templateId: s.templateId,
            chartTypes: s.chartTypes || {},
            customWidgets: s.customWidgets || [],
            dataScope: s.dataScope || 'secretaria',
            type: 'secretaria',
        }))
        : scope === 'rol'
            ? roles.map((r) => ({
                key: `rol-${r.name}`,
                id: r.name,
                title: r.label || r.name,
                subtitle: `Rol: ${r.name}`,
                templateId: r.templateId,
                chartTypes: r.chartTypes || {},
                customWidgets: r.customWidgets || [],
                dataScope: r.dataScope || 'usuario',
                type: 'rol',
            }))
            : scope === 'unidad'
                ? unidades.map((u) => ({
                    key: `und-${u.id}`,
                    id: u.id,
                    title: u.nombre,
                    subtitle: `Unidad #${u.id}`,
                    templateId: u.templateId,
                    chartTypes: u.chartTypes || {},
                    customWidgets: u.customWidgets || [],
                    dataScope: u.dataScope || 'unidad',
                    type: 'unidad',
                }))
            : filteredUsuarios.map((u) => ({
                key: `usr-${u.id}`,
                id: u.id,
                title: u.name,
                subtitle: `${u.email} · ${(u.roles || []).join(', ')}`,
                templateId: u.templateId,
                chartTypes: u.chartTypes || {},
                customWidgets: u.customWidgets || [],
                dataScope: u.dataScope || 'usuario',
                type: 'usuario',
            }));

    const selectedTarget = useMemo(() => {
        if (!selectedTargetKey) return null;
        return targetCards.find((t) => t.key === selectedTargetKey) || null;
    }, [targetCards, selectedTargetKey]);

    useEffect(() => {
        setSelectedTargetKey(null);
    }, [scope]);

    useEffect(() => {
        if (!selectedTargetKey) return;
        const exists = targetCards.some((t) => t.key === selectedTargetKey);
        if (!exists) {
            setSelectedTargetKey(null);
        }
    }, [targetCards, selectedTargetKey]);

    useEffect(() => {
        setSelectedWidgetId(null);
    }, [selectedTargetKey, scope]);

    useEffect(() => {
        const onResize = () => setViewportWidth(window.innerWidth || 1280);
        window.addEventListener('resize', onResize);
        return () => window.removeEventListener('resize', onResize);
    }, []);

    const saveSelectedTarget = async () => {
        if (!selectedTarget) return;

        const widgets = selectedTarget.customWidgets || [];
        if (widgets.length === 0) {
            showToast('Agrega al menos un widget antes de guardar el dashboard.', 'error');
            return;
        }

        await saveTarget(selectedTarget.type, selectedTarget);
    };

    return (
        <div style={{ padding: isMobile ? 10 : 16, background: '#e9ecef', minHeight: '100vh' }}>
            {toast && (
                <div style={{ position: 'fixed', top: 14, right: 16, zIndex: 3000, background: toast.type === 'error' ? '#dc2626' : '#059669', color: '#fff', padding: '10px 14px', borderRadius: 8, fontSize: 13, boxShadow: '0 8px 20px rgba(0,0,0,.18)' }}>
                    {toast.msg}
                </div>
            )}

            <div style={{ background: '#fff', border: '1px solid #d4d4d8', borderRadius: 10, overflow: 'hidden', boxShadow: '0 10px 28px rgba(0,0,0,.08)' }}>
                <div style={{ background: '#2f2f33', color: '#fff', padding: '8px 14px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12 }}>
                    <div style={{ fontSize: 13, fontWeight: 700, letterSpacing: '.02em' }}>Dashboard Studio - Gobernacion</div>
                    <div style={{ fontSize: 11, opacity: .85 }}>Vista tipo Power BI</div>
                </div>

                <div style={{ background: '#f3f4f6', borderBottom: '1px solid #d1d5db', padding: '8px 12px' }}>
                    <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginBottom: 8 }}>
                        <button type="button" style={{ border: '1px solid #a3a3a3', borderRadius: 6, background: '#fff', fontSize: 11, fontWeight: 700, padding: '5px 10px' }}>Inicio</button>
                        <button type="button" style={{ border: '1px solid #d4d4d8', borderRadius: 6, background: '#fafafa', fontSize: 11, padding: '5px 10px' }}>Insertar</button>
                        <button type="button" style={{ border: '1px solid #d4d4d8', borderRadius: 6, background: '#fafafa', fontSize: 11, padding: '5px 10px' }}>Modelado</button>
                        <button type="button" style={{ border: '1px solid #d4d4d8', borderRadius: 6, background: '#fafafa', fontSize: 11, padding: '5px 10px' }}>Vista</button>
                    </div>
                    <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap', alignItems: 'center' }}>
                        <div style={{ border: '1px solid #d1d5db', borderRadius: 8, background: '#fff', padding: '6px 8px', fontSize: 11, color: '#374151' }}>Nuevo visual</div>
                        <div style={{ border: '1px solid #d1d5db', borderRadius: 8, background: '#fff', padding: '6px 8px', fontSize: 11, color: '#374151' }}>Transformar datos</div>
                        <div style={{ border: '1px solid #d1d5db', borderRadius: 8, background: '#fff', padding: '6px 8px', fontSize: 11, color: '#374151' }}>Publicar</div>
                    </div>
                </div>

                <div style={{ padding: '10px 14px', borderBottom: '1px solid #e5e7eb', background: '#ffffff', color: '#111827' }}>
                    <div style={{ fontSize: 14, fontWeight: 700 }}>Constructor visual de dashboards</div>
                    <div style={{ fontSize: 12, color: '#6b7280', marginTop: 2 }}>Selecciona objetivo, arrastra widgets al lienzo y construye desde cero.</div>
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: isMobile ? '1fr' : '280px 1fr 320px', minHeight: 640 }}>
                    <aside style={{ borderRight: isMobile ? 'none' : '1px solid #e5e7eb', borderBottom: isMobile ? '1px solid #e5e7eb' : 'none', background: '#fbfbfc', order: isMobile ? 2 : 0 }}>
                        <div style={{ padding: 12, borderBottom: '1px solid #e5e7eb' }}>
                            <div style={{ fontSize: 12, fontWeight: 700, color: '#1f2937' }}>Visualizaciones</div>
                            <div style={{ fontSize: 11, color: '#94a3b8', marginTop: 2 }}>Arrastra al lienzo central</div>
                        </div>

                        <div style={{ padding: 10, maxHeight: 560, overflowY: 'auto' }}>
                            {widgetLibrary.map((widget) => (
                                <div key={`${widget.metrica}-${widget.tipo}`}
                                     draggable
                                     onDragStart={(e) => beginDragWidget(e, widget)}
                                     style={{ border: '1px solid #e2e8f0', borderRadius: 10, background: '#f8fafc', padding: 10, marginBottom: 8, cursor: 'grab' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 6 }}>
                                        <div style={{ fontSize: 13, fontWeight: 700, color: '#1f2937' }}>{widget.titulo}</div>
                                        <Badge
                                            text={widget.tipo === 'kpi' ? 'KPI' : 'CHART'}
                                            bg={widget.tipo === 'kpi' ? '#dcfce7' : '#dbeafe'}
                                            color={widget.tipo === 'kpi' ? '#166534' : '#1e40af'}
                                            border={widget.tipo === 'kpi' ? '#86efac' : '#93c5fd'}
                                        />
                                    </div>
                                    <div style={{ fontSize: 11, color: '#6b7280', marginTop: 2 }}>{widget.metrica.replaceAll('_', ' ')}</div>
                                    <WidgetPreview widget={widget} />
                                    <button
                                        type="button"
                                        onClick={() => selectedTarget && addWidgetToTarget(selectedTarget.type, selectedTarget.id, widget)}
                                        disabled={!selectedTarget}
                                        style={{ marginTop: 8, border: '1px solid #cbd5e1', background: '#fff', borderRadius: 8, fontSize: 11, padding: '4px 8px', cursor: selectedTarget ? 'pointer' : 'not-allowed', opacity: selectedTarget ? 1 : 0.5 }}>
                                        Agregar
                                    </button>
                                </div>
                            ))}

                            {widgetLibrary.length === 0 && (
                                <div style={{ border: '1px dashed #cbd5e1', borderRadius: 10, padding: 12, fontSize: 11, color: '#64748b' }}>
                                    No hay widgets disponibles.
                                </div>
                            )}
                        </div>
                    </aside>

                    <section style={{ background: '#eceff3', order: isMobile ? 1 : 0 }}>
                        <div style={{ padding: 12, borderBottom: '1px solid #e5e7eb', display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap' }}>
                            <button onClick={() => setScope('secretaria')} style={{ border: '1px solid #cbd5e1', background: scope === 'secretaria' ? '#dbeafe' : '#fff', color: '#1e3a8a', borderRadius: 8, padding: '6px 10px', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>Por Secretaria</button>
                            <button onClick={() => setScope('unidad')} style={{ border: '1px solid #cbd5e1', background: scope === 'unidad' ? '#ede9fe' : '#fff', color: '#6d28d9', borderRadius: 8, padding: '6px 10px', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>Por Unidad</button>
                            <button onClick={() => setScope('rol')} style={{ border: '1px solid #cbd5e1', background: scope === 'rol' ? '#ede9fe' : '#fff', color: '#5b21b6', borderRadius: 8, padding: '6px 10px', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>Por Rol</button>
                            <button onClick={() => setScope('usuario')} style={{ border: '1px solid #cbd5e1', background: scope === 'usuario' ? '#dcfce7' : '#fff', color: '#166534', borderRadius: 8, padding: '6px 10px', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>Por Usuario</button>

                            {scope === 'usuario' && (
                                <input
                                    type="text"
                                    value={query}
                                    onChange={(e) => setQuery(e.target.value)}
                                    placeholder="Buscar usuario, correo o rol"
                                    style={{ marginLeft: 8, padding: '7px 10px', borderRadius: 8, border: '1px solid #cbd5e1', minWidth: 260, fontSize: 12 }}
                                />
                            )}
                        </div>

                        <div style={{ padding: isMobile ? 10 : 14, height: 560, overflow: 'auto' }}>
                            {!selectedTarget && (
                                <div style={{ border: '2px dashed #cbd5e1', borderRadius: 16, minHeight: 460, display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#fff' }}>
                                    <div style={{ textAlign: 'center', color: '#64748b', maxWidth: 360, padding: 24 }}>
                                        <div style={{ fontSize: 30, fontWeight: 800, color: '#1f2937', marginBottom: 10 }}>Comience a construir su dashboard</div>
                                        <div style={{ fontSize: 14, lineHeight: 1.6 }}>
                                            Seleccione un objetivo en el panel derecho y arrastre widgets desde la libreria para iniciar la configuracion.
                                        </div>
                                    </div>
                                </div>
                            )}

                            {selectedTarget && (() => {
                                const saveKey = `${selectedTarget.type}-${selectedTarget.id}`;
                                const widgets = [...(selectedTarget.customWidgets || [])]
                                    .sort((a, b) => (a.orden || 0) - (b.orden || 0))
                                    .map((w) => ({
                                        ...w,
                                        ancho: Number.isFinite(Number(w.ancho)) ? Number(w.ancho) : (w.tipo === 'kpi' ? 3 : 6),
                                        alto: Number.isFinite(Number(w.alto)) ? Number(w.alto) : (w.tipo === 'kpi' ? 1 : 2),
                                    }));
                                const selectedWidget = widgets.find((w) => String(w.id) === String(selectedWidgetId)) || null;
                                return (
                                    <div style={{ background: '#fff', border: '1px solid #d1d5db', borderRadius: 10, padding: 16, boxShadow: '0 6px 18px rgba(0,0,0,.06)' }}>
                                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 8 }}>
                                            <div>
                                                <div style={{ fontSize: 18, fontWeight: 800, color: '#1e3a8a' }}>{selectedTarget.title}</div>
                                                <div style={{ fontSize: 12, color: '#64748b', marginTop: 2 }}>{selectedTarget.subtitle}</div>
                                            </div>
                                            <Badge text={selectedTarget.type.toUpperCase()} bg="#dbeafe" color="#1d4ed8" border="#93c5fd" />
                                        </div>

                                        <div
                                            onDragOver={(e) => e.preventDefault()}
                                            onDrop={(e) => {
                                                e.preventDefault();
                                                const raw = e.dataTransfer.getData('text/dashboard-widget');
                                                if (!raw) return;
                                                try {
                                                    const widget = JSON.parse(raw);
                                                    addWidgetToTarget(selectedTarget.type, selectedTarget.id, widget);
                                                } catch {
                                                    showToast('No se pudo agregar el widget.', 'error');
                                                }
                                            }}
                                            style={{ marginTop: 14, border: '2px dashed #9ca3af', background: '#f9fafb', borderRadius: 10, padding: '12px 14px', fontSize: 12, color: '#374151', fontWeight: 700 }}>
                                            Arrastre y suelte aqui los widgets
                                        </div>

                                        {widgets.length === 0 && (
                                            <div style={{ marginTop: 14, border: '1px dashed #cbd5e1', borderRadius: 12, padding: 16, color: '#64748b', fontSize: 12 }}>
                                                El lienzo esta vacio. Agregue widgets para crear un dashboard desde cero.
                                            </div>
                                        )}

                                        {widgets.length > 0 && (
                                            <div style={{ marginTop: 14, border: '1px solid #d1d5db', borderRadius: 10, padding: 12, background: '#f3f4f6' }}>
                                                <div style={{ fontSize: 11, color: '#475569', marginBottom: 10, fontWeight: 700, textTransform: 'uppercase' }}>Lienzo</div>
                                                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(12, minmax(0, 1fr))', gap: 8, gridAutoRows: 64 }}>
                                                    {widgets.map((widget, index) => {
                                                        const active = String(selectedWidgetId) === String(widget.id);
                                                        return (
                                                            <div
                                                                key={widget.id}
                                                                onClick={() => setSelectedWidgetId(widget.id)}
                                                                style={{
                                                                    gridColumn: `span ${Math.max(2, Math.min(12, widget.ancho || (widget.tipo === 'kpi' ? 3 : 6)))}`,
                                                                    gridRow: `span ${Math.max(1, Math.min(4, widget.alto || (widget.tipo === 'kpi' ? 1 : 2)))}`,
                                                                    border: `2px solid ${active ? '#2563eb' : '#cbd5e1'}`,
                                                                    borderRadius: 10,
                                                                    background: '#fff',
                                                                    padding: 10,
                                                                    cursor: 'pointer',
                                                                    boxShadow: active ? '0 0 0 2px rgba(37,99,235,.15)' : 'none',
                                                                }}>
                                                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 6 }}>
                                                                    <div style={{ fontSize: 12, fontWeight: 700, color: '#0f172a' }}>{widget.titulo || widget.metrica}</div>
                                                                    <Badge text={`#${index + 1}`} />
                                                                </div>
                                                                <div style={{ marginTop: 6, display: 'flex', gap: 6, alignItems: 'center', flexWrap: 'wrap' }}>
                                                                    <Badge
                                                                        text={widget.tipo === 'kpi' ? 'KPI' : 'CHART'}
                                                                        bg={widget.tipo === 'kpi' ? '#dcfce7' : '#dbeafe'}
                                                                        color={widget.tipo === 'kpi' ? '#166534' : '#1e40af'}
                                                                        border={widget.tipo === 'kpi' ? '#86efac' : '#93c5fd'}
                                                                    />
                                                                    <span style={{ fontSize: 10, color: '#64748b', textTransform: 'uppercase' }}>{widget.metrica.replaceAll('_', ' ')}</span>
                                                                </div>
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                        )}

                                        <div style={{ marginTop: 12, maxWidth: 320 }}>
                                            <div style={{ fontSize: 10, color: '#64748b', marginBottom: 4, textTransform: 'uppercase', fontWeight: 700 }}>Alcance de datos</div>
                                            <select
                                                value={selectedTarget.dataScope || 'usuario'}
                                                onChange={(e) => updateDataScope(selectedTarget.type, selectedTarget.id, e.target.value)}
                                                style={{ width: '100%', padding: '7px 8px', borderRadius: 8, border: '1px solid #d1d5db', fontSize: 11 }}>
                                                {dataScopeOptions.map((scopeValue) => (
                                                    <option key={scopeValue} value={scopeValue}>{scopeValue.toUpperCase()}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {selectedTarget.templateId && widgets.length > 0 && (
                                            <div style={{ marginTop: 10, fontSize: 11, color: '#0369a1', background: '#f0f9ff', border: '1px solid #bae6fd', borderRadius: 10, padding: '8px 10px' }}>
                                                Al guardar, esta configuracion personalizada reemplazara la plantilla heredada para este objetivo.
                                            </div>
                                        )}

                                        <div style={{ marginTop: 14, display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 10 }}>
                                            <button
                                                type="button"
                                                onClick={() => updateTargetState(selectedTarget.type, selectedTarget.id, (obj) => ({ ...obj, customWidgets: [], chartTypes: {} }))}
                                                style={{ border: '1px solid #cbd5e1', background: '#fff', color: '#0f172a', borderRadius: 9, padding: '8px 12px', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>
                                                Limpiar lienzo
                                            </button>
                                            <button
                                                onClick={saveSelectedTarget}
                                                disabled={savingKey === saveKey}
                                                style={{ background: '#15803d', color: '#fff', border: 'none', borderRadius: 9, padding: '8px 14px', fontSize: 12, fontWeight: 700, cursor: 'pointer', opacity: savingKey === saveKey ? 0.6 : 1 }}>
                                                {savingKey === saveKey ? 'Guardando...' : 'Guardar configuracion'}
                                            </button>
                                        </div>

                                        {selectedWidget && (
                                            <div style={{ marginTop: 14, border: '1px solid #cbd5e1', borderRadius: 12, background: '#ffffff', padding: 12 }}>
                                                <div style={{ fontSize: 11, fontWeight: 800, color: '#1f2937', textTransform: 'uppercase', marginBottom: 8 }}>Propiedades del visual</div>

                                                <div style={{ display: 'grid', gap: 8 }}>
                                                    <div>
                                                        <div style={{ fontSize: 10, color: '#64748b', marginBottom: 4, textTransform: 'uppercase', fontWeight: 700 }}>Titulo</div>
                                                        <input
                                                            type="text"
                                                            value={selectedWidget.titulo || ''}
                                                            onChange={(e) => updateWidgetTitle(selectedTarget.type, selectedTarget.id, selectedWidget.id, e.target.value)}
                                                            style={{ width: '100%', padding: '7px 8px', borderRadius: 8, border: '1px solid #d1d5db', fontSize: 12 }}
                                                        />
                                                    </div>

                                                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
                                                        <div>
                                                            <div style={{ fontSize: 10, color: '#64748b', marginBottom: 4, textTransform: 'uppercase', fontWeight: 700 }}>Ancho (2-12)</div>
                                                            <input
                                                                type="number"
                                                                min="2"
                                                                max="12"
                                                                value={selectedWidget.ancho || (selectedWidget.tipo === 'kpi' ? 3 : 6)}
                                                                onChange={(e) => updateWidgetLayout(selectedTarget.type, selectedTarget.id, selectedWidget.id, 'ancho', e.target.value)}
                                                                style={{ width: '100%', padding: '7px 8px', borderRadius: 8, border: '1px solid #d1d5db', fontSize: 12 }}
                                                            />
                                                        </div>
                                                        <div>
                                                            <div style={{ fontSize: 10, color: '#64748b', marginBottom: 4, textTransform: 'uppercase', fontWeight: 700 }}>Alto (1-4)</div>
                                                            <input
                                                                type="number"
                                                                min="1"
                                                                max="4"
                                                                value={selectedWidget.alto || (selectedWidget.tipo === 'kpi' ? 1 : 2)}
                                                                onChange={(e) => updateWidgetLayout(selectedTarget.type, selectedTarget.id, selectedWidget.id, 'alto', e.target.value)}
                                                                style={{ width: '100%', padding: '7px 8px', borderRadius: 8, border: '1px solid #d1d5db', fontSize: 12 }}
                                                            />
                                                        </div>
                                                    </div>

                                                    {selectedWidget.tipo === 'chart' && (
                                                        <div>
                                                            <div style={{ fontSize: 10, color: '#64748b', marginBottom: 4, textTransform: 'uppercase', fontWeight: 700 }}>Tipo de grafica</div>
                                                            <select
                                                                value={(selectedTarget.chartTypes || {})[selectedWidget.metrica] || ''}
                                                                onChange={(e) => updateChartType(selectedTarget.type, selectedTarget.id, selectedWidget.metrica, e.target.value)}
                                                                style={{ width: '100%', padding: '7px 8px', borderRadius: 8, border: '1px solid #d1d5db', fontSize: 11 }}>
                                                                <option value="">Default</option>
                                                                {(chartOptions[selectedWidget.metrica] || []).map((opt) => <option key={opt} value={opt}>{opt.toUpperCase()}</option>)}
                                                            </select>
                                                        </div>
                                                    )}

                                                    <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
                                                        <button type="button" onClick={() => moveWidget(selectedTarget.type, selectedTarget.id, selectedWidget.id, 'up')} style={{ border: '1px solid #cbd5e1', background: '#fff', borderRadius: 8, fontSize: 11, padding: '5px 8px', cursor: 'pointer' }}>Mover arriba</button>
                                                        <button type="button" onClick={() => moveWidget(selectedTarget.type, selectedTarget.id, selectedWidget.id, 'down')} style={{ border: '1px solid #cbd5e1', background: '#fff', borderRadius: 8, fontSize: 11, padding: '5px 8px', cursor: 'pointer' }}>Mover abajo</button>
                                                        <button type="button" onClick={() => removeWidgetFromTarget(selectedTarget.type, selectedTarget.id, selectedWidget.id)} style={{ border: '1px solid #fecaca', background: '#fff1f2', color: '#be123c', borderRadius: 8, fontSize: 11, padding: '5px 8px', cursor: 'pointer' }}>Eliminar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                );
                            })()}
                        </div>
                    </section>

                    <aside style={{ borderLeft: isMobile ? 'none' : '1px solid #e5e7eb', borderTop: isMobile ? '1px solid #e5e7eb' : 'none', background: '#ffffff', order: isMobile ? 3 : 0 }}>
                        <div style={{ padding: 12, borderBottom: '1px solid #e5e7eb' }}>
                            <div style={{ fontSize: 12, fontWeight: 700, color: '#1f2937' }}>Filtros y Objetivos</div>
                            <div style={{ fontSize: 11, color: '#94a3b8', marginTop: 2 }}>Seleccione donde construir</div>
                        </div>

                        <div style={{ padding: 10, borderBottom: '1px solid #e5e7eb', display: 'grid', gap: 8 }}>
                            <div style={{ display: 'flex', gap: 6 }}>
                                <button onClick={() => setScope('secretaria')} style={{ border: '1px solid #cbd5e1', background: scope === 'secretaria' ? '#dbeafe' : '#fff', color: '#1e3a8a', borderRadius: 8, padding: '6px 8px', fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>Secretaria</button>
                                <button onClick={() => setScope('unidad')} style={{ border: '1px solid #cbd5e1', background: scope === 'unidad' ? '#ede9fe' : '#fff', color: '#6d28d9', borderRadius: 8, padding: '6px 8px', fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>Unidad</button>
                                <button onClick={() => setScope('rol')} style={{ border: '1px solid #cbd5e1', background: scope === 'rol' ? '#ede9fe' : '#fff', color: '#5b21b6', borderRadius: 8, padding: '6px 8px', fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>Rol</button>
                                <button onClick={() => setScope('usuario')} style={{ border: '1px solid #cbd5e1', background: scope === 'usuario' ? '#dcfce7' : '#fff', color: '#166534', borderRadius: 8, padding: '6px 8px', fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>Usuario</button>
                            </div>

                            {scope === 'usuario' && (
                                <input
                                    type="text"
                                    value={query}
                                    onChange={(e) => setQuery(e.target.value)}
                                    placeholder="Buscar usuario, correo o rol"
                                    style={{ padding: '7px 9px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 12 }}
                                />
                            )}
                        </div>

                        <div style={{ padding: 10, maxHeight: 500, overflowY: 'auto' }}>
                            {targetCards.map((target) => {
                                const active = selectedTargetKey === target.key;
                                const widgetCount = (target.customWidgets || []).length;
                                return (
                                    <div
                                        key={target.key}
                                        onClick={() => setSelectedTargetKey(target.key)}
                                        style={{ border: `1px solid ${active ? '#93c5fd' : '#e2e8f0'}`, background: active ? '#eff6ff' : '#f8fafc', borderRadius: 10, padding: 10, marginBottom: 8, cursor: 'pointer' }}>
                                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 6 }}>
                                            <div style={{ fontSize: 12, fontWeight: 700, color: '#1f2937' }}>{target.title}</div>
                                            <Badge text={target.type} />
                                        </div>
                                        <div style={{ fontSize: 10, color: '#64748b', marginTop: 3 }}>{target.subtitle}</div>
                                        <div style={{ marginTop: 5 }}>
                                            <Badge text={`Scope: ${(target.dataScope || 'usuario').toUpperCase()}`} bg="#eef2ff" color="#312e81" border="#c7d2fe" />
                                        </div>
                                        <div style={{ marginTop: 5 }}>
                                            <Badge
                                                text={`${widgetCount} widgets`}
                                                bg={widgetCount > 0 ? '#dcfce7' : '#f1f5f9'}
                                                color={widgetCount > 0 ? '#166534' : '#334155'}
                                                border={widgetCount > 0 ? '#86efac' : '#cbd5e1'}
                                            />
                                        </div>
                                    </div>
                                );
                            })}

                            {targetCards.length === 0 && (
                                <div style={{ border: '1px dashed #cbd5e1', borderRadius: 10, padding: 12, fontSize: 11, color: '#64748b' }}>
                                    No hay objetivos para este filtro.
                                </div>
                            )}
                        </div>
                    </aside>
                </div>
            </div>

            <div style={{ marginTop: 12, background: '#fff', border: '1px solid #d4d4d8', borderRadius: 10 }}>
                <div style={{ padding: '10px 14px', borderBottom: '1px solid #e5e7eb', fontSize: 13, fontWeight: 700, color: '#1f2937' }}>Historial de asignaciones</div>
                <div style={{ maxHeight: 220, overflowY: 'auto' }}>
                    {(data.historial || []).length === 0 && <div style={{ padding: 16, fontSize: 12, color: '#94a3b8' }}>Sin cambios registrados.</div>}
                    {(data.historial || []).map((h) => (
                        <div key={h.id} style={{ padding: '10px 14px', borderBottom: '1px solid #f1f5f9', fontSize: 12, color: '#334155' }}>
                            <strong>{h.fecha}</strong> · {h.actor} · {h.tipo_objetivo} · {String(h.accion || '').toUpperCase()} · {h.anterior} → {h.nueva}
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

const root = document.getElementById('dashboard-motor-app');
if (root) {
    createRoot(root).render(<DashboardMotorApp />);
}
