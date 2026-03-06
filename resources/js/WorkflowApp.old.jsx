import React, { useState, useEffect, useCallback, useRef } from 'react';

/*  WorkflowApp – Constructor Visual de Flujos
    Endpoint save: POST /api/motor-flujos/flujos/guardar-completo
    Layout: SINGLE COLUMN (avoids flexbox sidebar collapse issues) */

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
            <div key={t.id} style={{ marginBottom: 8, padding: '10px 18px', borderRadius: 8, color: '#fff', fontSize: 13, fontWeight: 500, boxShadow: '0 4px 12px rgba(0,0,0,.15)',
                background: t.type === 'error' ? '#dc2626' : t.type === 'warning' ? '#d97706' : '#059669' }}>
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

/* ── CSS-in-JS styles (inline) so they ALWAYS work ── */
const S = {
    card: { background:'#fff', border:'1px solid #e5e7eb', borderRadius:12, marginBottom:16, overflow:'hidden', transition:'box-shadow .15s' },
    cardOpen: { border:'2px solid #93c5fd', boxShadow:'0 4px 16px rgba(59,130,246,.12)' },
    header: { display:'flex', alignItems:'center', gap:10, padding:'12px 16px', cursor:'pointer', userSelect:'none' },
    badge: (bg) => ({ display:'inline-block', fontSize:10, fontWeight:600, padding:'2px 8px', borderRadius:99, color:'#fff', background:bg }),
    numBadge: (bg) => ({ width:32, height:32, borderRadius:8, display:'flex', alignItems:'center', justifyContent:'center', color:'#fff', fontWeight:700, fontSize:13, background:bg, flexShrink:0 }),
    btn: (bg, color='#fff') => ({ padding:'8px 18px', background:bg, color, border:'none', borderRadius:8, fontSize:13, fontWeight:600, cursor:'pointer', display:'inline-flex', alignItems:'center', gap:6 }),
    btnSm: (bg, color='#fff') => ({ padding:'5px 12px', background:bg, color, border:'none', borderRadius:6, fontSize:12, fontWeight:500, cursor:'pointer', display:'inline-flex', alignItems:'center', gap:4 }),
    input: { width:'100%', padding:'8px 12px', border:'1px solid #d1d5db', borderRadius:8, fontSize:13, outline:'none', boxSizing:'border-box' },
    select: { padding:'8px 12px', border:'1px solid #d1d5db', borderRadius:8, fontSize:13, outline:'none', boxSizing:'border-box', background:'#fff' },
    label: { display:'block', fontSize:11, fontWeight:600, color:'#6b7280', marginBottom:4, textTransform:'uppercase' },
    section: { padding:'20px 24px' },
    grid2: { display:'grid', gridTemplateColumns:'1fr 1fr', gap:16 },
    docRow: { display:'flex', alignItems:'center', gap:8, padding:'6px 10px', background:'#f9fafb', borderRadius:8, marginBottom:6 },
    check: (on) => ({ width:20, height:20, borderRadius:4, border:`2px solid ${on ? '#2563eb':'#d1d5db'}`, background:on?'#2563eb':'transparent', color:'#fff', display:'flex', alignItems:'center', justifyContent:'center', cursor:'pointer', flexShrink:0, fontSize:12 }),
    depBtn: (on) => ({ padding:'4px 12px', borderRadius:99, fontSize:11, fontWeight:500, cursor:'pointer', border:'none', background:on?'#ede9fe':'#f3f4f6', color:on?'#6d28d9':'#6b7280', outline:on?'2px solid #c4b5fd':'none' }),
    connector: { display:'flex', justifyContent:'center', margin:'-6px 0' },
    connDot: { width:8, height:8, borderRadius:'50%', background:'#9ca3af' },
    connLine: { width:2, height:10, background:'#d1d5db' },
};


/* ================================================================
   PasoCard – Tarjeta expandible por paso
   ================================================================ */
