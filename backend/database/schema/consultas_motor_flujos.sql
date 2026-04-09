-- ╔══════════════════════════════════════════════════════════════════════════════╗
-- ║  CONSULTAS SQL – Motor de Flujos Configurable por Secretaría              ║
-- ╠══════════════════════════════════════════════════════════════════════════════╣
-- ║  Consultas útiles para el motor de flujos en MariaDB.                     ║
-- ║  Compatibles con MariaDB 10.5+ y MySQL 8.0+.                             ║
-- ╚══════════════════════════════════════════════════════════════════════════════╝

-- ═══════════════════════════════════════════════════════════════════════════════
-- 1) OBTENER FLUJO ORDENADO COMPLETO PARA UNA SECRETARÍA
--    Devuelve todos los pasos con documentos, responsables y condiciones
-- ═══════════════════════════════════════════════════════════════════════════════
SELECT
    f.id                       AS flujo_id,
    f.codigo                   AS flujo_codigo,
    f.nombre                   AS flujo_nombre,
    f.tipo_contratacion,
    s.nombre                   AS secretaria,
    fv.numero_version,
    fv.estado                  AS version_estado,
    fv.publicada_at,
    fp.orden                   AS paso_orden,
    cp.codigo                  AS paso_codigo,
    COALESCE(fp.nombre_personalizado, cp.nombre) AS paso_nombre,
    cp.descripcion             AS paso_descripcion,
    cp.icono,
    cp.color,
    cp.tipo                    AS paso_tipo,
    fp.es_obligatorio,
    fp.es_paralelo,
    fp.dias_estimados,
    fp.area_responsable_default
FROM flujos f
    INNER JOIN secretarias s        ON s.id  = f.secretaria_id
    INNER JOIN flujo_versiones fv   ON fv.id = f.version_activa_id
    INNER JOIN flujo_pasos fp       ON fp.flujo_version_id = fv.id AND fp.activo = 1
    INNER JOIN catalogo_pasos cp    ON cp.id = fp.catalogo_paso_id
WHERE f.secretaria_id = :secretaria_id          -- ← Parámetro: ID de la Secretaría
  AND f.tipo_contratacion = :tipo_contratacion  -- ← Parámetro: 'cd_pn', 'lp', etc.
  AND f.activo = 1
ORDER BY fp.orden ASC;


-- ═══════════════════════════════════════════════════════════════════════════════
-- 2) OBTENER DOCUMENTOS REQUERIDOS POR PASO
-- ═══════════════════════════════════════════════════════════════════════════════
SELECT
    fp.orden            AS paso_orden,
    COALESCE(fp.nombre_personalizado, cp.nombre) AS paso_nombre,
    fpd.nombre          AS documento_nombre,
    fpd.descripcion     AS documento_descripcion,
    fpd.tipo_archivo,
    fpd.es_obligatorio,
    fpd.max_archivos,
    fpd.max_tamano_mb,
    fpd.plantilla_url
FROM flujo_paso_documentos fpd
    INNER JOIN flujo_pasos fp       ON fp.id = fpd.flujo_paso_id
    INNER JOIN catalogo_pasos cp    ON cp.id = fp.catalogo_paso_id
    INNER JOIN flujo_versiones fv   ON fv.id = fp.flujo_version_id
    INNER JOIN flujos f             ON f.id  = fv.flujo_id
WHERE f.secretaria_id = :secretaria_id
  AND f.tipo_contratacion = :tipo_contratacion
  AND fv.estado = 'activa'
  AND fp.activo = 1
  AND fpd.activo = 1
ORDER BY fp.orden ASC, fpd.orden ASC;


-- ═══════════════════════════════════════════════════════════════════════════════
-- 3) OBTENER RESPONSABLES POR PASO
-- ═══════════════════════════════════════════════════════════════════════════════
SELECT
    fp.orden            AS paso_orden,
    COALESCE(fp.nombre_personalizado, cp.nombre) AS paso_nombre,
    fpr.rol,
    fpr.tipo,           -- ejecutor, revisor, aprobador, observador
    fpr.es_principal,
    u.name              AS usuario_nombre,
    u.email             AS usuario_email,
    un.nombre           AS unidad_nombre
FROM flujo_paso_responsables fpr
    INNER JOIN flujo_pasos fp       ON fp.id = fpr.flujo_paso_id
    INNER JOIN catalogo_pasos cp    ON cp.id = fp.catalogo_paso_id
    INNER JOIN flujo_versiones fv   ON fv.id = fp.flujo_version_id
    INNER JOIN flujos f             ON f.id  = fv.flujo_id
    LEFT JOIN users u               ON u.id  = fpr.user_id
    LEFT JOIN unidades un           ON un.id = fpr.unidad_id
WHERE f.secretaria_id = :secretaria_id
  AND f.tipo_contratacion = :tipo_contratacion
  AND fv.estado = 'activa'
  AND fp.activo = 1
  AND fpr.activo = 1
ORDER BY fp.orden ASC, fpr.es_principal DESC;


