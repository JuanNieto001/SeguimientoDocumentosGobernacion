import React, { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import {
    ReactFlow,
    Controls,
    Background,
    MiniMap,
    Handle,
    Position,
    useNodesState,
    useEdgesState,
    addEdge,
    MarkerType,
    ReactFlowProvider,
    useReactFlow,
    Panel,
} from '@xyflow/react';
import '@xyflow/react/dist/style.css';

/* ================================================================
   WorkflowApp – Constructor Visual de Flujos con React Flow
   Endpoint save: POST /api/motor-flujos/flujos/guardar-completo
   ================================================================ */

const API = '/api/motor-flujos';

/* ── helpers ── */
async function apiFetch(url, opts = {}) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const res = await fetch(url, {
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin', ...opts,
    });
    if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.errors ? Object.values(body.errors).flat().join(' | ') : (body.message || `Error ${res.status}`));
    }
    return res.json();
}

function useToast() {
    const [toasts, setToasts] = useState([]);
    const counter = useRef(0);
    const show = useCallback((msg, type = 'success') => {
        const id = ++counter.current;
        setToasts(p => [...p, { id, msg, type }]);
        setTimeout(() => setToasts(p => p.filter(t => t.id !== id)), 4000);
    }, []);
    return { toasts, show };
}

const Toasts = ({ toasts }) => !toasts.length ? null : (
    <div style={{ position: 'fixed', top: 16, right: 16, zIndex: 9999 }}>
        {toasts.map(t => (
            <div key={t.id} style={{
                marginBottom: 8, padding: '10px 18px', borderRadius: 8, color: '#fff', fontSize: 13,
                fontWeight: 500, boxShadow: '0 4px 12px rgba(0,0,0,.15)', animation: 'slideInRight .3s ease-out',
                background: t.type === 'error' ? '#dc2626' : t.type === 'warning' ? '#d97706' : '#059669'
            }}>
                {t.msg}
            </div>
        ))}
    </div>
);

/* ── Constantes ── */
const AREAS = [
    { v: 'unidad_solicitante', l: 'Unidad Solicitante', c: '#3B82F6' },
    { v: 'planeacion', l: 'Planeación', c: '#10B981' },
    { v: 'hacienda', l: 'Hacienda', c: '#F59E0B' },
    { v: 'juridica', l: 'Jurídica', c: '#8B5CF6' },
    { v: 'secop', l: 'SECOP', c: '#06B6D4' },
    { v: 'descentralizacion', l: 'Descentralización', c: '#EC4899' },
    { v: 'despacho', l: 'Despacho', c: '#EF4444' },
    { v: 'control_interno', l: 'Control Interno', c: '#84CC16' },
];
const areaColor = v => AREAS.find(a => a.v === v)?.c || '#6B7280';
const areaLabel = v => AREAS.find(a => a.v === v)?.l || v || 'Sin área';

/* ── CSS Animations inject ── */
const STYLE_ID = 'workflow-flow-styles';
function injectStyles() {
    if (document.getElementById(STYLE_ID)) return;
    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = `
        @keyframes nodeAppear { from { opacity:0; transform:scale(.85) translateY(10px); } to { opacity:1; transform:scale(1) translateY(0); } }
        @keyframes slideInRight { from { opacity:0; transform:translateX(30px); } to { opacity:1; transform:translateX(0); } }
        @keyframes slideInLeft { from { opacity:0; transform:translateX(-30px); } to { opacity:1; transform:translateX(0); } }
        @keyframes slideDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.5; } }
        @keyframes flowDash { to { stroke-dashoffset: -20; } }
        @keyframes spin { to { transform:rotate(360deg); } }
        @keyframes dotFlow { 0% { offset-distance:0%; } 100% { offset-distance:100%; } }
        @keyframes glowPulse { 0%,100% { filter:drop-shadow(0 0 3px rgba(59,130,246,.3)); } 50% { filter:drop-shadow(0 0 8px rgba(59,130,246,.6)); } }

        .react-flow__edge-path { stroke-dasharray: 8 4; animation: flowDash 1s linear infinite; }
        .react-flow__edge.animated .react-flow__edge-path { animation: flowDash .6s linear infinite; }
        .react-flow__handle { transition: all .15s ease; }
        .react-flow__handle:hover { transform: scale(1.4); box-shadow: 0 0 0 3px rgba(59,130,246,.3); }
        .paso-node { animation: nodeAppear .35s ease-out; cursor:pointer; }
        .paso-node:hover { filter: drop-shadow(0 4px 12px rgba(0,0,0,.1)); }
        .paso-node.selected-node { filter: drop-shadow(0 0 0 3px rgba(59,130,246,.4)); }
        .catalog-item { transition: all .15s ease; }
        .catalog-item:hover { transform: translateX(4px); background: #eff6ff !important; }
        .detail-panel { animation: slideInRight .25s ease-out; }
        .catalog-sidebar { animation: slideInLeft .25s ease-out; }
        .flow-canvas-wrapper .react-flow__minimap { border-radius: 8px; border: 1px solid #e5e7eb; }
        .flow-canvas-wrapper .react-flow__controls { border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .flow-canvas-wrapper .react-flow__controls button { border-bottom: 1px solid #e5e7eb; }
    `;
    document.head.appendChild(style);
}

/* Small helper to render an icon for a catalog item.
   The DB sometimes stores icon names like "CheckCircle" which were
   rendered as plain text; map common names to small emoji/SVG so the
   catalog looks tidy without adding a new dependency. */
function renderCatalogIcon(name, color) {
    const size = 20;
    const common = (name || '').toString().toLowerCase();
    const style = { width: size, height: size, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: 14 };
    if (!name) return <span style={style}>📄</span>;
    if (/check/.test(common) || /tick/.test(common) || /ok/.test(common)) return <span style={style}>✅</span>;
    if (/play/.test(common) || /start/.test(common)) return <span style={style}>▶️</span>;
    if (/users?/.test(common) || /user/.test(common)) return <span style={style}>👥</span>;
    if (/board|panel/.test(common)) return <span style={style}>📋</span>;
    if (/file|doc|document/.test(common)) return <span style={style}>📄</span>;
    if (/clock|time|hour/.test(common)) return <span style={style}>⏱️</span>;
    if (/playcircle|play-circle/.test(common)) return <span style={style}>▶️</span>;
    // fallback: show first letter in a colored circle
    const letter = (name || '?').toString().trim().charAt(0).toUpperCase();
    return (
        <div style={{ ...style, width: 28, height: 28, borderRadius: 6, background: (color || '#6b7280') + '15', color: color || '#6b7280', fontWeight: 700 }}>
            {letter}
        </div>
    );
}

/* ── Shared styles ── */
const S = {
    btn: (bg, color = '#fff') => ({ padding: '8px 18px', background: bg, color, border: 'none', borderRadius: 8, fontSize: 13, fontWeight: 600, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: 6, transition: 'all .15s' }),
    btnSm: (bg, color = '#fff') => ({ padding: '5px 12px', background: bg, color, border: 'none', borderRadius: 6, fontSize: 12, fontWeight: 500, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: 4, transition: 'all .15s' }),
    input: { width: '100%', padding: '8px 12px', border: '1px solid #d1d5db', borderRadius: 8, fontSize: 13, outline: 'none', boxSizing: 'border-box' },
    select: { padding: '8px 12px', border: '1px solid #d1d5db', borderRadius: 8, fontSize: 13, outline: 'none', boxSizing: 'border-box', background: '#fff' },
    label: { display: 'block', fontSize: 11, fontWeight: 600, color: '#6b7280', marginBottom: 4, textTransform: 'uppercase' },
    badge: (bg) => ({ display: 'inline-block', fontSize: 10, fontWeight: 600, padding: '2px 8px', borderRadius: 99, color: '#fff', background: bg }),
    card: { background: '#fff', border: '1px solid #e5e7eb', borderRadius: 12, marginBottom: 16, overflow: 'hidden', transition: 'all .2s' },
};


/* ================================================================
   CUSTOM NODE – PasoNode (for React Flow canvas)
   ================================================================ */