function PasoCard({ paso, idx, total, allPasos, onChange, onRemove, onMove }) {
    const [open, setOpen] = useState(false);
    const [newDoc, setNewDoc] = useState('');
    const color = areaColor(paso.area);

    const addDoc = () => {
        if (!newDoc.trim()) return;
        onChange({ ...paso, documentos: [...paso.documentos, { nombre: newDoc.trim(), tipo: 'pdf', obligatorio: true, depende_de_doc: null }] });
        setNewDoc('');
    };
    const rmDoc = i => { const d = [...paso.documentos]; d.splice(i, 1); onChange({ ...paso, documentos: d }); };
    const toggleDocObl = i => { const d = [...paso.documentos]; d[i] = { ...d[i], obligatorio: !d[i].obligatorio }; onChange({ ...paso, documentos: d }); };
    const editDocNombre = (i, val) => { const d = [...paso.documentos]; d[i] = { ...d[i], nombre: val }; onChange({ ...paso, documentos: d }); };
    const editDocTipo = (i, val) => { const d = [...paso.documentos]; d[i] = { ...d[i], tipo: val }; onChange({ ...paso, documentos: d }); };
    const setDocDep = (i, val) => { const d = [...paso.documentos]; d[i] = { ...d[i], depende_de_doc: val || null }; onChange({ ...paso, documentos: d }); };
    const toggleDep = depIdx => {
        const deps = paso.depende_de || [];
        onChange({ ...paso, depende_de: deps.includes(depIdx) ? deps.filter(d => d !== depIdx) : [...deps, depIdx] });
    };

    return (
        <div style={{ marginBottom: 12 }}>
            {/* Conector visual */}
            {idx > 0 && (
                <div style={S.connector}>
                    <div style={{ display:'flex', flexDirection:'column', alignItems:'center' }}>
                        <div style={S.connLine} /><div style={S.connDot} /><div style={S.connLine} />
                    </div>
                </div>
            )}

            <div style={{ ...S.card, ...(open ? S.cardOpen : {}), marginBottom: 0 }}>
                {/* Header */}
                <div style={S.header} onClick={() => setOpen(!open)}>
                    <div style={{ cursor:'grab', color:'#d1d5db', fontSize:18, lineHeight:1 }} title="Arrastrar">☰</div>
                    <div style={S.numBadge(color)}>{idx + 1}</div>
                    <div style={{ flex:1, minWidth:0 }}>
                        <input type="text" value={paso.nombre}
                            onClick={e => e.stopPropagation()}
                            onChange={e => onChange({ ...paso, nombre: e.target.value })}
                            placeholder="Nombre del paso..."
                            style={{ fontWeight:600, fontSize:14, color:'#1f2937', border:'none', background:'transparent', width:'100%', outline:'none', padding:0 }} />
                        <div style={{ display:'flex', gap:6, marginTop:3, flexWrap:'wrap', alignItems:'center' }}>
                            <span style={S.badge(color)}>{areaLabel(paso.area)}</span>
                            {paso.documentos.length > 0 && <span style={{ ...S.badge('#dbeafe'), color:'#2563eb' }}>{paso.documentos.length} doc{paso.documentos.length > 1 && 's'}</span>}
                            {(paso.depende_de?.length > 0) && <span style={{ ...S.badge('#f3e8ff'), color:'#7c3aed' }}>{paso.depende_de.length} dep</span>}
                            {paso.dias && <span style={{ fontSize:10, color:'#9ca3af' }}>{paso.dias}d</span>}
                        </div>
                    </div>
                    <div style={{ display:'flex', gap:2, alignItems:'center' }}>
                        {idx > 0 && <button onClick={e => { e.stopPropagation(); onMove(-1); }} style={{ ...S.btnSm('transparent','#6b7280'), padding:4 }} title="Subir">▲</button>}
                        {idx < total - 1 && <button onClick={e => { e.stopPropagation(); onMove(1); }} style={{ ...S.btnSm('transparent','#6b7280'), padding:4 }} title="Bajar">▼</button>}
                        <button onClick={e => { e.stopPropagation(); onRemove(); }} style={{ ...S.btnSm('transparent','#ef4444'), padding:4 }} title="Eliminar">✕</button>
                    </div>
                    <span style={{ fontSize:14, color:'#9ca3af' }}>{open ? '▲' : '▼'}</span>
                </div>

                {/* Panel expandido */}
                {open && (
                    <div style={{ borderTop:'1px solid #f3f4f6', padding:'16px 20px' }}>
                        {/* Config básica */}
                        <div style={S.grid2}>
                            <div>
                                <label style={S.label}>Área Responsable</label>
                                <select value={paso.area} onChange={e => onChange({ ...paso, area: e.target.value })} style={{ ...S.select, width:'100%' }}>
                                    {AREAS.map(a => <option key={a.v} value={a.v}>{a.l}</option>)}
                                </select>
                            </div>
                            <div>
                                <label style={S.label}>Días Estimados</label>
                                <input type="number" value={paso.dias || ''} onChange={e => onChange({ ...paso, dias: parseInt(e.target.value) || null })} style={S.input} placeholder="3" />
                            </div>
                        </div>
                        <div style={{ marginTop:12 }}>
                            <label style={S.label}>Instrucciones</label>
                            <textarea value={paso.instrucciones || ''} onChange={e => onChange({ ...paso, instrucciones: e.target.value })}
                                style={{ ...S.input, minHeight:48, resize:'vertical', fontFamily:'inherit' }} placeholder="Instrucciones para el ejecutor..." />
                        </div>

                        {/* ═══ DOCUMENTOS / CHECKS ═══ */}
                        <div style={{ marginTop:16 }}>
                            <label style={{ ...S.label, display:'flex', alignItems:'center', gap:4 }}>
                                📄 Documentos / Requisitos (Checks)
                            </label>
                            <p style={{ fontSize:11, color:'#9ca3af', marginBottom:8 }}>
                                Cada documento es un "check" que se debe cumplir en este paso. Marque ✓ si es obligatorio. Use "Depende de" para que un check solo se active cuando otro anterior esté completado.
                            </p>
                            {(() => {
                                /* Collect all docs from previous steps for dependency selection */
                                const prevDocs = [];
                                allPasos.forEach((p, pi) => {
                                    if (pi >= idx) return;
                                    (p.documentos || []).forEach((d, di) => {
                                        prevDocs.push({ pasoIdx: pi, docIdx: di, pasoNombre: p.nombre || `Paso ${pi+1}`, docNombre: d.nombre, key: `${pi}:${di}` });
                                    });
                                });
                                return paso.documentos.map((doc, di) => (
                                    <div key={di} style={{ ...S.docRow, flexWrap:'wrap' }}>
                                        <div style={{ display:'flex', alignItems:'center', gap:8, width:'100%' }}>
                                            <div style={S.check(doc.obligatorio)} onClick={() => toggleDocObl(di)} title={doc.obligatorio ? 'Obligatorio (click para opcional)' : 'Opcional (click para obligatorio)'}>
                                                {doc.obligatorio && '✓'}
                                            </div>
                                            <input type="text" value={doc.nombre} onChange={e => editDocNombre(di, e.target.value)}
                                                style={{ flex:1, border:'none', background:'transparent', fontSize:12, outline:'none', padding:'2px 4px' }} />
                                            <select value={doc.tipo || 'pdf'} onChange={e => editDocTipo(di, e.target.value)}
                                                style={{ fontSize:10, border:'1px solid #e5e7eb', borderRadius:4, padding:'2px 4px', background:'#fff' }}>
                                                <option value="pdf">PDF</option><option value="excel">Excel</option>
                                                <option value="word">Word</option><option value="imagen">Imagen</option><option value="otro">Otro</option>
                                            </select>
                                            <button onClick={() => rmDoc(di)} style={{ cursor:'pointer', color:'#ef4444', background:'none', border:'none', fontSize:14 }} title="Quitar documento">✕</button>
                                        </div>
                                        {prevDocs.length > 0 && (
                                            <div style={{ width:'100%', paddingLeft:28, marginTop:2 }}>
                                                <select value={doc.depende_de_doc || ''} onChange={e => setDocDep(di, e.target.value)}
                                                    style={{ fontSize:10, border:'1px solid #e5e7eb', borderRadius:4, padding:'2px 6px', background: doc.depende_de_doc ? '#faf5ff' : '#fff', color: doc.depende_de_doc ? '#7c3aed' : '#6b7280', width:'100%', maxWidth:350 }}>
                                                    <option value="">Sin dependencia de documento</option>
                                                    {prevDocs.map(pd => (
                                                        <option key={pd.key} value={pd.key}>
                                                            Depende de: Paso {pd.pasoIdx+1} → {pd.docNombre}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                        )}
                                    </div>
                                ));
                            })()}
                            <div style={{ display:'flex', gap:6, marginTop:4 }}>
                                <input type="text" value={newDoc} onChange={e => setNewDoc(e.target.value)}
                                    onKeyDown={e => e.key === 'Enter' && addDoc()}
                                    style={{ ...S.input, flex:1, borderStyle:'dashed' }} placeholder="+ Nombre del documento o requisito..." />
                                <button onClick={addDoc} style={S.btnSm('#eff6ff','#2563eb')}>Agregar</button>
                            </div>
                        </div>

                        {/* ═══ DEPENDENCIAS ═══ */}
                        {total > 1 && (
                            <div style={{ marginTop:16 }}>
                                <label style={{ ...S.label, display:'flex', alignItems:'center', gap:4 }}>
                                    🔗 Depende de (pasos previos)
                                </label>
                                <p style={{ fontSize:11, color:'#9ca3af', marginBottom:8 }}>
                                    Si este paso requiere que otro paso esté completado primero, márquelo aquí.
                                </p>
                                <div style={{ display:'flex', flexWrap:'wrap', gap:6 }}>
                                    {allPasos.map((other, oi) => {
                                        if (oi === idx) return null;
                                        const active = (paso.depende_de || []).includes(oi);
                                        return (
                                            <button key={oi} onClick={() => toggleDep(oi)} style={S.depBtn(active)}>
                                                {active && '✓ '}{oi + 1}. {other.nombre || `Paso ${oi + 1}`}
                                            </button>
                                        );
                                    })}
                                </div>
                                {!(paso.depende_de?.length) && <p style={{ fontSize:10, color:'#9ca3af', marginTop:4 }}>Sin dependencias (se ejecuta en orden secuencial).</p>}
                            </div>
                        )}

                        <div style={{ marginTop:12, paddingTop:8, borderTop:'1px solid #f3f4f6', display:'flex', alignItems:'center', gap:8 }}>
                            <input type="checkbox" checked={paso.obligatorio !== false}
                                onChange={e => onChange({ ...paso, obligatorio: e.target.checked })} />
                            <span style={{ fontSize:12, color:'#4b5563' }}>Paso obligatorio</span>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}


/* ================================================================
   BUILDER – Constructor visual (SINGLE COLUMN)
   ================================================================ */
function Builder({ flujoEdit, catalogo, secretariaId, onDone, onCancel, toast }) {
    const [codigo, setCodigo] = useState(flujoEdit?.codigo || '');
    const [nombre, setNombre] = useState(flujoEdit?.nombre || '');
    const [desc, setDesc] = useState(flujoEdit?.descripcion || '');
    const [pasos, setPasos] = useState([]);
    const [saving, setSaving] = useState(false);
    const [loadingPasos, setLoadingPasos] = useState(false);
    const [showCatalog, setShowCatalog] = useState(false);

    // ── Drag ──
    const dragIdx = useRef(null);
    const [overIdx, setOverIdx] = useState(null);

    useEffect(() => {
        if (!flujoEdit?.id) return;
        setLoadingPasos(true);
        apiFetch(`${API}/flujos/${flujoEdit.id}/pasos`)
            .then(d => {
                setPasos((d.pasos || []).map((p, i) => ({
                    catalogo_paso_id: p.catalogo_paso_id,
                    nombre: p.nombre_personalizado || p.catalogo_paso?.nombre || `Paso ${i + 1}`,
                    area: p.area_responsable_default || 'unidad_solicitante',
                    dias: p.dias_estimados,
                    instrucciones: p.instrucciones || '',
                    obligatorio: p.es_obligatorio !== false,
                    documentos: (p.documentos || []).map(doc => ({
                        nombre: doc.nombre, tipo: doc.tipo_archivo || 'pdf', obligatorio: doc.es_obligatorio !== false,
                        depende_de_doc: doc.depende_de_doc || null,
                    })),
                    depende_de: [],
                })));
            })
            .catch(() => toast('Error cargando pasos', 'error'))
            .finally(() => setLoadingPasos(false));
    }, [flujoEdit]);

    const addPaso = (cat = null) => {
        setPasos(p => [...p, {
            catalogo_paso_id: cat?.id || null,
            nombre: cat?.nombre || '', area: 'unidad_solicitante', dias: 3,
            instrucciones: '', obligatorio: true, documentos: [], depende_de: [],
        }]);
        setShowCatalog(false);
    };
    const updatePaso = (i, v) => setPasos(p => { const a = [...p]; a[i] = v; return a; });
    const removePaso = i => setPasos(p => p.filter((_, j) => j !== i).map(paso => ({
        ...paso, depende_de: (paso.depende_de || []).filter(d => d !== i).map(d => d > i ? d - 1 : d),
    })));
    const movePaso = (i, dir) => {
        setPasos(p => {
            const j = i + dir;
            if (j < 0 || j >= p.length) return p;
            const a = [...p]; [a[i], a[j]] = [a[j], a[i]];
            return a.map(paso => ({
                ...paso, depende_de: (paso.depende_de || []).map(d => d === i ? j : d === j ? i : d),
            }));
        });
    };

    /* Drag & Drop */
    const onDragStart = i => e => { dragIdx.current = i; e.dataTransfer.effectAllowed = 'move'; };
    const onDragOver = i => e => { e.preventDefault(); setOverIdx(i); };
    const onDragLeave = () => setOverIdx(null);
    const onDrop = dropI => e => {
        e.preventDefault();
        const fromI = dragIdx.current;
        if (fromI === null || fromI === dropI) { dragIdx.current = null; setOverIdx(null); return; }
        setPasos(prev => {
            const a = [...prev]; const [moved] = a.splice(fromI, 1); a.splice(dropI, 0, moved);
            const remap = old => { if (old === fromI) return dropI; if (fromI < dropI) { if (old > fromI && old <= dropI) return old - 1; } else { if (old >= dropI && old < fromI) return old + 1; } return old; };
            return a.map(p => ({ ...p, depende_de: (p.depende_de || []).map(remap) }));
        });
        dragIdx.current = null; setOverIdx(null);
    };
    const onDragEnd = () => { dragIdx.current = null; setOverIdx(null); };

    /* SAVE */
    const handleSave = async () => {
        if (!nombre.trim()) return toast('El nombre del flujo es requerido.', 'warning');
        if (!codigo.trim()) return toast('El código del flujo es requerido.', 'warning');
        if (pasos.length === 0) return toast('Agregue al menos un paso.', 'warning');
        for (let i = 0; i < pasos.length; i++) { if (!pasos[i].nombre.trim()) return toast(`El paso #${i + 1} no tiene nombre.`, 'warning'); }
        setSaving(true);
        try {
            await apiFetch(`${API}/flujos/guardar-completo`, {
                method: 'POST',
                body: JSON.stringify({
                    flujo_id: flujoEdit?.id || null, codigo, nombre,
                    descripcion: desc || null, secretaria_id: secretariaId,
                    pasos: pasos.map(p => ({
                        catalogo_paso_id: p.catalogo_paso_id, nombre: p.nombre, area_responsable: p.area,
                        dias_estimados: p.dias, instrucciones: p.instrucciones || null, obligatorio: p.obligatorio !== false,
                        documentos: p.documentos.map(d => ({ nombre: d.nombre, tipo: d.tipo || 'pdf', obligatorio: d.obligatorio !== false, depende_de_doc: d.depende_de_doc || null })),
                        depende_de: p.depende_de || [],
                    })),
                }),
            });
            toast('Flujo guardado exitosamente.');
            onDone();
        } catch (e) { toast(e.message, 'error'); }
        setSaving(false);
    };

    /* ── RENDER ── */
    return (
        <div>
            {/* ═══ TOP BAR ═══ */}
            <div style={{ background:'linear-gradient(135deg,#2563eb,#4338ca)', color:'#fff', padding:'16px 24px' }}>
                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                    <div>
                        <h2 style={{ margin:0, fontSize:18, fontWeight:700 }}>{flujoEdit ? '✏️ Editar Flujo' : '➕ Nuevo Flujo'}</h2>
                        <p style={{ margin:'2px 0 0', fontSize:12, color:'#bfdbfe' }}>Defina pasos, documentos (checks) y dependencias</p>
                    </div>
                    <div style={{ display:'flex', gap:8 }}>
                        <button onClick={onCancel} style={S.btn('rgba(255,255,255,.15)')}>Cancelar</button>
                        <button onClick={handleSave} disabled={saving} style={S.btn('#fff','#1d4ed8')}>
                            {saving ? '⏳ Guardando...' : '💾 Guardar Flujo'}
                        </button>
                    </div>
                </div>
            </div>

            {/* ═══ FORM INFO ═══ */}
            <div style={{ background:'#fff', borderBottom:'1px solid #e5e7eb', padding:'20px 24px' }}>
                <h3 style={{ fontSize:12, fontWeight:700, color:'#6b7280', textTransform:'uppercase', marginBottom:12 }}>Información del Flujo</h3>
                <div>
                    <label style={S.label}>Código</label>
                    <input type="text" value={codigo}
                        onChange={e => setCodigo(e.target.value.toUpperCase().replace(/[^A-Z0-9_]/g, ''))}
                        disabled={!!flujoEdit?.id} style={{ ...S.input, ...(flujoEdit?.id ? { background:'#f3f4f6', color:'#6b7280' } : {}) }}
                        placeholder="CD_PN_CULTURA" />
                </div>
                <div style={{ marginTop:12 }}>
                    <label style={S.label}>Nombre del Flujo</label>
                    <input type="text" value={nombre} onChange={e => setNombre(e.target.value)} style={S.input} placeholder="Ej: Mínima Cuantía – Secretaría de Cultura" />
                </div>
                <div style={{ marginTop:12 }}>
                    <label style={S.label}>Descripción <span style={{ fontWeight:400, color:'#9ca3af' }}>(opcional)</span></label>
                    <textarea value={desc} onChange={e => setDesc(e.target.value)} rows={2}
                        style={{ ...S.input, minHeight:48, resize:'vertical', fontFamily:'inherit' }} placeholder="Descripción breve del flujo..." />
                </div>
            </div>

            {/* ═══ PASOS ═══ */}
            <div style={S.section}>
                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:16 }}>
                    <div>
                        <h3 style={{ margin:0, fontSize:12, fontWeight:700, color:'#6b7280', textTransform:'uppercase' }}>
                            Pasos del Flujo <span style={{ fontWeight:400, textTransform:'none', color:'#9ca3af' }}>({pasos.length} pasos) — click en un paso para expandir y editar — use ▲▼ o arrastre para reordenar</span>
                        </h3>
                    </div>
                    <div style={{ position:'relative' }}>
                        <button onClick={() => setShowCatalog(!showCatalog)} style={S.btn('#2563eb')}>
                            + Agregar Paso
                        </button>
                        {/* Dropdown catálogo */}
                        {showCatalog && (
                            <div style={{ position:'absolute', right:0, top:'100%', marginTop:8, width:360, background:'#fff', border:'1px solid #e5e7eb', borderRadius:12, boxShadow:'0 8px 24px rgba(0,0,0,.12)', zIndex:100, maxHeight:450, overflowY:'auto' }}>
                                <div style={{ padding:'12px 16px', borderBottom:'1px solid #e5e7eb', background:'#f9fafb', borderRadius:'12px 12px 0 0' }}>
                                    <h4 style={{ margin:0, fontSize:12, fontWeight:700, color:'#374151' }}>Catálogo de Pasos</h4>
                                    <p style={{ margin:'2px 0 0', fontSize:10, color:'#9ca3af' }}>Click para agregar al flujo</p>
                                </div>
                                {catalogo.map(cp => (
                                    <button key={cp.id} onClick={() => addPaso(cp)}
                                        style={{ display:'block', width:'100%', textAlign:'left', padding:'10px 16px', border:'none', borderBottom:'1px solid #f3f4f6', background:'#fff', cursor:'pointer', fontSize:12 }}
                                        onMouseEnter={e => e.currentTarget.style.background='#eff6ff'}
                                        onMouseLeave={e => e.currentTarget.style.background='#fff'}>
                                        <div style={{ display:'flex', alignItems:'center', gap:6 }}>
                                            <div style={{ width:8, height:8, borderRadius:'50%', background:cp.color || '#6b7280' }} />
                                            <strong style={{ color:'#1f2937' }}>{cp.nombre}</strong>
                                        </div>
                                        {cp.descripcion && <p style={{ margin:'2px 0 0 14px', fontSize:10, color:'#9ca3af' }}>{cp.descripcion}</p>}
                                        <div style={{ display:'flex', gap:4, marginTop:2, marginLeft:14 }}>
                                            <span style={{ fontSize:9, background:'#f3f4f6', padding:'1px 6px', borderRadius:4, color:'#6b7280' }}>{cp.tipo}</span>
                                            <span style={{ fontSize:9, color:'#9ca3af' }}>{cp.codigo}</span>
                                        </div>
                                    </button>
                                ))}
                                <button onClick={() => addPaso()} style={{ display:'block', width:'100%', textAlign:'left', padding:'12px 16px', border:'none', background:'#f9fafb', cursor:'pointer', fontSize:12, color:'#6b7280', borderRadius:'0 0 12px 12px' }}
                                    onMouseEnter={e => e.currentTarget.style.background='#eff6ff'} onMouseLeave={e => e.currentTarget.style.background='#f9fafb'}>
                                    ✚ Paso personalizado (sin catálogo)
                                </button>
                            </div>
                        )}
                    </div>
                </div>

                {/* Click away to close catalog dropdown */}
                {showCatalog && <div onClick={() => setShowCatalog(false)} style={{ position:'fixed', inset:0, zIndex:50 }} />}

                {loadingPasos && (
                    <div style={{ textAlign:'center', padding:'48px 0', color:'#6b7280' }}>
                        <div style={{ fontSize:24, marginBottom:8 }}>⏳</div>
                        <p>Cargando pasos...</p>
                    </div>
                )}

                {!loadingPasos && pasos.length === 0 && (
                    <div style={{ textAlign:'center', padding:'48px 0', border:'2px dashed #e5e7eb', borderRadius:16 }}>
                        <div style={{ fontSize:40, marginBottom:8 }}>📋</div>
                        <p style={{ fontWeight:500, color:'#6b7280' }}>Sin pasos configurados</p>
                        <p style={{ fontSize:13, color:'#9ca3af', marginTop:4 }}>Use el botón "Agregar Paso" arriba para comenzar.</p>
                        <button onClick={() => setShowCatalog(true)} style={{ ...S.btn('#2563eb'), marginTop:16 }}>+ Agregar primer paso</button>
                    </div>
                )}

                {/* Lista de paso cards */}
                <div style={{ maxWidth:800 }}>
                    {pasos.map((paso, i) => (
                        <div key={i}
                            draggable
                            onDragStart={onDragStart(i)}
                            onDragOver={onDragOver(i)}
                            onDragLeave={onDragLeave}
                            onDrop={onDrop(i)}
                            onDragEnd={onDragEnd}
                            style={overIdx === i && dragIdx.current !== i ? { outline:'2px solid #60a5fa', borderRadius:12 } : {}}>
                            <PasoCard
                                paso={paso} idx={i} total={pasos.length} allPasos={pasos}
                                onChange={v => updatePaso(i, v)}
                                onRemove={() => removePaso(i)}
                                onMove={dir => movePaso(i, dir)}
                            />
                        </div>
                    ))}
                </div>

                {pasos.length > 0 && (
                    <div style={{ textAlign:'center', marginTop:8 }}>
                        <button onClick={() => setShowCatalog(true)} style={{ padding:'12px 0', width:'100%', maxWidth:800, border:'2px dashed #d1d5db', borderRadius:12, background:'transparent', cursor:'pointer', fontSize:13, color:'#6b7280' }}
                            onMouseEnter={e => { e.currentTarget.style.borderColor='#60a5fa'; e.currentTarget.style.color='#2563eb'; e.currentTarget.style.background='#eff6ff'; }}
                            onMouseLeave={e => { e.currentTarget.style.borderColor='#d1d5db'; e.currentTarget.style.color='#6b7280'; e.currentTarget.style.background='transparent'; }}>
                            + Agregar paso
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}


/* ================================================================
   LISTA DE FLUJOS
   ================================================================ */
function FlujosList({ flujos, loading, onView, onEdit, onNew, onDelete, toast }) {
    const handleDelete = async (f) => {
        if (!confirm(`¿Eliminar el flujo "${f.nombre}"?`)) return;
        try {
            await apiFetch(`${API}/flujos/${f.id}`, { method: 'DELETE' });
            toast('Flujo eliminado.'); onDelete();
        } catch (e) { toast(e.message, 'error'); }
    };

    return (
        <div style={S.section}>
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:20 }}>
                <div>
                    <h3 style={{ margin:0, fontSize:18, fontWeight:700, color:'#1f2937' }}>Flujos Configurados</h3>
                    <p style={{ margin:'2px 0 0', fontSize:13, color:'#9ca3af' }}>{flujos.length} flujo{flujos.length !== 1 && 's'}</p>
                </div>
                <button onClick={onNew} style={S.btn('#2563eb')}>+ Crear Nuevo Flujo</button>
            </div>

            {loading && <div style={{ textAlign:'center', padding:'80px 0', color:'#6b7280' }}><div style={{ fontSize:28 }}>⏳</div><p>Cargando flujos...</p></div>}

            {!loading && flujos.length === 0 && (
                <div style={{ textAlign:'center', padding:'80px 0', background:'#fff', borderRadius:16, border:'2px dashed #e5e7eb' }}>
                    <div style={{ fontSize:48, marginBottom:12 }}>🏗️</div>
                    <h3 style={{ color:'#374151', fontWeight:700 }}>Sin flujos para esta Secretaría</h3>
                    <p style={{ color:'#9ca3af', marginTop:4 }}>Cree su primer flujo de contratación definiendo pasos, documentos y dependencias.</p>
                    <button onClick={onNew} style={{ ...S.btn('#2563eb'), marginTop:20 }}>+ Crear Primer Flujo</button>
                </div>
            )}

            {!loading && flujos.length > 0 && (
                <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill, minmax(320px, 1fr))', gap:16, maxWidth:900 }}>
                    {flujos.map(f => (
                        <div key={f.id} style={{ ...S.card, marginBottom:0 }}>
                            <div style={{ padding:20 }}>
                                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start' }}>
                                    <div style={{ minWidth:0, flex:1 }}>
                                        <h4 style={{ margin:0, fontSize:14, fontWeight:700, color:'#1f2937', overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>{f.nombre}</h4>
                                        <p style={{ margin:'2px 0 0', fontSize:11, color:'#9ca3af' }}>{f.codigo}</p>
                                    </div>
                                    <span style={{ ...S.badge('#dcfce7'), color:'#15803d', marginLeft:8 }}>v{f.version_activa?.numero_version || '1'}</span>
                                </div>
                                {f.descripcion && <p style={{ margin:'8px 0 0', fontSize:12, color:'#6b7280', display:'-webkit-box', WebkitLineClamp:2, WebkitBoxOrient:'vertical', overflow:'hidden' }}>{f.descripcion}</p>}
                            </div>
                            <div style={{ borderTop:'1px solid #f3f4f6', padding:'10px 20px', background:'#f9fafb', display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                                <span style={{ fontSize:10, color:'#9ca3af' }}>{f.version_activa?.pasos?.length ?? '?'} pasos</span>
                                <div style={{ display:'flex', gap:12 }}>
                                    <button onClick={() => onView(f)} style={{ ...S.btnSm('transparent','#2563eb'), padding:0 }}>👁 Ver</button>
                                    <button onClick={() => onEdit(f)} style={{ ...S.btnSm('transparent','#4f46e5'), padding:0 }}>✏️ Editar</button>
                                    <button onClick={() => handleDelete(f)} style={{ ...S.btnSm('transparent','#dc2626'), padding:0 }}>🗑</button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}


/* ================================================================
   VISTA DETALLE (solo lectura)
   ================================================================ */
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
        <div style={S.section}>
            <div style={{ display:'flex', alignItems:'center', gap:12, marginBottom:20 }}>
                <button onClick={onBack} style={{ ...S.btnSm('#f3f4f6','#6b7280'), borderRadius:8 }}>← Volver</button>
                <div style={{ flex:1, minWidth:0 }}>
                    <h3 style={{ margin:0, fontSize:18, fontWeight:700, color:'#1f2937' }}>{flujo.nombre}</h3>
                    <p style={{ margin:'2px 0 0', fontSize:12, color:'#9ca3af' }}>{flujo.codigo} · {flujo.tipo_contratacion} · v{flujo.version_activa?.numero_version || '?'}</p>
                </div>
                <button onClick={() => onEdit(flujo)} style={S.btn('#4f46e5')}>✏️ Editar</button>
            </div>

            {loading ? (
                <div style={{ textAlign:'center', padding:'60px 0', color:'#6b7280' }}><div style={{ fontSize:24 }}>⏳</div><p>Cargando pasos...</p></div>
            ) : (
                <div style={{ maxWidth:700 }}>
                    {pasos.map((p, i) => {
                        const color = areaColor(p.area_responsable_default);
                        const nom = p.nombre_personalizado || p.catalogo_paso?.nombre || `Paso ${p.orden + 1}`;
                        return (
                            <div key={p.id}>
                                {i > 0 && <div style={S.connector}><div style={{ display:'flex', flexDirection:'column', alignItems:'center' }}><div style={{ width:2, height:20, background:'#d1d5db' }} /></div></div>}
                                <div style={S.card}>
                                    <div style={{ padding:16 }}>
                                        <div style={{ display:'flex', alignItems:'center', gap:12 }}>
                                            <div style={{ width:40, height:40, borderRadius:10, background:color, color:'#fff', display:'flex', alignItems:'center', justifyContent:'center', fontWeight:700, fontSize:15, flexShrink:0 }}>{p.orden + 1}</div>
                                            <div style={{ flex:1, minWidth:0 }}>
                                                <h4 style={{ margin:0, fontSize:14, fontWeight:600, color:'#1f2937' }}>{nom}</h4>
                                                <div style={{ display:'flex', gap:6, marginTop:3, flexWrap:'wrap', alignItems:'center' }}>
                                                    <span style={S.badge(color)}>{areaLabel(p.area_responsable_default)}</span>
                                                    {p.dias_estimados && <span style={{ fontSize:10, color:'#9ca3af' }}>{p.dias_estimados} días</span>}
                                                    {!p.es_obligatorio && <span style={{ ...S.badge('#fef3c7'), color:'#b45309' }}>Opcional</span>}
                                                </div>
                                            </div>
                                        </div>

                                        {(p.documentos?.length > 0) && (
                                            <div style={{ marginTop:12, paddingLeft:16, borderLeft:'3px solid #bfdbfe' }}>
                                                <p style={{ fontSize:10, fontWeight:700, color:'#2563eb', textTransform:'uppercase', marginBottom:4 }}>Documentos requeridos (checks)</p>
                                                {p.documentos.map(doc => (
                                                    <div key={doc.id} style={{ display:'flex', alignItems:'center', gap:6, padding:'2px 0', flexWrap:'wrap' }}>
                                                        <div style={S.check(doc.es_obligatorio)}>{doc.es_obligatorio && '✓'}</div>
                                                        <span style={{ fontSize:12, color:'#4b5563' }}>{doc.nombre}</span>
                                                        <span style={{ fontSize:9, color:'#9ca3af' }}>({doc.tipo_archivo})</span>
                                                        {doc.depende_de_doc && (() => {
                                                            const [pi, di] = doc.depende_de_doc.split(':').map(Number);
                                                            const depPaso = pasos[pi];
                                                            const depDoc = depPaso?.documentos?.[di];
                                                            return depDoc ? (
                                                                <span style={{ fontSize:9, color:'#7c3aed', background:'#f5f3ff', padding:'1px 6px', borderRadius:8 }}>
                                                                    ↳ Depende de: Paso {pi+1} → {depDoc.nombre}
                                                                </span>
                                                            ) : null;
                                                        })()}
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {(p.condiciones?.length > 0) && (
                                            <div style={{ marginTop:8, paddingLeft:16, borderLeft:'3px solid #fde68a' }}>
                                                <p style={{ fontSize:10, fontWeight:700, color:'#b45309', textTransform:'uppercase', marginBottom:4 }}>Dependencias</p>
                                                {p.condiciones.map(c => (
                                                    <p key={c.id} style={{ fontSize:12, color:'#92400e', padding:'2px 0' }}>↳ {c.descripcion}</p>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                    {pasos.length === 0 && (
                        <div style={{ textAlign:'center', padding:'48px 0', color:'#9ca3af' }}>
                            <p style={{ fontSize:16 }}>Sin pasos configurados</p>
                            <button onClick={() => onEdit(flujo)} style={{ marginTop:12, cursor:'pointer', color:'#2563eb', background:'none', border:'none', fontSize:13 }}>Editar flujo para agregar pasos</button>
                        </div>
                    )}
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

    if (!user && !error) return <div style={{ display:'flex', justifyContent:'center', alignItems:'center', minHeight:'50vh' }}><span style={{ fontSize:24 }}>⏳</span></div>;
    if (error) return (
        <div style={{ maxWidth:400, margin:'60px auto', padding:24, background:'#fef2f2', border:'1px solid #fecaca', borderRadius:16, textAlign:'center' }}>
            <div style={{ fontSize:36, marginBottom:8 }}>🔒</div>
            <h2 style={{ color:'#991b1b', fontWeight:700 }}>Error</h2>
            <p style={{ color:'#dc2626', fontSize:13 }}>{error}</p>
            <a href="/login" style={{ display:'inline-block', marginTop:12, padding:'8px 20px', background:'#dc2626', color:'#fff', borderRadius:8, textDecoration:'none', fontSize:13 }}>Ir al Login</a>
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
        <div style={{ minHeight:'50vh' }}>
            {/* Top bar */}
            <div style={{ background:'#fff', borderBottom:'1px solid #e5e7eb', padding:'12px 24px', display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                <div>
                    <h1 style={{ margin:0, fontSize:16, fontWeight:700, color:'#1f2937' }}>Motor de Flujos</h1>
                    <p style={{ margin:0, fontSize:11, color:'#9ca3af' }}>Configuración de flujos de contratación por secretaría</p>
                </div>
                {esAdmin && secretarias.length > 0 && (
                    <div style={{ display:'flex', alignItems:'center', gap:8 }}>
                        <label style={{ fontSize:12, fontWeight:500, color:'#6b7280' }}>Secretaría:</label>
                        <select value={effectiveSecId || ''} onChange={e => setSecId(parseInt(e.target.value))}
                            style={{ ...S.select, minWidth:280 }}>
                            <option value="" disabled>Seleccionar...</option>
                            {secretarias.map(s => <option key={s.id} value={s.id}>{s.nombre}</option>)}
                        </select>
                    </div>
                )}
            </div>

            {!effectiveSecId ? (
                <div style={{ textAlign:'center', padding:'80px 0' }}>
                    <div style={{ fontSize:48, marginBottom:12 }}>🏛️</div>
                    <p style={{ fontSize:16, fontWeight:500, color:'#6b7280' }}>Seleccione una Secretaría</p>
                    <p style={{ fontSize:13, color:'#9ca3af', marginTop:4 }}>Use el selector de arriba para comenzar</p>
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
