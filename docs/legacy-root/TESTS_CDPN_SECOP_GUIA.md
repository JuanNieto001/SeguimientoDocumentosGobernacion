# 🧪 GUÍA DE PRUEBAS E2E - FLUJO CD-PN CON SECOP

## 📋 Descripción General

Tests end-to-end completosдля el flujo de **Contratación Directa - Persona Natural (CD-PN)** usando la **cédula SECOP real: 1053850113**.

## ✅ Tests Implementados

### CDPN-001: Crear proceso con cédula SECOP
- ✓ Login como Admin
- ✓ Crear proceso CD-PN
- ✓ Usar cédula 1053850113 (datos reales en SECOP)
- ✓ Capturar ID del proceso creado

### CDPN-002: Gestión de archivos
- ✓ Login como Planeación
- ✓ Recibir proceso en bandeja
- ✓ Subir documento PDF de prueba
- ✓ Verificar versiones de archivos

### CDPN-003: Consultar SECOP
- ✓ Login con permisos SECOP
- ✓ Consultar contratos con cédula 1053850113
- ✓ Verificar resultados (API SECOP real)
- ✓ Probar filtros disponibles

### CDPN-004: Permisos exportación Admin
- ✓ Verificar exportación CSV en PAA
- ✓ Verificar exportación PDF en PAA
- ✓ Confirmar acceso completo a archivos de proceso

### CDPN-005: Flujo completo inicio a fin
- ✓ **Etapa 0**: Crear proceso (Admin)
- ✓ **Etapa 1**: Recibir + Aprobar (Planeación)
- ✓ **Etapa 2**: Recibir + Emitir CDP + Aprobar (Hacienda)
- ✓ **Etapa 3**: Recibir + Aprobar (Jurídica)
- ✓ **Etapa 4-9**: Recibir + Finalizar (SECOP)
- ✓ Screenshots en cada etapa

## 🚀 Cómo Ejecutar

### Opción 1: Doble clic en BAT (Recomendado)
```
ABRIR_PLAYWRIGHT_CDPN.bat
```
Abre Playwright UI con limpieza automática de evidencias.

### Opción 2: Línea de comandos
```bash
# Limpiar evidencias anteriores
del test-results\*.png
del test-results\*.webm

# Abrir UI de Playwright
npx playwright test tests/e2e/flujo-cdpn-completo.spec.js --ui
```

### Opción 3: Ejecutar sin UI (headless)
```bash
npx playwright test tests/e2e/flujo-cdpn-completo.spec.js
```

## 📊 Evidencias Generadas

Ubicación: `test-results/`

### Screenshots por Test:
- `cdpn-001-proceso-creado-secop.png` - Proceso creado con cédula SECOP
- `cdpn-002-gestion-archivos.png` - Gestión de archivos
- `cdpn-003-secop-completo.png` - Consulta SECOP
- `cdpn-004-exportar-admin.png` - Permisos Admin
- `cdpn-005-e0-creado.png` - Etapa 0: Proceso creado
- `cdpn-005-e1-planeacion.png` - Etapa 1: Planeación
- `cdpn-005-e2-hacienda.png` - Etapa 2: Hacienda
- `cdpn-005-e3-juridica.png` - Etapa 3: Jurídica
- `cdpn-005-e4-secop.png` - Etapa 4: SECOP
- `cdpn-005-flujo-completo-final.png` - Flujo completo finalizado

### Videos (si hay fallos):
- `cdpn-*.webm` - Grabación completa del test

## 🔑 Datos de Prueba

### Cédula SECOP (CRÍTICA):
```
1053850113
```
⚠️ **Esta cédula tiene contratos reales en SECOP** (años 2025 y 2026).  
NO cambiar por otra cédula o los tests de SECOP fallarán.

### Usuarios del Sistema:

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| Admin | admin@demo.com | 12345678 | Administrador |
| Planeación | planeacion@demo.com | 12345 | Planeación |
| Hacienda | hacienda@demo.com | 12345 | Hacienda |
| Jurídica | juridica@demo.com | 12345 | Jurídica |
| SECOP | secop@demo.com | 12345 | SECOP |

## 🛠️ Funcionalidad del Sistema Usada

### Rutas por Área:

#### Planeación (`/planeacion`)
- `POST /workflow/procesos/{id}/recibir` - Recibir proceso
- `POST /workflow/procesos/{id}/checks/{id}/toggle` - Marcar checks
- `POST /planeacion/procesos/{id}/aprobar` - Aprobar
- `POST /workflow/procesos/{id}/enviar` - Enviar a siguiente etapa

