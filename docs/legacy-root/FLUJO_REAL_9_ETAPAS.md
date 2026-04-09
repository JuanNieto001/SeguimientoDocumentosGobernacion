# FLUJO REAL CD-PN - 9 ETAPAS (0-8)

## ⚠️ CORRECCIÓN IMPORTANTE
El sistema tenía 16 etapas (0-15) pero el flujo REAL de la Gobernación tiene **9 ETAPAS (0-8)**

---

## 📊 ETAPAS CORRECTAS

### **ETAPA 0 - Definición de la Necesidad**
- **Responsable:** Unidad Solicitante (Jefe de Unidad)
- **Rol:** `unidad_solicitante`
- **Documentos:**
  1. ✅ **Estudios Previos** (objeto, valor, plazo) - ÚNICO DOCUMENTO
- **Envía a:** Descentralización (Planeación)

---

### **ETAPA 1 - Solicitud de Documentos Iniciales** 🔄 PARALELO
- **Responsable:** Unidad de Descentralización
- **Rol:** `planeacion`
- **Documentos (pueden solicitarse simultáneamente):**
  1. PAA → Compras (Sec. General)
  2. No Planta → Talento Humano (Sec. General)
  3. Paz y Salvo Rentas → Hacienda
  4. Paz y Salvo Contabilidad → Hacienda
  5. **Compatibilidad del Gasto** → Planeación ⚠️ REQUERIDO PRIMERO
  6. **CDP** → Hacienda ⚠️ REQUIERE Compatibilidad
  7. SIGEP validado → Jurídica
- **Envía a:** Unidad Solicitante

---

### **ETAPA 2 - Validación del Contratista** 🔄 PARALELO
- **Responsable:** Abogado Unidad Solicitante
- **Rol:** `unidad_solicitante`
- **Documentos del contratista (21):**
  - Hoja de vida SIGEP
  - Certificados estudio y experiencia
  - RUT, cédula, cuenta bancaria
  - Antecedentes (disciplinarios, fiscales, judiciales, delitos sexuales)
  - Seguridad social (salud, pensión)
  - Certificado médico
  - Tarjeta profesional (si aplica)
  - REDAM, inhabilidades
- **Envía a:** Abogado Unidad (Etapa 3)

---

### **ETAPA 3 - Elaboración de Documentos Contractuales**
- **Responsable:** Abogado Unidad Solicitante
- **Rol:** `unidad_solicitante`
- **Documentos proyectados (8):**
  1. Invitación a Presentar Oferta (firma ordenador + supervisor)
  2. Solicitud de Contratación y Supervisión (firma ordenador + supervisor)
  3. Certificado de Idoneidad (firma ordenador)
  4. Estudios Previos definitivos (firma ordenador + supervisor)
  5. Análisis del Sector (firma supervisor)
  6. Aceptación de Oferta (firma contratista)
  7. Ficha BPIN (opcional)
  8. Excepción regla fiscal (opcional)
- **Envía a:** Abogado Unidad (Etapa 4)

---

### **ETAPA 4 - Consolidación del Expediente Precontractual**
- **Responsable:** Abogado Unidad Solicitante
- **Rol:** `unidad_solicitante`
- **Actividad:** Organizar carpeta completa con **35 documentos** (checklist)
- **Nota:** La carpeta debe llevar el nombre del contratista
- **Envía a:** Secretaría Jurídica

---

### **ETAPA 5 - Radicación en Secretaría Jurídica + Ajustado a Derecho**
- **Responsable:** Oficina de Radicación + Abogado Enlace
- **Rol:** `juridica`
- **Actividades:**
  1. Solicitud en SharePoint
  2. Asignar número proceso (CD-SP-XX-2026)
  3. Revisión lista de chequeo
  4. Ajustado a Derecho (si hay observaciones → devuelve)
  5. **Firma contrato:** Secretario Privado + Contratista + Abogado Enlace
- **Envía a:** SECOP

---

### **ETAPA 6 - Publicación y Firma en SECOP II**
- **Responsable:** Apoyo Estructuración
- **Rol:** `secop`
- **Actividades:**
  1. Cargar contrato en SECOP II
  2. Flujo aprobación:
     - Abogado enlace aprueba creación
     - **Contratista firma PRIMERO**
     - **Secretario Privado firma DESPUÉS**
  3. Descargar contrato electrónico