function PasoNode({ data, selected }) {
    const color = areaColor(data.area);
    const docsCount = data.documentos?.length || 0;

    const handleDelete = (e) => {
        e.stopPropagation();
        if (data.onDelete) data.onDelete(data._nodeId);
    };

    return (
        <div className={`paso-node ${selected ? 'selected-node' : ''}`}
            style={{
                background: '#fff', borderRadius: 10, width: 190, maxWidth: 190,
                border: selected ? `2px solid ${color}` : '1px solid #e5e7eb',
                boxShadow: selected ? `0 0 0 3px ${color}33, 0 4px 12px rgba(0,0,0,.1)` : '0 2px 6px rgba(0,0,0,.06)',
                overflow: 'hidden', transition: 'all .2s ease', position: 'relative',
            }}>
            {/* Delete button top-right */}
            <button onClick={handleDelete} className="node-delete-btn"
                title="Eliminar paso del flujo"
                style={{
                    position: 'absolute', top: 4, right: 4, zIndex: 10,
                    width: 18, height: 18, borderRadius: 4, border: 'none',
                    background: 'rgba(239,68,68,.08)', color: '#ef4444',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    cursor: 'pointer', fontSize: 11, fontWeight: 700, lineHeight: 1,
                    transition: 'all .15s', opacity: 0.4,
                }}
                onMouseEnter={e => { e.currentTarget.style.opacity = '1'; e.currentTarget.style.background = '#fef2f2'; e.currentTarget.style.transform = 'scale(1.15)'; }}
                onMouseLeave={e => { e.currentTarget.style.opacity = '0.4'; e.currentTarget.style.background = 'rgba(239,68,68,.08)'; e.currentTarget.style.transform = 'scale(1)'; }}>
                ✕
            </button>

            {/* Color bar top */}
            <div style={{ height: 3, background: `linear-gradient(90deg, ${color}, ${color}88)` }} />

            <Handle type="target" position={Position.Top}
                style={{ background: color, border: '2px solid #fff', width: 8, height: 8, top: -4 }} />
            <Handle type="target" position={Position.Left} id="left-target"
                style={{ background: color, border: '2px solid #fff', width: 8, height: 8, left: -4 }} />
            <Handle type="source" position={Position.Right} id="right-source"
                style={{ background: color, border: '2px solid #fff', width: 8, height: 8, right: -4 }} />

            <div style={{ padding: '8px 10px' }}>
                {/* Order badge + Name */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 4 }}>
                    <div style={{
                        width: 22, height: 22, borderRadius: 6, background: color, color: '#fff',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        fontWeight: 700, fontSize: 10, flexShrink: 0,
                    }}>
                        {data.order != null ? data.order + 1 : '?'}
                    </div>
                    <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{
                            fontWeight: 600, fontSize: 11, color: '#1f2937',
                            overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                        }}>
                            {data.nombre || 'Sin nombre'}
                        </div>
                    </div>
                </div>

                {/* Area label */}
                <div style={{ display: 'flex', gap: 3, flexWrap: 'wrap', alignItems: 'center' }}>
                    <span style={{ ...S.badge(color), fontSize: 8, padding: '1px 6px' }}>{areaLabel(data.area)}</span>
                    {docsCount > 0 && <span style={{ ...S.badge('#dbeafe'), color: '#2563eb', fontSize: 8, padding: '1px 6px' }}>{docsCount} check{docsCount > 1 ? 's' : ''}</span>}
                    {data.dias && <span style={{ fontSize: 8, color: '#9ca3af' }}>{data.dias}d</span>}
                </div>

                {/* Docs preview - más compacto */}
                {docsCount > 0 && (
                    <div style={{ marginTop: 6, borderTop: '1px solid #f3f4f6', paddingTop: 4 }}>
                        {data.documentos.slice(0, 2).map((d, i) => (
                            <div key={i} style={{ fontSize: 9, color: '#6b7280', display: 'flex', alignItems: 'center', gap: 3, padding: '1px 0' }}>
                                <span style={{ color: d.obligatorio ? '#059669' : '#d97706' }}>{d.obligatorio ? '✓' : '○'}</span>
                                <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{d.nombre}</span>
                            </div>
                        ))}
                        {docsCount > 2 && <div style={{ fontSize: 8, color: '#9ca3af', marginTop: 1 }}>+{docsCount - 2} más...</div>}
                    </div>
                )}
            </div>

            <Handle type="source" position={Position.Bottom} id="bottom"
                style={{ background: color, border: '2px solid #fff', width: 8, height: 8, bottom: -4 }} />
            <Handle type="source" position={Position.Left} id="left-source"
                style={{ background: color, border: '2px solid #fff', width: 8, height: 8, left: -4 }} />
            <Handle type="target" position={Position.Right} id="right-target"
                style={{ background: color, border: '2px solid #fff', width: 8, height: 8, right: -4 }} />
        </div>
    );
}

/* ── Start / End nodes ── */
function StartNode() {
    return (
        <div className="paso-node" style={{
            background: 'linear-gradient(135deg, #059669, #10B981)', borderRadius: 20,
            padding: '6px 16px', color: '#fff', fontWeight: 600, fontSize: 11,
            boxShadow: '0 2px 8px rgba(16,185,129,.3)', display: 'flex', alignItems: 'center', gap: 4,
        }}>
            <span style={{ fontSize: 12 }}>▶</span> INICIO
            <Handle type="source" position={Position.Bottom}
                style={{ background: '#059669', border: '2px solid #fff', width: 8, height: 8, bottom: -4 }} />
            <Handle type="source" position={Position.Right} id="right"
                style={{ background: '#059669', border: '2px solid #fff', width: 8, height: 8, right: -4 }} />
        </div>
    );
}

function EndNode() {
    return (
        <div className="paso-node" style={{
            background: 'linear-gradient(135deg, #dc2626, #ef4444)', borderRadius: 20,
            padding: '6px 16px', color: '#fff', fontWeight: 600, fontSize: 11,
            boxShadow: '0 2px 8px rgba(220,38,38,.3)', display: 'flex', alignItems: 'center', gap: 4,
        }}>
            <Handle type="target" position={Position.Top}
                style={{ background: '#dc2626', border: '2px solid #fff', width: 8, height: 8, top: -4 }} />
            <Handle type="target" position={Position.Left} id="left"
                style={{ background: '#dc2626', border: '2px solid #fff', width: 8, height: 8, left: -4 }} />
            <Handle type="target" position={Position.Right} id="right"
                style={{ background: '#dc2626', border: '2px solid #fff', width: 8, height: 8, right: -4 }} />
            <span style={{ fontSize: 12 }}>■</span> FIN
        </div>
    );
}

const nodeTypes = { pasoNode: PasoNode, startNode: StartNode, endNode: EndNode };


/* ================================================================
   CATALOG SIDEBAR – Draggable items from catalogo_pasos
   ================================================================ */