#### Hacienda (`/hacienda`)
- `POST /workflow/procesos/{id}/recibir` - Recibir proceso
- `POST /hacienda/procesos/{id}/cdp` - Emitir CDP
- `POST /hacienda/procesos/{id}/rp` - Emitir RP
- `POST /hacienda/procesos/{id}/aprobar` - Aprobar viabilidad
- `POST /workflow/procesos/{id}/enviar` - Enviar siguiente

#### Jurídica (`/juridica`)
- `POST /workflow/procesos/{id}/recibir` - Recibir proceso
- `POST /juridica/procesos/{id}/verificar-contratista` - Verificar contratista
- `POST /juridica/procesos/{id}/ajustado` - Emitir ajustado de derecho
- `POST /juridica/procesos/{id}/polizas` - Aprobar pólizas
- `POST /workflow/procesos/{id}/enviar` - Enviar siguiente

#### SECOP (`/secop`)
- `POST /workflow/procesos/{id}/recibir` - Recibir proceso
- `POST /secop/procesos/{id}/publicar` - Publicar en SECOP
- `POST /secop/procesos/{id}/contrato` - Registrar contrato
- `POST /secop/procesos/{id}/acta-inicio` - Registrar acta
- `POST /secop/procesos/{id}/cerrar` - Cerrar en SECOP

#### Gestión de Archivos:
- `POST /workflow/procesos/{id}/archivos` - Upload
- `GET /workflow/archivos/{id}/descargar` - Download
- `DELETE /workflow/archivos/{id}` - Delete
- `POST /workflow/archivos/{id}/reemplazar` - Reemplazar (nueva versión)
- `POST /workflow/archivos/{id}/aprobar` - Aprobar documento
- `POST /workflow/archivos/{id}/rechazar` - Rechazar documento

#### Exportación (Admin):
- `GET /paa/exportar/csv` - Exportar PAA a CSV
- `GET /paa/exportar/pdf` - Exportar PAA a PDF

## ⚙️ Configuración de Tests

### Archivo: `playwright.config.js`
```javascript
{
  use: {
    headless: false,      // Ver navegador (para debug)
    video: 'retain-on-failure',  // Video solo si falla
    screenshot: 'on',     // Screenshots siempre
    trace: 'on',          // Trace para debug
  }
}
```

## 🐛 Solución de Problemas

### El test CDPN-001 falla en creación
**Problema**: No encuentra select de flujo  
**Solución**: Verificar que exista flujo "CD-PN" o "Persona Natural" en DB
```bash
php verificar_flujos.php
```

### El test CDPN-003 no encuentra contratos SECOP
**Problema**: API SECOP no responde o cédula incorrecta  
**Solución**: 
1. Verificar que la cédula sea exactamente `1053850113`
2. Verificar config SECOP en `.env`:
```env
SECOP_API_URL=https://www.datos.gov.co/resource/jbjy-vk9h.json
SECOP_ENTIDAD_NIT=890001097
```

### El test CDPN-005 se queda en una etapa
**Problema**: Falta marcar checks o aprobar archivos  
**Solución**:
- Verificar que todos los checks requeridos estén marcados
- Verificar que no haya archivos en estado "pendiente" o "rechazado"

### Login falla con contraseña incorrecta
**Problema**: Password de admin incorrecto  
**Solución**: Admin usa `12345678`, otros usan `12345`

## 📝 Notas Técnicas

1. **LoginHelper**: Se usa estáticamente (no instanciar)
   ```javascript
   // ✅ CORRECTO
   await LoginHelper.loginAsPlaneacion(page);
   
   // ❌ INCORRECTO
   const login = new LoginHelper();
   await login.loginAsPlaneacion(page);
   ```

2. **Archivos de prueba**: Se crean automáticamente en `test-results/archivos-prueba/`

3. **Timeouts**: Se usan `waitForTimeout()` para dar tiempo al servidor

4. **API SECOP**: Es API REAL, no mock. Requiere internet.

5. **Persistencia**: Los procesos creados en tests QUEDAN en la DB.  
   Para limpiar:
   ```sql
   DELETE FROM procesos WHERE nombre LIKE '%Test%E2E%';
   ```

## 🎯 Próximos Pasos

- [ ] Agregar test de rechazo de proceso (retroceder etapa)
- [ ] Agregar test de aprobación/rechazo de documentos
- [ ] Agregar test de notificaciones
- [ ] Agregar test de auditoría
- [ ] Mock de SECOP para tests offline

## 📞 Soporte

Para dudas o problemas, revisar:
- `PLAYWRIGHT_GUIA.md` - Guía general de Playwright
- `PLAN_PRUEBAS_PLAYWRIGHT_COMPLETO.md` - Plan completo de pruebas
- `FLUJO_CD_PN_DOCUMENTACION.md` - Documentación del flujo CD-PN

---
**Última actualización**: 2026-04-08  
**Autor**: Sistema de Seguimiento de Documentos - Gobernación de Caldas