-- ═══════════════════════════════════════════════════════════════════════════════
-- 4) OBTENER CONDICIONES POR PASO
-- ═══════════════════════════════════════════════════════════════════════════════
SELECT
    fp.orden            AS paso_orden,
    COALESCE(fp.nombre_personalizado, cp.nombre) AS paso_nombre,
    fpc.campo,
    fpc.operador,
    fpc.valor,
    fpc.accion,
    fpc.descripcion     AS condicion_descripcion,
    fpc.prioridad
FROM flujo_paso_condiciones fpc
    INNER JOIN flujo_pasos fp       ON fp.id = fpc.flujo_paso_id
    INNER JOIN catalogo_pasos cp    ON cp.id = fp.catalogo_paso_id
    INNER JOIN flujo_versiones fv   ON fv.id = fp.flujo_version_id
    INNER JOIN flujos f             ON f.id  = fv.flujo_id
WHERE f.secretaria_id = :secretaria_id
  AND f.tipo_contratacion = :tipo_contratacion
  AND fv.estado = 'activa'
  AND fp.activo = 1
  AND fpc.activo = 1
ORDER BY fp.orden ASC, fpc.prioridad ASC;


-- ═══════════════════════════════════════════════════════════════════════════════
-- 5) DASHBOARD: ESTADO DE UNA INSTANCIA (proceso en curso)
-- ═══════════════════════════════════════════════════════════════════════════════
SELECT
    fi.codigo_proceso,
    fi.objeto,
    fi.monto_estimado,
    fi.estado                AS estado_proceso,
    s.nombre                 AS secretaria,
    un.nombre                AS unidad,
    fip.orden                AS paso_orden,
    COALESCE(fp.nombre_personalizado, cp.nombre) AS paso_nombre,
    fip.estado               AS paso_estado,
    fip.omitido_por_condicion,
    fip.recibido_at,
    fip.completado_at,
    u_recibido.name          AS recibido_por_nombre,
    u_completado.name        AS completado_por_nombre,
    fip.observaciones,
    fip.motivo_devolucion
FROM flujo_instancia_pasos fip
    INNER JOIN flujo_instancias fi   ON fi.id = fip.instancia_id
    INNER JOIN flujo_pasos fp        ON fp.id = fip.flujo_paso_id
    INNER JOIN catalogo_pasos cp     ON cp.id = fp.catalogo_paso_id
    INNER JOIN secretarias s         ON s.id  = fi.secretaria_id
    LEFT JOIN unidades un            ON un.id = fi.unidad_id
    LEFT JOIN users u_recibido       ON u_recibido.id = fip.recibido_por
    LEFT JOIN users u_completado     ON u_completado.id = fip.completado_por
WHERE fi.id = :instancia_id           -- ← Parámetro: ID de la instancia
ORDER BY fip.orden ASC;


-- ═══════════════════════════════════════════════════════════════════════════════
-- 6) COMPARAR FLUJOS DE DOS SECRETARÍAS
-- ═══════════════════════════════════════════════════════════════════════════════
SELECT
    s.nombre                 AS secretaria,
    f.codigo                 AS flujo,
    fp.orden,
    cp.codigo                AS paso_codigo,
    COALESCE(fp.nombre_personalizado, cp.nombre) AS paso_nombre,
    fp.area_responsable_default,
    fp.dias_estimados,
    (SELECT COUNT(*) FROM flujo_paso_documentos fpd WHERE fpd.flujo_paso_id = fp.id AND fpd.activo = 1) AS num_documentos,
    (SELECT COUNT(*) FROM flujo_paso_condiciones fpc WHERE fpc.flujo_paso_id = fp.id AND fpc.activo = 1) AS num_condiciones
FROM flujo_pasos fp
    INNER JOIN flujo_versiones fv   ON fv.id = fp.flujo_version_id
    INNER JOIN flujos f             ON f.id  = fv.flujo_id AND f.version_activa_id = fv.id
    INNER JOIN secretarias s        ON s.id  = f.secretaria_id
    INNER JOIN catalogo_pasos cp    ON cp.id = fp.catalogo_paso_id
WHERE f.tipo_contratacion = :tipo_contratacion
  AND f.activo = 1
  AND fp.activo = 1
ORDER BY s.nombre, fp.orden;


-- ═══════════════════════════════════════════════════════════════════════════════
-- 7) HISTORIAL DE VERSIONES DE UN FLUJO
-- ═══════════════════════════════════════════════════════════════════════════════
SELECT
    fv.numero_version,
    fv.estado,
    fv.motivo_cambio,
    fv.publicada_at,
    u.name                   AS creado_por,
    fv.created_at,
    (SELECT COUNT(*) FROM flujo_pasos fp2 WHERE fp2.flujo_version_id = fv.id) AS total_pasos,
    (SELECT COUNT(*) FROM flujo_instancias fi2 WHERE fi2.flujo_version_id = fv.id) AS procesos_usando
FROM flujo_versiones fv
    LEFT JOIN users u ON u.id = fv.creado_por
WHERE fv.flujo_id = :flujo_id
ORDER BY fv.numero_version DESC;