function CatalogSidebar({ catalogo, onAddPaso }) {
    const [search, setSearch] = useState('');
    const [collapsed, setCollapsed] = useState(false);

    const filtered = useMemo(() => {
        if (!search.trim()) return catalogo;
        const q = search.toLowerCase();
        return catalogo.filter(c => c.nombre.toLowerCase().includes(q) || (c.codigo || '').toLowerCase().includes(q) || (c.descripcion || '').toLowerCase().includes(q));
    }, [catalogo, search]);

    const onDragStart = (e, cat) => {
        e.dataTransfer.setData('application/reactflow-catalog', JSON.stringify(cat));
        e.dataTransfer.effectAllowed = 'move';
    };

    if (collapsed) {
        return (
            <div style={{
                position: 'absolute', left: 0, top: 0, bottom: 0, width: 40, background: '#fff',
                borderRight: '1px solid #e5e7eb', zIndex: 10, display: 'flex', flexDirection: 'column',
                alignItems: 'center', paddingTop: 12,
            }}>
                <button onClick={() => setCollapsed(false)}
                    style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: 18, color: '#6b7280', padding: 6 }}
                    title="Expandir catálogo">
                    📂
                </button>
            </div>
        );
    }

    return (
        <div className="catalog-sidebar" style={{
            position: 'absolute', left: 0, top: 0, bottom: 0, width: 260, background: '#fff',
            borderRight: '1px solid #e5e7eb', zIndex: 10, display: 'flex', flexDirection: 'column',
            boxShadow: '2px 0 12px rgba(0,0,0,.04)',
        }}>
            {/* Header */}
            <div style={{ padding: '14px 16px', borderBottom: '1px solid #e5e7eb', background: '#f9fafb' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <div>
                        <h3 style={{ margin: 0, fontSize: 13, fontWeight: 700, color: '#1f2937' }}>📋 Catálogo de Pasos</h3>
                        <p style={{ margin: '2px 0 0', fontSize: 10, color: '#9ca3af' }}>Arrastra al canvas para agregar</p>
                    </div>
                    <button onClick={() => setCollapsed(true)}
                        style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: 14, color: '#9ca3af', padding: 4 }}
                        title="Minimizar">
                        ◀
                    </button>
                </div>
                <input type="text" value={search} onChange={e => setSearch(e.target.value)}
                    placeholder="🔍 Buscar paso..."
                    style={{ ...S.input, marginTop: 10, fontSize: 12, padding: '6px 10px' }} />
            </div>

            {/* Items */}
            <div style={{ flex: 1, overflowY: 'auto', padding: '8px 0' }}>
                {filtered.map(cat => (
                    <div key={cat.id} className="catalog-item"
                        draggable
                        onDragStart={e => onDragStart(e, cat)}
                        onClick={() => onAddPaso(cat)}
                        style={{
                            padding: '10px 16px', cursor: 'grab', borderBottom: '1px solid #f3f4f6',
                            background: '#fff', userSelect: 'none',
                        }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                            <div style={{
                                width: 32, height: 32, borderRadius: 8, display: 'flex', alignItems: 'center',
                                justifyContent: 'center', fontSize: 14, flexShrink: 0,
                            }}>
                                {renderCatalogIcon(cat.icono, cat.color)}
                            </div>
                            <div style={{ flex: 1, minWidth: 0 }}>
                                <div style={{ fontWeight: 600, fontSize: 12, color: '#1f2937', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                    {cat.nombre}
                                </div>
                                <div style={{ display: 'flex', gap: 4, marginTop: 2 }}>
                                    <span style={{ fontSize: 9, background: '#f3f4f6', padding: '1px 6px', borderRadius: 4, color: '#6b7280' }}>{cat.tipo || 'paso'}</span>
                                    <span style={{ fontSize: 9, color: '#9ca3af' }}>{cat.codigo}</span>
                                </div>
                            </div>
                            <span style={{ fontSize: 14, color: '#d1d5db' }}>⋮⋮</span>
                        </div>
                        {cat.descripcion && (
                            <p style={{ margin: '4px 0 0 40px', fontSize: 10, color: '#9ca3af', lineHeight: 1.3 }}>
                                {cat.descripcion.length > 80 ? cat.descripcion.slice(0, 80) + '...' : cat.descripcion}
                            </p>
                        )}
                    </div>
                ))}

                {/* Custom paso button */}
                <div className="catalog-item"
                    onClick={() => onAddPaso(null)}
                    style={{
                        padding: '12px 16px', cursor: 'pointer', background: '#f9fafb',
                        borderTop: '1px solid #e5e7eb', marginTop: 4,
                    }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, color: '#6b7280', fontSize: 12 }}>
                        <span style={{ fontSize: 18 }}>✚</span>
                        Paso personalizado
                    </div>
                </div>

                {filtered.length === 0 && search && (
                    <div style={{ padding: '20px 16px', textAlign: 'center', color: '#9ca3af', fontSize: 12 }}>
                        Sin resultados para "{search}"
                    </div>
                )}
            </div>
        </div>
    );
}


/* ================================================================
   PASO DETAIL PANEL – Edit selected paso properties
   ================================================================ */
function PasoDetailPanel({ paso, allPasos, catalogo, onChange, onClose, onRemove }) {
    const [newDoc, setNewDoc] = useState('');
    const [showReuseFrom, setShowReuseFrom] = useState(false);
    const color = areaColor(paso.area);

    const addDoc = (nombre = null) => {
        const n = nombre || newDoc.trim();
        if (!n) return;
        onChange({ ...paso, documentos: [...paso.documentos, { nombre: n, tipo: 'pdf', obligatorio: true, depende_de_doc: null }] });
        setNewDoc('');
    };
    const rmDoc = i => { const d = [...paso.documentos]; d.splice(i, 1); onChange({ ...paso, documentos: d }); };
    const toggleDocObl = i => { const d = [...paso.documentos]; d[i] = { ...d[i], obligatorio: !d[i].obligatorio }; onChange({ ...paso, documentos: d }); };
    const editDocNombre = (i, val) => { const d = [...paso.documentos]; d[i] = { ...d[i], nombre: val }; onChange({ ...paso, documentos: d }); };
    const editDocTipo = (i, val) => { const d = [...paso.documentos]; d[i] = { ...d[i], tipo: val }; onChange({ ...paso, documentos: d }); };

    /* Collect checks from all OTHER pasos for reuse */
    const otherChecks = useMemo(() => {
        const checks = [];
        allPasos.forEach(p => {
            if (p._nodeId === paso._nodeId) return;
            (p.documentos || []).forEach(d => {
                if (!checks.find(c => c.nombre === d.nombre)) {
                    checks.push({ nombre: d.nombre, tipo: d.tipo, fromPaso: p.nombre, obligatorio: d.obligatorio });
                }
            });
        });
        return checks;
    }, [allPasos, paso._nodeId]);

    /* Collect checks from catalog pasos */
    const catalogChecks = useMemo(() => {
        const existing = new Set((paso.documentos || []).map(d => d.nombre.toLowerCase()));
        return otherChecks.filter(c => !existing.has(c.nombre.toLowerCase()));
    }, [otherChecks, paso.documentos]);

    return (
        <div className="detail-panel" style={{
            position: 'absolute', right: 0, top: 0, bottom: 0, width: 340, background: '#fff',
            borderLeft: '1px solid #e5e7eb', zIndex: 10, display: 'flex', flexDirection: 'column',
            boxShadow: '-2px 0 12px rgba(0,0,0,.04)',
        }}>
            {/* Header */}
            <div style={{
                padding: '14px 16px', borderBottom: '1px solid #e5e7eb',
                background: `linear-gradient(135deg, ${color}10, ${color}05)`,
            }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <h3 style={{ margin: 0, fontSize: 14, fontWeight: 700, color: '#1f2937' }}>Configurar Paso</h3>
                    <button onClick={onClose}
                        style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: 18, color: '#9ca3af', padding: 4 }}>
                        ✕
                    </button>
                </div>
            </div>

            {/* Content */}
            <div style={{ flex: 1, overflowY: 'auto', padding: '16px' }}>
                {/* Nombre */}
                <div style={{ marginBottom: 14 }}>
                    <label style={S.label}>Nombre del Paso</label>
                    <input type="text" value={paso.nombre}
                        onChange={e => onChange({ ...paso, nombre: e.target.value })}
                        style={S.input} placeholder="Nombre del paso..." />
                </div>

                {/* Area */}
                <div style={{ marginBottom: 14 }}>
                    <label style={S.label}>Área Responsable</label>
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                        {AREAS.map(a => (
                            <button key={a.v} onClick={() => onChange({ ...paso, area: a.v })}
                                style={{
                                    padding: '5px 10px', borderRadius: 20, fontSize: 11, fontWeight: 500,
                                    cursor: 'pointer', transition: 'all .15s',
                                    border: paso.area === a.v ? `2px solid ${a.c}` : '1px solid #e5e7eb',
                                    background: paso.area === a.v ? `${a.c}15` : '#fff',
                                    color: paso.area === a.v ? a.c : '#6b7280',
                                }}>
                                {a.l}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Dias + Obligatorio */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, marginBottom: 14 }}>
                    <div>
                        <label style={S.label}>Días Estimados</label>
                        <input type="number" value={paso.dias || ''} onChange={e => onChange({ ...paso, dias: parseInt(e.target.value) || null })}
                            style={S.input} placeholder="3" min="1" />
                    </div>
                    <div>
                        <label style={S.label}>Obligatorio</label>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 6 }}>
                            <div onClick={() => onChange({ ...paso, obligatorio: !paso.obligatorio })}
                                style={{
                                    width: 40, height: 22, borderRadius: 11, cursor: 'pointer', transition: 'all .2s',
                                    background: paso.obligatorio !== false ? '#059669' : '#d1d5db',
                                    padding: 2, display: 'flex', alignItems: paso.obligatorio !== false ? 'center' : 'center',
                                    justifyContent: paso.obligatorio !== false ? 'flex-end' : 'flex-start',
                                }}>
                                <div style={{
                                    width: 18, height: 18, borderRadius: '50%', background: '#fff',
                                    boxShadow: '0 1px 3px rgba(0,0,0,.2)', transition: 'all .2s',
                                }} />
                            </div>
                            <span style={{ fontSize: 12, color: '#4b5563' }}>{paso.obligatorio !== false ? 'Sí' : 'No'}</span>
                        </div>
                    </div>
                </div>

                {/* Instrucciones */}
                <div style={{ marginBottom: 14 }}>
                    <label style={S.label}>Instrucciones</label>
                    <textarea value={paso.instrucciones || ''} onChange={e => onChange({ ...paso, instrucciones: e.target.value })}
                        style={{ ...S.input, minHeight: 60, resize: 'vertical', fontFamily: 'inherit' }}
                        placeholder="Instrucciones para el ejecutor..." />
                </div>

                {/* ═══ DOCUMENTOS / CHECKS ═══ */}
                <div style={{ marginBottom: 14 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
                        <label style={{ ...S.label, marginBottom: 0 }}>📄 Checks / Documentos</label>
                        {catalogChecks.length > 0 && (
                            <button onClick={() => setShowReuseFrom(!showReuseFrom)}
                                style={{
                                    ...S.btnSm(showReuseFrom ? '#dbeafe' : '#f3f4f6', showReuseFrom ? '#2563eb' : '#6b7280'),
                                    fontSize: 10,
                                }}>
                                {showReuseFrom ? '✕ Cerrar' : '♻ Reutilizar'}
                            </button>
                        )}
                    </div>

                    {/* Reuse panel */}
                    {showReuseFrom && catalogChecks.length > 0 && (
                        <div style={{
                            background: '#eff6ff', borderRadius: 8, padding: '10px 12px', marginBottom: 10,
                            border: '1px solid #bfdbfe', animation: 'slideDown .2s ease-out',
                        }}>
                            <p style={{ fontSize: 10, fontWeight: 600, color: '#1d4ed8', marginBottom: 6 }}>
                                ♻ Checks de otros pasos (click para agregar)
                            </p>
                            <div style={{ maxHeight: 150, overflowY: 'auto' }}>
                                {catalogChecks.map((c, i) => (
                                    <div key={i} onClick={() => addDoc(c.nombre)}
                                        style={{
                                            display: 'flex', alignItems: 'center', gap: 6, padding: '4px 8px',
                                            borderRadius: 6, cursor: 'pointer', fontSize: 11, color: '#374151',
                                            marginBottom: 2, transition: 'all .1s',
                                        }}
                                        onMouseEnter={e => e.currentTarget.style.background = '#dbeafe'}
                                        onMouseLeave={e => e.currentTarget.style.background = 'transparent'}>
                                        <span style={{ color: '#2563eb' }}>+</span>
                                        <span style={{ flex: 1 }}>{c.nombre}</span>
                                        <span style={{ fontSize: 9, color: '#93c5fd' }}>de: {c.fromPaso || 'otro paso'}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Existing checks */}
                    {paso.documentos.map((doc, di) => (
                        <div key={di} style={{
                            display: 'flex', alignItems: 'center', gap: 6, padding: '6px 8px',
                            background: '#f9fafb', borderRadius: 8, marginBottom: 4, animation: 'slideDown .2s ease-out',
                        }}>
                            <div onClick={() => toggleDocObl(di)}
                                title={doc.obligatorio ? 'Obligatorio → Opcional' : 'Opcional → Obligatorio'}
                                style={{
                                    width: 18, height: 18, borderRadius: 4, display: 'flex', alignItems: 'center',
                                    justifyContent: 'center', cursor: 'pointer', flexShrink: 0, fontSize: 10,
                                    border: `2px solid ${doc.obligatorio ? '#2563eb' : '#d1d5db'}`,
                                    background: doc.obligatorio ? '#2563eb' : 'transparent', color: '#fff',
                                }}>
                                {doc.obligatorio && '✓'}
                            </div>
                            <input type="text" value={doc.nombre} onChange={e => editDocNombre(di, e.target.value)}
                                style={{ flex: 1, border: 'none', background: 'transparent', fontSize: 12, outline: 'none', padding: '2px 4px', minWidth: 0 }} />
                            <select value={doc.tipo || 'pdf'} onChange={e => editDocTipo(di, e.target.value)}
                                style={{ fontSize: 10, border: '1px solid #e5e7eb', borderRadius: 4, padding: '2px 4px', background: '#fff' }}>
                                <option value="pdf">PDF</option><option value="excel">Excel</option>
                                <option value="word">Word</option><option value="imagen">Imagen</option><option value="otro">Otro</option>
                            </select>
                            <button onClick={() => rmDoc(di)}
                                style={{ cursor: 'pointer', color: '#ef4444', background: 'none', border: 'none', fontSize: 14, padding: 0, flexShrink: 0 }}>
                                ✕
                            </button>
                        </div>
                    ))}

                    {/* Add new doc */}
                    <div style={{ display: 'flex', gap: 4, marginTop: 6 }}>
                        <input type="text" value={newDoc} onChange={e => setNewDoc(e.target.value)}
                            onKeyDown={e => e.key === 'Enter' && addDoc()}
                            style={{ ...S.input, flex: 1, fontSize: 12, borderStyle: 'dashed', padding: '6px 10px' }}
                            placeholder="+ Nombre del check/documento..." />
                        <button onClick={() => addDoc()} style={S.btnSm('#eff6ff', '#2563eb')}>+</button>
                    </div>
                </div>

                {/* Delete button */}
                <div style={{ borderTop: '1px solid #f3f4f6', paddingTop: 12, marginTop: 8 }}>
                    <button onClick={onRemove}
                        style={{ ...S.btn('#fef2f2', '#dc2626'), width: '100%', justifyContent: 'center' }}>
                        🗑 Eliminar Paso
                    </button>
                </div>
            </div>
        </div>
    );
}


/* ================================================================
   FLOW CANVAS – Main builder using React Flow
   ================================================================ */
const NODE_WIDTH = 200;
const NODE_HEIGHT = 180;
const NODE_SPACING_X = 230;
const NODE_SPACING_Y = 220;
const COLS_PER_ROW = 4;
const START_X = 120;
const START_Y = 60;

function buildNodesAndEdges(pasos, onDeleteNode) {
    const nodes = [];
    const edges = [];
    
    // Layout serpentina: izq->der, der->izq, izq->der...
    const getPosition = (index) => {
        const row = Math.floor(index / COLS_PER_ROW);
        const colInRow = index % COLS_PER_ROW;
        const isReverseRow = row % 2 === 1;
        const col = isReverseRow ? (COLS_PER_ROW - 1 - colInRow) : colInRow;
        
        return {
            x: START_X + col * NODE_SPACING_X,
            y: START_Y + row * NODE_SPACING_Y
        };
    };
    
    // Determina handles basado en posición relativa
    const getHandles = (sourceIdx, targetIdx) => {
        const sourceRow = Math.floor(sourceIdx / COLS_PER_ROW);
        const targetRow = Math.floor(targetIdx / COLS_PER_ROW);
        const isSourceRowReverse = sourceRow % 2 === 1;
        const isTargetRowReverse = targetRow % 2 === 1;
        
        if (sourceRow === targetRow) {
            // Misma fila - horizontal
            if (isSourceRowReverse) {
                return { sourceHandle: 'left-source', targetHandle: 'right-target' };
            } else {
                return { sourceHandle: 'right-source', targetHandle: 'left-target' };
            }
        } else {
            // Cambio de fila - sale por bottom del lado donde termina la fila
            // y entra por el lado donde empieza la nueva fila
            if (isSourceRowReverse) {
                // Fila reversa termina a la IZQUIERDA → sale por bottom-left
                // Nueva fila normal empieza a la IZQUIERDA → entra por left
                return { sourceHandle: 'bottom', targetHandle: 'left-target' };
            } else {
                // Fila normal termina a la DERECHA → sale por bottom-right
                // Nueva fila reversa empieza a la DERECHA → entra por right
                return { sourceHandle: 'bottom', targetHandle: 'right-target' };
            }
        }
    };

    // Start node - movible
    nodes.push({
        id: 'start',
        type: 'startNode',
        position: { x: START_X - 100, y: START_Y + 40 },
        data: {},
        draggable: true,
    });

    // Paso nodes
    pasos.forEach((paso, i) => {
        const nodeId = paso._nodeId || `paso-${i}`;
        const position = getPosition(i);
        
        nodes.push({
            id: nodeId,
            type: 'pasoNode',
            position,
            data: { ...paso, order: i, _nodeId: nodeId, onDelete: onDeleteNode },
            draggable: false,
        });
    });

    // End node - movible, posición después del último
    const lastIndex = pasos.length - 1;
    const lastRow = lastIndex >= 0 ? Math.floor(lastIndex / COLS_PER_ROW) : 0;
    const lastColInRow = lastIndex >= 0 ? lastIndex % COLS_PER_ROW : 0;
    const isLastRowReverse = lastRow % 2 === 1;
    
    let endPos;
    if (pasos.length === 0) {
        endPos = { x: START_X + 100, y: START_Y + 40 };
    } else {
        const lastPos = getPosition(lastIndex);
        if (isLastRowReverse) {
            // Fila reversa, el último está más a la izquierda
            endPos = { x: lastPos.x - 100, y: lastPos.y + 40 };
        } else {
            // Fila normal, el último está más a la derecha
            endPos = { x: lastPos.x + NODE_WIDTH + 30, y: lastPos.y + 40 };
        }
    }
    
    nodes.push({
        id: 'end',
        type: 'endNode',
        position: endPos,
        data: {},
        draggable: true,
    });

    // Edges
    pasos.forEach((paso, i) => {
        const nodeId = paso._nodeId || `paso-${i}`;
        const sourceId = i === 0 ? 'start' : (pasos[i - 1]._nodeId || `paso-${i - 1}`);
        const color = areaColor(paso.area);
        
        let sourceHandle = null;
        let targetHandle = null;
        
        if (i === 0) {
            sourceHandle = 'right';
            targetHandle = 'left-target';
        } else {
            const handles = getHandles(i - 1, i);
            sourceHandle = handles.sourceHandle;
            targetHandle = handles.targetHandle;
        }
        
        const edge = {
            id: `e-${sourceId}-${nodeId}`,
            source: sourceId,
            target: nodeId,
            type: 'smoothstep',
            animated: true,
            style: { stroke: color, strokeWidth: 2 },
            markerEnd: { type: MarkerType.ArrowClosed, color, width: 16, height: 16 },
        };
        if (sourceHandle) edge.sourceHandle = sourceHandle;
        if (targetHandle) edge.targetHandle = targetHandle;
        edges.push(edge);
    });

    // Edge al nodo final
    const lastPasoId = pasos.length > 0 ? (pasos[pasos.length - 1]._nodeId || `paso-${pasos.length - 1}`) : 'start';
    const endEdge = {
        id: `e-${lastPasoId}-end`,
        source: lastPasoId,
        target: 'end',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#9ca3af', strokeWidth: 2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#9ca3af', width: 16, height: 16 },
    };
    
    if (pasos.length > 0) {
        if (isLastRowReverse) {
            endEdge.sourceHandle = 'left-source';
            endEdge.targetHandle = 'right';
        } else {
            endEdge.sourceHandle = 'right-source';
            endEdge.targetHandle = 'left';
        }
    } else {
        endEdge.sourceHandle = 'right';
        endEdge.targetHandle = 'left';
    }
    edges.push(endEdge);

    return { nodes, edges };
}

function FlowCanvasInner({ catalogo, pasos, setPasos, selectedNodeId, setSelectedNodeId, toast }) {
    const [nodes, setNodes, onNodesChange] = useNodesState([]);
    const [edges, setEdges, onEdgesChange] = useEdgesState([]);
    const reactFlowInstance = useReactFlow();
    const wrapperRef = useRef(null);
    const idCounter = useRef(pasos.length + 1);

    // ── Delete a paso by nodeId (used by node button + keyboard) ──
    const deletePasoByNodeId = useCallback((nodeId) => {
        setPasos(prev => prev.filter(p => p._nodeId !== nodeId));
        if (selectedNodeId === nodeId) setSelectedNodeId(null);
    }, [setPasos, selectedNodeId, setSelectedNodeId]);

    // Rebuild nodes/edges when pasos change - layout serpentina fijo
    useEffect(() => {
        const { nodes: newNodes, edges: newEdges } = buildNodesAndEdges(pasos, deletePasoByNodeId);
        setNodes(newNodes);
        setEdges(newEdges);
        // Fit view when pasos change
        setTimeout(() => reactFlowInstance.fitView({ padding: 0.2, duration: 200 }), 50);
    }, [pasos, deletePasoByNodeId, reactFlowInstance]);

    // ── Keyboard: Delete / Backspace removes selected node ──
    useEffect(() => {
        const handleKeyDown = (e) => {
            if ((e.key === 'Delete' || e.key === 'Backspace') && selectedNodeId) {
                // Don't interfere with inputs/textareas
                const tag = e.target.tagName;
                if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
                e.preventDefault();
                deletePasoByNodeId(selectedNodeId);
            }
        };
        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [selectedNodeId, deletePasoByNodeId]);

    // Fit view on initial load
    useEffect(() => {
        const timer = setTimeout(() => {
            reactFlowInstance.fitView({ padding: 0.3 });
        }, 200);
        return () => clearTimeout(timer);
    }, [pasos.length === 0]);

    const onNodeClick = useCallback((_, node) => {
        if (node.type === 'pasoNode') {
            setSelectedNodeId(node.id);
        }
    }, []);

    const onPaneClick = useCallback(() => {
        setSelectedNodeId(null);
    }, []);

    // ── Drag from catalog / sidebar drops ──
    const onDragOver = useCallback(e => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }, []);

    const onDrop = useCallback(e => {
        e.preventDefault();
        const catData = e.dataTransfer.getData('application/reactflow-catalog');
        if (!catData) return;

        const cat = JSON.parse(catData);
        const newNodeId = `paso-new-${++idCounter.current}`;
        const newPaso = {
            _nodeId: newNodeId,
            catalogo_paso_id: cat.id || null,
            nombre: cat.nombre || 'Nuevo Paso',
            area: 'unidad_solicitante',
            dias: 3,
            instrucciones: '',
            obligatorio: true,
            documentos: [],
            depende_de: [],
        };

        setPasos(prev => [...prev, newPaso]);
        setSelectedNodeId(newNodeId);

        // Fit view after adding
        setTimeout(() => reactFlowInstance.fitView({ padding: 0.3, duration: 300 }), 100);
    }, [setPasos, reactFlowInstance]);

    // ── Add paso from catalog sidebar click ──
    const addPasoFromCatalog = useCallback(cat => {
        const newNodeId = `paso-new-${++idCounter.current}`;
        const newPaso = {
            _nodeId: newNodeId,
            catalogo_paso_id: cat?.id || null,
            nombre: cat?.nombre || 'Paso Personalizado',
            area: 'unidad_solicitante',
            dias: 3,
            instrucciones: '',
            obligatorio: true,
            documentos: [],
            depende_de: [],
        };

        setPasos(prev => [...prev, newPaso]);
        setSelectedNodeId(newNodeId);

        setTimeout(() => reactFlowInstance.fitView({ padding: 0.3, duration: 300 }), 100);
    }, [setPasos, reactFlowInstance]);

    // ── Node drag end – no-op since layout is fixed serpentine ──
    const onNodeDragStop = useCallback((_, node) => {
        // En layout serpentina, los nodos no se mueven libremente
        // El orden se mantiene por el array de pasos
    }, []);

    // Selected paso for detail panel
    const selectedPaso = selectedNodeId ? pasos.find(p => p._nodeId === selectedNodeId) : null;

    const updateSelectedPaso = useCallback(updatedPaso => {
        setPasos(prev => prev.map(p => p._nodeId === selectedNodeId ? { ...updatedPaso, _nodeId: selectedNodeId } : p));
    }, [selectedNodeId, setPasos]);

    const removeSelectedPaso = useCallback(() => {
        setPasos(prev => prev.filter(p => p._nodeId !== selectedNodeId));
        setSelectedNodeId(null);
    }, [selectedNodeId, setPasos]);

    return (
        <div ref={wrapperRef} className="flow-canvas-wrapper"
            style={{ width: '100%', height: '100%', position: 'relative' }}>

            <CatalogSidebar catalogo={catalogo} onAddPaso={addPasoFromCatalog} />

            <div style={{ position: 'absolute', left: 260, top: 0, right: selectedPaso ? 340 : 0, bottom: 0, transition: 'right .25s ease' }}>
                <ReactFlow
                    nodes={nodes}
                    edges={edges}
                    onNodesChange={onNodesChange}
                    onEdgesChange={onEdgesChange}
                    onNodeClick={onNodeClick}
                    onPaneClick={onPaneClick}
                    onNodeDragStop={onNodeDragStop}
                    onDragOver={onDragOver}
                    onDrop={onDrop}
                    nodeTypes={nodeTypes}
                    fitView
                    fitViewOptions={{ padding: 0.3 }}
                    defaultEdgeOptions={{ animated: true }}
                    proOptions={{ hideAttribution: true }}
                    snapToGrid
                    snapGrid={[15, 15]}
                    minZoom={0.05}
                    maxZoom={2}>
                    <Controls position="bottom-left" />
                    <MiniMap
                        position="bottom-right"
                        nodeColor={n => {
                            if (n.type === 'startNode') return '#10B981';
                            if (n.type === 'endNode') return '#EF4444';
                            return areaColor(n.data?.area);
                        }}
                        maskColor="rgba(0,0,0,.08)"
                        style={{ height: 100, width: 150 }}
                    />
                    <Background variant="dots" gap={20} size={1} color="#e5e7eb" />

                    {/* Empty state */}
                    {pasos.length === 0 && (
                        <Panel position="top-center">
                            <div style={{
                                textAlign: 'center', padding: '40px 30px', background: '#fff',
                                borderRadius: 16, border: '2px dashed #d1d5db', boxShadow: '0 4px 20px rgba(0,0,0,.06)',
                                animation: 'nodeAppear .4s ease-out', marginTop: 80,
                            }}>
                                <div style={{ fontSize: 48, marginBottom: 12 }}>🎨</div>
                                <h3 style={{ margin: 0, fontSize: 16, fontWeight: 700, color: '#374151' }}>
                                    Comience a construir su flujo
                                </h3>
                                <p style={{ margin: '8px 0 0', fontSize: 13, color: '#9ca3af', maxWidth: 300 }}>
                                    Arrastre pasos del catálogo (izquierda) al canvas, o haga click en un paso del catálogo para agregarlo.
                                </p>
                            </div>
                        </Panel>
                    )}
                </ReactFlow>
            </div>

            {/* Detail panel */}
            {selectedPaso && (
                <PasoDetailPanel
                    paso={selectedPaso}
                    allPasos={pasos}
                    catalogo={catalogo}
                    onChange={updateSelectedPaso}
                    onClose={() => setSelectedNodeId(null)}
                    onRemove={removeSelectedPaso}
                />
            )}
        </div>
    );
}


/* ================================================================
   BUILDER – Top-level builder wrapping FlowCanvas
   ================================================================ */
function Builder({ flujoEdit, catalogo, secretariaId, onDone, onCancel, toast }) {
    const [codigo, setCodigo] = useState(flujoEdit?.codigo || '');
    const [nombre, setNombre] = useState(flujoEdit?.nombre || '');
    const [desc, setDesc] = useState(flujoEdit?.descripcion || '');
    const [pasos, setPasos] = useState([]);
    const [saving, setSaving] = useState(false);
    const [loadingPasos, setLoadingPasos] = useState(false);
    const [selectedNodeId, setSelectedNodeId] = useState(null);
    const [showInfo, setShowInfo] = useState(!flujoEdit);
    const idCounter = useRef(0);

    // Load existing pasos
    useEffect(() => {
        if (!flujoEdit?.id) return;
        setLoadingPasos(true);
        apiFetch(`${API}/flujos/${flujoEdit.id}/pasos`)
            .then(d => {
                setPasos((d.pasos || []).map((p, i) => {
                    const nid = `paso-loaded-${++idCounter.current}`;
                    return {
                        _nodeId: nid,
                        catalogo_paso_id: p.catalogo_paso_id,
                        nombre: p.nombre_personalizado || p.catalogo_paso?.nombre || `Paso ${i + 1}`,
                        area: p.area_responsable_default || 'unidad_solicitante',
                        dias: p.dias_estimados,
                        instrucciones: p.instrucciones || '',
                        obligatorio: p.es_obligatorio !== false,
                        documentos: (p.documentos || []).map(doc => ({
                            nombre: doc.nombre, tipo: doc.tipo_archivo || 'pdf',
                            obligatorio: doc.es_obligatorio !== false,
                            depende_de_doc: doc.depende_de_doc || null,
                        })),
                        depende_de: [],
                    };
                }));
            })
            .catch(() => toast('Error cargando pasos', 'error'))
            .finally(() => setLoadingPasos(false));
    }, [flujoEdit]);

    /* SAVE */
    const handleSave = async () => {
        if (!nombre.trim()) return toast('El nombre del flujo es requerido.', 'warning');
        if (!codigo.trim()) return toast('El código del flujo es requerido.', 'warning');
        if (pasos.length === 0) return toast('Agregue al menos un paso.', 'warning');
        for (let i = 0; i < pasos.length; i++) {
            if (!pasos[i].nombre.trim()) return toast(`El paso #${i + 1} no tiene nombre.`, 'warning');
        }
        setSaving(true);
        try {
            await apiFetch(`${API}/flujos/guardar-completo`, {
                method: 'POST',
                body: JSON.stringify({
                    flujo_id: flujoEdit?.id || null, codigo, nombre,
                    descripcion: desc || null, secretaria_id: secretariaId,
                    pasos: pasos.map(p => ({
                        catalogo_paso_id: p.catalogo_paso_id, nombre: p.nombre,
                        area_responsable: p.area, dias_estimados: p.dias,
                        instrucciones: p.instrucciones || null,
                        obligatorio: p.obligatorio !== false,
                        documentos: p.documentos.map(d => ({
                            nombre: d.nombre, tipo: d.tipo || 'pdf',
                            obligatorio: d.obligatorio !== false,
                            depende_de_doc: d.depende_de_doc || null,
                        })),
                        depende_de: p.depende_de || [],
                    })),
                }),
            });
            toast('✅ Flujo guardado exitosamente.');
            onDone();
        } catch (e) { toast(e.message, 'error'); }
        setSaving(false);
    };

    return (
        <div style={{ height: '100vh', display: 'flex', flexDirection: 'column' }}>
            {/* ═══ TOP BAR ═══ */}
            <div style={{
                background: 'linear-gradient(135deg, #1e40af, #4338ca)', color: '#fff',
                padding: '10px 20px', flexShrink: 0, zIndex: 20,
            }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        <button onClick={onCancel}
                            style={{ ...S.btnSm('rgba(255,255,255,.15)', '#fff'), borderRadius: 8 }}>
                            ← Volver
                        </button>
                        <div>
                            <h2 style={{ margin: 0, fontSize: 16, fontWeight: 700 }}>
                                {flujoEdit ? '✏️ Editar Flujo' : '➕ Nuevo Flujo'}
                            </h2>
                            <p style={{ margin: 0, fontSize: 11, color: '#bfdbfe' }}>
                                {nombre || 'Sin nombre'} · {pasos.length} paso{pasos.length !== 1 ? 's' : ''}
                            </p>
                        </div>
                    </div>
                    <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                        <button onClick={() => setShowInfo(!showInfo)}
                            style={S.btnSm('rgba(255,255,255,.15)', '#fff')}>
                            {showInfo ? '▲ Info' : '▼ Info'}
                        </button>
                        <button onClick={onCancel} style={S.btn('rgba(255,255,255,.15)')}>Cancelar</button>
                        <button onClick={handleSave} disabled={saving}
                            style={{ ...S.btn('#fff', '#1d4ed8'), opacity: saving ? 0.6 : 1 }}>
                            {saving ? '⏳ Guardando...' : '💾 Guardar Flujo'}
                        </button>
                    </div>
                </div>

                {/* Collapsible info form */}
                {showInfo && (
                    <div style={{
                        marginTop: 12, padding: '14px 16px', background: 'rgba(255,255,255,.1)',
                        borderRadius: 12, animation: 'slideDown .2s ease-out',
                    }}>
                        <div style={{ display: 'grid', gridTemplateColumns: '200px 1fr 1fr', gap: 12 }}>
                            <div>
                                <label style={{ ...S.label, color: '#93c5fd' }}>Código</label>
                                <input type="text" value={codigo}
                                    onChange={e => setCodigo(e.target.value.toUpperCase().replace(/[^A-Z0-9_]/g, ''))}
                                    disabled={!!flujoEdit?.id}
                                    style={{ ...S.input, background: flujoEdit?.id ? 'rgba(255,255,255,.05)' : 'rgba(255,255,255,.15)', border: '1px solid rgba(255,255,255,.2)', color: '#fff' }}
                                    placeholder="CD_PN_CULTURA" />
                            </div>
                            <div>
                                <label style={{ ...S.label, color: '#93c5fd' }}>Nombre del Flujo</label>
                                <input type="text" value={nombre} onChange={e => setNombre(e.target.value)}
                                    style={{ ...S.input, background: 'rgba(255,255,255,.15)', border: '1px solid rgba(255,255,255,.2)', color: '#fff' }}
                                    placeholder="Mínima Cuantía – Sec. Cultura" />
                            </div>
                            <div>
                                <label style={{ ...S.label, color: '#93c5fd' }}>Descripción <span style={{ fontWeight: 400 }}>(opcional)</span></label>
                                <input type="text" value={desc} onChange={e => setDesc(e.target.value)}
                                    style={{ ...S.input, background: 'rgba(255,255,255,.15)', border: '1px solid rgba(255,255,255,.2)', color: '#fff' }}
                                    placeholder="Descripción breve..." />
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* ═══ CANVAS AREA ═══ */}
            <div style={{ flex: 1, position: 'relative', background: '#f8fafc' }}>
                {loadingPasos ? (
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100%' }}>
                        <div style={{ textAlign: 'center', color: '#6b7280' }}>
                            <div style={{ fontSize: 32, animation: 'spin 1s linear infinite', display: 'inline-block' }}>⏳</div>
                            <p style={{ marginTop: 12 }}>Cargando pasos del flujo...</p>
                        </div>
                    </div>
                ) : (
                    <ReactFlowProvider>
                        <FlowCanvasInner
                            catalogo={catalogo}
                            pasos={pasos}
                            setPasos={setPasos}
                            selectedNodeId={selectedNodeId}
                            setSelectedNodeId={setSelectedNodeId}
                            toast={toast}
                        />
                    </ReactFlowProvider>
                )}
            </div>
        </div>
    );
}


/* ================================================================
   LISTA DE FLUJOS – Card grid
   ================================================================ */
function FlujosList({ flujos, loading, onView, onEdit, onNew, onDelete, toast }) {
    const [hoveredId, setHoveredId] = useState(null);

    const handleDelete = async (f) => {
        if (!confirm(`¿Eliminar el flujo "${f.nombre}"?`)) return;
        try {
            await apiFetch(`${API}/flujos/${f.id}`, { method: 'DELETE' });
            toast('Flujo eliminado.'); onDelete();
        } catch (e) { toast(e.message, 'error'); }
    };

    return (
        <div style={{ padding: '24px 30px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
                <div>
                    <h3 style={{ margin: 0, fontSize: 20, fontWeight: 700, color: '#1f2937' }}>🏗️ Flujos Configurados</h3>
                    <p style={{ margin: '4px 0 0', fontSize: 13, color: '#9ca3af' }}>{flujos.length} flujo{flujos.length !== 1 && 's'} · Seleccione uno para ver o editar</p>
                </div>
                <button onClick={onNew} style={{
                    ...S.btn('#2563eb'), padding: '10px 24px', fontSize: 14,
                    boxShadow: '0 2px 8px rgba(37,99,235,.3)',
                }}>
                    + Crear Nuevo Flujo
                </button>
            </div>

            {loading && (
                <div style={{ textAlign: 'center', padding: '80px 0', color: '#6b7280' }}>
                    <div style={{ fontSize: 32, animation: 'spin 1s linear infinite', display: 'inline-block' }}>⏳</div>
                    <p style={{ marginTop: 12 }}>Cargando flujos...</p>
                </div>
            )}

            {!loading && flujos.length === 0 && (
                <div style={{
                    textAlign: 'center', padding: '80px 0', background: '#fff',
                    borderRadius: 20, border: '2px dashed #e5e7eb',
                }}>
                    <div style={{ fontSize: 56, marginBottom: 16 }}>🎨</div>
                    <h3 style={{ color: '#374151', fontWeight: 700, fontSize: 18 }}>Sin flujos para esta Secretaría</h3>
                    <p style={{ color: '#9ca3af', marginTop: 8, maxWidth: 400, margin: '8px auto 0' }}>
                        Cree su primer flujo arrastrando pasos del catálogo al canvas interactivo.
                    </p>
                    <button onClick={onNew} style={{
                        ...S.btn('#2563eb'), marginTop: 24, padding: '12px 28px', fontSize: 14,
                    }}>
                        + Crear Primer Flujo
                    </button>
                </div>
            )}

            {!loading && flujos.length > 0 && (
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))', gap: 20 }}>
                    {flujos.map(f => {
                        const isHovered = hoveredId === f.id;
                        return (
                            <div key={f.id}
                                onMouseEnter={() => setHoveredId(f.id)}
                                onMouseLeave={() => setHoveredId(null)}
                                style={{
                                    ...S.card, marginBottom: 0, cursor: 'pointer',
                                    transform: isHovered ? 'translateY(-3px)' : 'none',
                                    boxShadow: isHovered ? '0 8px 24px rgba(0,0,0,.1)' : '0 1px 4px rgba(0,0,0,.04)',
                                    border: isHovered ? '1px solid #93c5fd' : '1px solid #e5e7eb',
                                }}
                                onClick={() => onView(f)}>
                                {/* Color bar */}
                                <div style={{ height: 3, background: 'linear-gradient(90deg, #3B82F6, #8B5CF6, #EC4899)' }} />
                                <div style={{ padding: 20 }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                                        <div style={{ minWidth: 0, flex: 1 }}>
                                            <h4 style={{ margin: 0, fontSize: 15, fontWeight: 700, color: '#1f2937' }}>{f.nombre}</h4>
                                            <p style={{ margin: '2px 0 0', fontSize: 11, color: '#9ca3af' }}>{f.codigo}</p>
                                        </div>
                                        <span style={{ ...S.badge('#dcfce7'), color: '#15803d', marginLeft: 8, flexShrink: 0 }}>
                                            v{f.version_activa?.numero_version || '1'}
                                        </span>
                                    </div>
                                    {f.descripcion && <p style={{ margin: '10px 0 0', fontSize: 12, color: '#6b7280', lineHeight: 1.4 }}>{f.descripcion}</p>}
                                </div>
                                <div style={{
                                    borderTop: '1px solid #f3f4f6', padding: '10px 20px', background: '#f9fafb',
                                    display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                                }}>
                                    <span style={{ fontSize: 11, color: '#9ca3af' }}>
                                        {f.version_activa?.pasos_count ?? f.version_activa?.pasos?.length ?? 0} pasos
                                    </span>
                                    <div style={{ display: 'flex', gap: 8 }} onClick={e => e.stopPropagation()}>
                                        <button onClick={() => onView(f)} style={{ ...S.btnSm('transparent', '#2563eb'), padding: '4px 8px' }}>👁 Ver</button>
                                        <button onClick={() => onEdit(f)} style={{ ...S.btnSm('transparent', '#4f46e5'), padding: '4px 8px' }}>✏️ Editar</button>
                                        <button onClick={() => handleDelete(f)} style={{ ...S.btnSm('transparent', '#dc2626'), padding: '4px 8px' }}>🗑</button>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}


/* ================================================================
   DETAIL VIEW – Read-only React Flow visualization
   ================================================================ */
function DetailViewCanvas({ pasos }) {
    const mapped = pasos.map((p, i) => ({
        _nodeId: `view-${i}`,
        nombre: p.nombre_personalizado || p.catalogo_paso?.nombre || `Paso ${p.orden + 1}`,
        area: p.area_responsable_default || 'unidad_solicitante',
        dias: p.dias_estimados,
        obligatorio: p.es_obligatorio !== false,
        documentos: (p.documentos || []).map(d => ({
            nombre: d.nombre, tipo: d.tipo_archivo || 'pdf',
            obligatorio: d.es_obligatorio !== false,
        })),
    }));

    const { nodes, edges } = buildNodesAndEdges(mapped);

    return (
        <div style={{ height: 500, borderRadius: 16, overflow: 'hidden', border: '1px solid #e5e7eb' }}>
            <ReactFlowProvider>
                <ReactFlow
                    nodes={nodes}
                    edges={edges}
                    nodeTypes={nodeTypes}
                    fitView
                    fitViewOptions={{ padding: 0.4 }}
                    nodesDraggable={false}
                    nodesConnectable={false}
                    elementsSelectable={false}
                    proOptions={{ hideAttribution: true }}
                    defaultEdgeOptions={{ animated: true }}>
                    <Controls showInteractive={false} position="bottom-left" />
                    <Background variant="dots" gap={20} size={1} color="#e5e7eb" />
                </ReactFlow>
            </ReactFlowProvider>
        </div>
    );
}

function DetailView({ flujo, onBack, onEdit }) {
    const [pasos, setPasos] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        apiFetch(`${API}/flujos/${flujo.id}/pasos`)
            .then(d => setPasos(d.pasos || []))
            .finally(() => setLoading(false));
    }, [flujo]);

    return (
        <div style={{ padding: '24px 30px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 20 }}>
                <button onClick={onBack} style={{ ...S.btnSm('#f3f4f6', '#6b7280'), borderRadius: 8 }}>← Volver</button>
                <div style={{ flex: 1, minWidth: 0 }}>
                    <h3 style={{ margin: 0, fontSize: 20, fontWeight: 700, color: '#1f2937' }}>{flujo.nombre}</h3>
                    <p style={{ margin: '2px 0 0', fontSize: 12, color: '#9ca3af' }}>
                        {flujo.codigo} · v{flujo.version_activa?.numero_version || '?'} · {pasos.length} pasos
                    </p>
                </div>
                <button onClick={() => onEdit(flujo)} style={S.btn('#4f46e5')}>✏️ Editar en Canvas</button>
            </div>

            {loading ? (
                <div style={{ textAlign: 'center', padding: '60px 0', color: '#6b7280' }}>
                    <div style={{ fontSize: 32, animation: 'spin 1s linear infinite', display: 'inline-block' }}>⏳</div>
                    <p style={{ marginTop: 12 }}>Cargando flujo visual...</p>
                </div>
            ) : pasos.length > 0 ? (
                <DetailViewCanvas pasos={pasos} />
            ) : (
                <div style={{ textAlign: 'center', padding: '48px 0', color: '#9ca3af' }}>
                    <p style={{ fontSize: 16 }}>Sin pasos configurados</p>
                    <button onClick={() => onEdit(flujo)}
                        style={{ marginTop: 12, cursor: 'pointer', color: '#2563eb', background: 'none', border: 'none', fontSize: 13 }}>
                        Editar flujo para agregar pasos
                    </button>
                </div>
            )}

            {/* Step list below the canvas */}
            {!loading && pasos.length > 0 && (
                <div style={{ marginTop: 24 }}>
                    <h4 style={{ fontSize: 13, fontWeight: 700, color: '#6b7280', textTransform: 'uppercase', marginBottom: 12 }}>
                        Lista detallada de pasos
                    </h4>
                    <div style={{ display: 'grid', gap: 8 }}>
                        {pasos.map((p, i) => {
                            const color = areaColor(p.area_responsable_default);
                            const nom = p.nombre_personalizado || p.catalogo_paso?.nombre || `Paso ${p.orden + 1}`;
                            return (
                                <div key={p.id} style={{
                                    ...S.card, marginBottom: 0, display: 'flex', alignItems: 'center', gap: 14, padding: '14px 18px',
                                }}>
                                    <div style={{
                                        width: 36, height: 36, borderRadius: 10, background: color, color: '#fff',
                                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                                        fontWeight: 700, fontSize: 14, flexShrink: 0,
                                    }}>
                                        {i + 1}
                                    </div>
                                    <div style={{ flex: 1, minWidth: 0 }}>
                                        <div style={{ fontWeight: 600, fontSize: 14, color: '#1f2937' }}>{nom}</div>
                                        <div style={{ display: 'flex', gap: 6, marginTop: 3, flexWrap: 'wrap', alignItems: 'center' }}>
                                            <span style={S.badge(color)}>{areaLabel(p.area_responsable_default)}</span>
                                            {p.dias_estimados && <span style={{ fontSize: 10, color: '#9ca3af' }}>{p.dias_estimados} días</span>}
                                            {(p.documentos?.length > 0) && (
                                                <span style={{ ...S.badge('#dbeafe'), color: '#2563eb' }}>
                                                    {p.documentos.length} check{p.documentos.length > 1 ? 's' : ''}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    {!p.es_obligatorio && <span style={{ ...S.badge('#fef3c7'), color: '#b45309' }}>Opcional</span>}
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
}


/* ================================================================
   COMPONENTE PRINCIPAL
   ================================================================ */
export default function WorkflowApp() {
    const [user, setUser] = useState(null);
    const [error, setError] = useState(null);
    const { toasts, show: toast } = useToast();
    const [secretarias, setSecretarias] = useState([]);
    const [secId, setSecId] = useState(null);
    const [flujos, setFlujos] = useState([]);
    const [catalogo, setCatalogo] = useState([]);
    const [loading, setLoading] = useState(true);
    const [view, setView] = useState('list');   // 'list' | 'view' | 'builder'
    const [activeFlujo, setActive] = useState(null);

    const esAdmin = user?.roles?.some(r => ['admin', 'admin_general', 'admin_unidad'].includes(typeof r === 'string' ? r : r.name));
    const effectiveSecId = secId || user?.secretaria?.id || user?.secretaria_id || null;

    // Inject CSS animations
    useEffect(() => { injectStyles(); }, []);

    useEffect(() => {
        apiFetch('/api/auth/me').then(d => setUser(d.user || d)).catch(() => setError('No autenticado. Inicie sesión.'));
    }, []);

    useEffect(() => {
        if (!user) return;
        if (esAdmin) {
            apiFetch('/api/secretarias').then(d => {
                const list = Array.isArray(d.secretarias || d.data) ? (d.secretarias || d.data) : [];
                setSecretarias(list);
                if (!secId && !user?.secretaria?.id && !user?.secretaria_id && list.length > 0) {
                    const plan = list.find(s => /planeaci/i.test(s.nombre));
                    setSecId(plan ? plan.id : list[0].id);
                }
            }).catch(() => {});
        }
        apiFetch(`${API}/catalogo-pasos`).then(d => setCatalogo(d.catalogo_pasos || [])).catch(() => {});
    }, [user]);

    const loadFlujos = useCallback(() => {
        if (!effectiveSecId) { setLoading(false); return; }
        setLoading(true);
        apiFetch(`${API}/secretarias/${effectiveSecId}/flujos`)
            .then(d => setFlujos(d.flujos || []))
            .catch(e => toast(e.message, 'error'))
            .finally(() => setLoading(false));
    }, [effectiveSecId]);

    useEffect(() => { loadFlujos(); setView('list'); setActive(null); }, [loadFlujos]);

    if (!user && !error) return (
        <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '50vh' }}>
            <div style={{ textAlign: 'center', color: '#6b7280' }}>
                <div style={{ fontSize: 32, animation: 'spin 1s linear infinite', display: 'inline-block' }}>⏳</div>
                <p style={{ marginTop: 12 }}>Cargando...</p>
            </div>
        </div>
    );

    if (error) return (
        <div style={{ maxWidth: 400, margin: '60px auto', padding: 24, background: '#fef2f2', border: '1px solid #fecaca', borderRadius: 16, textAlign: 'center' }}>
            <div style={{ fontSize: 36, marginBottom: 8 }}>🔒</div>
            <h2 style={{ color: '#991b1b', fontWeight: 700 }}>Error</h2>
            <p style={{ color: '#dc2626', fontSize: 13 }}>{error}</p>
            <a href="/login" style={{ display: 'inline-block', marginTop: 12, padding: '8px 20px', background: '#dc2626', color: '#fff', borderRadius: 8, textDecoration: 'none', fontSize: 13 }}>Ir al Login</a>
        </div>
    );

    if (view === 'builder') return (
        <>
            <Builder flujoEdit={activeFlujo} catalogo={catalogo} secretariaId={effectiveSecId}
                onDone={() => { loadFlujos(); setView('list'); setActive(null); }}
                onCancel={() => setView(activeFlujo ? 'view' : 'list')} toast={toast} />
            <Toasts toasts={toasts} />
        </>
    );

    return (
        <div style={{ minHeight: '50vh', background: '#f8fafc' }}>
            {/* Top bar */}
            <div style={{
                background: '#fff', borderBottom: '1px solid #e5e7eb', padding: '14px 24px',
                display: 'flex', justifyContent: 'space-between', alignItems: 'center',
            }}>
                <div>
                    <h1 style={{ margin: 0, fontSize: 18, fontWeight: 700, color: '#1f2937' }}>
                        🔄 Motor de Flujos
                    </h1>
                    <p style={{ margin: 0, fontSize: 11, color: '#9ca3af' }}>
                        Constructor visual de flujos de contratación por secretaría
                    </p>
                </div>
                {esAdmin && secretarias.length > 0 && (
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        <label style={{ fontSize: 12, fontWeight: 500, color: '#6b7280' }}>Secretaría:</label>
                        <select value={effectiveSecId || ''} onChange={e => setSecId(parseInt(e.target.value))}
                            style={{ ...S.select, minWidth: 280 }}>
                            <option value="" disabled>Seleccionar...</option>
                            {secretarias.map(s => <option key={s.id} value={s.id}>{s.nombre}</option>)}
                        </select>
                    </div>
                )}
            </div>

            {!effectiveSecId ? (
                <div style={{ textAlign: 'center', padding: '80px 0' }}>
                    <div style={{ fontSize: 56, marginBottom: 16 }}>🏛️</div>
                    <p style={{ fontSize: 18, fontWeight: 600, color: '#374151' }}>Seleccione una Secretaría</p>
                    <p style={{ fontSize: 13, color: '#9ca3af', marginTop: 4 }}>Use el selector de arriba para comenzar</p>
                </div>
            ) : view === 'list' ? (
                <FlujosList flujos={flujos} loading={loading}
                    onView={f => { setActive(f); setView('view'); }}
                    onEdit={f => { setActive(f); setView('builder'); }}
                    onNew={() => { setActive(null); setView('builder'); }}
                    onDelete={loadFlujos} toast={toast} />
            ) : view === 'view' && activeFlujo ? (
                <DetailView flujo={activeFlujo}
                    onBack={() => { setView('list'); setActive(null); }}
                    onEdit={f => { setActive(f); setView('builder'); }} />
            ) : null}

            <Toasts toasts={toasts} />
        </div>
    );
}