- **Envía a:** Descentralización (Planeación)

---

### **ETAPA 7 - Solicitud de RPC (Registro Presupuestal)**
- **Responsable:** Unidad de Descentralización
- **Rol:** `planeacion`
- **Actividades:**
  1. Imprimir contrato + ajustado a derecho
  2. Solicitud RPC firmada por Secretario de Planeación
  3. Radicar en Hacienda
  4. **Hacienda expide RPC** (Unidad de Presupuesto)
  5. **Paralelo:** Organizar expediente físico completo
- **Envía a:** Secretaría Jurídica

---

### **ETAPA 8 - Radicación Final y Número de Contrato**
- **Responsable:** Oficina de Radicación
- **Rol:** `juridica`
- **Actividades:**
  1. Con RPC listo → radicar expediente
  2. Asignar número de contrato
- **Envía a:** Unidad Solicitante (Etapa 9)

---

### **ETAPA 9 - ARL, Acta de Inicio e Inicio en SECOP** ✅ FINAL
- **Responsable:** Supervisor + Contratista
- **Rol:** `unidad_solicitante`
- **Actividades:**
  1. Solicitud ARL con número de contrato
  2. Elaboración Acta de Inicio
  3. Firma por partes
  4. Registro inicio ejecución en SECOP II
- **Estado:** ✅ **CONTRATO INICIADO**

---

## 📁 TIPOS DE DOCUMENTOS POR ETAPA

| Etapa | Tipos Permitidos |
|-------|------------------|
| 0 | `estudios_previos` (ÚNICO) |
| 1 | `paa`, `no_planta`, `paz_salvo_rentas`, `paz_salvo_contabilidad`, `compatibilidad_gasto`, `cdp`, `sigep` |
| 2 | `hoja_vida_sigep`, `certificado_estudio`, `certificado_experiencia`, `rut`, `cedula`, `cuenta_bancaria`, `antecedentes_*`, `seguridad_social_*`, `certificado_medico`, `tarjeta_profesional`, `redam` |
| 3 | `invitacion_oferta`, `solicitud_contratacion`, `certificado_idoneidad`, `estudios_previos_finales`, `analisis_sector`, `aceptacion_oferta`, `ficha_bpin`, `excepcion_fiscal` |
| 4 | `carpeta_precontractual` (checklist 35 docs) |
| 5 | `solicitud_sharepoint`, `numero_proceso`, `lista_chequeo`, `ajustado_derecho`, `contrato_firmado` |
| 6 | `contrato_secop`, `aprobacion_juridica`, `firma_contratista`, `firma_secretario`, `contrato_electronico` |
| 7 | `solicitud_rpc`, `firma_secretario_planeacion`, `radicado_hacienda`, `rpc_expedido`, `expediente_fisico` |
| 8 | `radicado_final`, `numero_contrato` |
| 9 | `solicitud_arl`, `acta_inicio`, `registro_secop_inicio` |

---

## ⚠️ REGLAS CRÍTICAS

1. **Etapa 0:** SOLO "Estudios Previos" - nada más
2. **Etapa 1:** CDP requiere Compatibilidad del Gasto primero
3. **Etapa 5:** Contrato firmado por 3 personas (Privado, Contratista, Jurídica)
4. **Etapa 6:** Contratista firma ANTES que Secretario Privado
5. **Etapa 7:** RPC es PRERREQUISITO para Etapa 8
6. **Etapa 8:** Número de contrato es PRERREQUISITO para Etapa 9

---

## 🔧 CORRECCIONES NECESARIAS

### ✅ 1. WorkflowSeeder.php
- [ ] Reducir de 16 etapas a 9 etapas (0-8)
- [ ] Ajustar descripciones según documento real
- [ ] Corregir ítems por etapa

### ✅ 2. UnidadController.php
- [ ] Cambiar validación de tipos de archivo para Etapa 0
- [ ] Permitir solo `estudios_previos` en Etapa 0
- [ ] Actualizar lógica de envío

### ✅ 3. unidad.blade.php
- [ ] Dropdown con solo "Estudios Previos" en Etapa 0
- [ ] Mostrar tipos correctos según etapa actual

### ✅ 4. WorkflowController.php
- [ ] No marcar `enviado=true` hasta confirmar envío
- [ ] Permitir edición mientras `enviado=false`

