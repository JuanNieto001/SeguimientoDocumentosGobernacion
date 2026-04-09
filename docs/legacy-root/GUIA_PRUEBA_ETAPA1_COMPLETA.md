# GUÍA DE PRUEBA COMPLETA - SISTEMA DE SOLICITUDES DOCUMENTALES ETAPA 1

## 🎯 RESUMEN DEL SISTEMA

Cuando Descentralización (Etapa 1) recibe un proceso, automáticamente se crean 7 solicitudes documentales a diferentes áreas:

1. **PAA** → compras@demo.com
2. **No Planta** → talento_humano@demo.com
3. **Paz y Salvo Rentas** → rentas@demo.com
4. **Paz y Salvo Contabilidad** → contabilidad@demo.com
5. **Compatibilidad del Gasto** → inversiones_publicas@demo.com
6. **SIGEP** → talento_humano@demo.com
7. **CDP** → presupuesto@demo.com (🔒 BLOQUEADO hasta que suban Compatibilidad)

---

## 📋 USUARIOS CREADOS

Todos tienen password: `password123`

| Email | Rol | Responsabilidad |
|-------|-----|-----------------|
| sistemas@demo.com | unidad_solicitante | Crea procesos, sube Estudios Previos |
| descentralizacion@demo.com | planeacion | Recibe y coordina solicitudes |
| compras@demo.com | compras | Sube PAA |
| talento_humano@demo.com | talento_humano | Sube No Planta y SIGEP |
| contabilidad@demo.com | contabilidad | Sube Paz y Salvo Contabilidad |
| rentas@demo.com | rentas | Sube Paz y Salvo Rentas |
| inversiones_publicas@demo.com | inversiones_publicas | Sube Compatibilidad del Gasto |
| presupuesto@demo.com | presupuesto | Sube CDP (solo cuando Compatibilidad esté subida) |

---

## 🚀 PASOS DE PRUEBA

### PASO 1: Crear proceso (Unidad Solicitante)
```
1. Abrir http://127.0.0.1:8000
2. Login: sistemas@demo.com / password123
3. Ir a "Nueva solicitud"
4. Llenar formulario:
   - Descripción: "Contratación prueba sistema solicitudes"
   - Secretaría: Seleccionar una (ej: Secretaría General)
   - Unidad: Seleccionar la correspondiente
5. Crear proceso
6. En la bandeja, encontrar el proceso
7. Subir archivo como "Estudios Previos"
8. Hacer clic en "Enviar a Descentralización"
9. Logout
```

### PASO 2: Recibir y ver solicitudes (Descentralización)
```
1. Login: descentralizacion@demo.com / password123
2. Ir a "Mi bandeja"
3. Ver el proceso recibido
4. Hacer clic en "Recibí"
   ✅ ESTO CREA AUTOMÁTICAMENTE LAS 7 SOLICITUDES
5. Observar la nueva sección "📋 Documentos Solicitados a Otras Áreas"
6. Ver que muestra: 0 de 7 documentos subidos
7. Ver que 6 documentos están con ⏳ (pendientes)
8. Ver que 1 documento (CDP) está con 🔒 (bloqueado)
9. Logout
```

### PASO 3: Subir PAA (Compras)
```
1. Login: compras@demo.com / password123
2. Ir a "Documentos Pendientes" (menú lateral)
3. Ver el proceso con solicitud pendiente
4. Hacer clic en "Ver Detalle"
5. Seleccionar tipo de archivo: "PAA"
6. Subir un archivo cualquiera
7. ✅ Ver mensaje de éxito
8. Logout
```

### PASO 4: Subir No Planta (Talento Humano)
```
1. Login: talento_humano@demo.com / password123
2. Ir a "Documentos Pendientes"
3. Ver el proceso (debe mostrar 2 solicitudes: No Planta y SIGEP)
4. Hacer clic en "Ver Detalle"
5. Subir "No Planta"
6. Logout
```

### PASO 5: Subir Paz y Salvo Contabilidad
```
1. Login: contabilidad@demo.com / password123
2. Ir a "Documentos Pendientes"
3. Ver Detalle del proceso
4. Subir "Paz y Salvo Contabilidad"
5. Logout
```

### PASO 6: Subir Paz y Salvo Rentas
```
1. Login: rentas@demo.com / password123
2. Ir a "Documentos Pendientes"
3. Subir "Paz y Salvo Rentas"
4. Logout
```

### PASO 7: Subir Compatibilidad del Gasto (DESBLOQUEA CDP)
```
1. Login: inversiones_publicas@demo.com / password123
2. Ir a "Documentos Pendientes"
3. Subir "Compatibilidad del Gasto"
4. ✅ ESTO DESBLOQUEA AUTOMÁTICAMENTE EL CDP PARA PRESUPUESTO
5. Logout
```

### PASO 8: Verificar desbloqueo de CDP (Presupuesto)
```
1. Login: presupuesto@demo.com / password123
2. Ir a "Documentos Pendientes"
3. Ver que CDP ahora muestra ⏳ (pendiente) en lugar de 🔒 (bloqueado)
4. Subir "CDP"
5. ✅ Ahora 6 de 7 documentos están subidos
6. Logout
```

### PASO 9: Subir SIGEP (Talento Humano - segunda solicitud)
```
1. Login: talento_humano@demo.com / password123
2. Ir a "Documentos Pendientes"
3. Ver Detalle del proceso
4. Subir "SIGEP"
5. ✅ Todas las solicitudes completadas!
6. Logout
```

### PASO 10: Verificar 7/7 y enviar (Descentralización)
```
1. Login: descentralizacion@demo.com / password123
2. Ir a "Mi bandeja"
3. Ver el proceso
4. Observar la sección de solicitudes:
   - ✅ Debe mostrar "7 de 7 documentos subidos"
   - ✅ Todos con checkmark verde ✅
   - ✅ Mensaje: "¡Todos los documentos están completos! Puedes enviar el proceso"
5. Hacer clic en "Enviar a la siguiente secretaría"
6. ✅ PROCESO COMPLETADO
```

---

## 🔍 VERIFICACIONES TÉCNICAS

### Base de Datos
```sql
-- Ver solicitudes creadas
SELECT * FROM proceso_documentos_solicitados ORDER BY id DESC LIMIT 7;

-- Ver estado de solicitudes de un proceso específico
SELECT 
    nombre_documento, 
    area_responsable_nombre, 
    estado, 
    puede_subir 
FROM proceso_documentos_solicitados 
WHERE proceso_id = 1;

-- Ver auditoría
SELECT * FROM proceso_auditorias ORDER BY created_at DESC LIMIT 20;
```

### Archivos de Logs
```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Buscar eventos de solicitudes
grep "solicitud_completada" storage/logs/laravel.log
grep "documento_desbloqueado" storage/logs/laravel.log
```

---

## ⚠️ ESCENARIOS DE ERROR A PROBAR

### 1. Intentar subir CDP antes de Compatibilidad
```
- Login como presupuesto@demo.com
- Intentar subir CDP cuando no está desbloqueado
- Resultado esperado: No debería poder (puede_subir = false)
```

### 2. Intentar editar después de enviar
```
- Como sistemas@demo.com, después de enviar en Etapa 0
- Intentar subir más archivos
- Resultado esperado: Formulario deshabilitado con mensaje amarillo
```

### 3. Intentar eliminar archivo después de enviar
```
- Como sistemas@demo.com, después de enviar
- Intentar eliminar archivos
- Resultado esperado: Error "No puedes eliminar archivos porque esta etapa ya fue enviada"
```

---

## 📊 INDICADORES DE ÉXITO

✅ **Sistema funcional completo:**
1. ✅ Etapa 0 solo acepta "Estudios Previos"
2. ✅ Post-envío bloquea edición/eliminación
3. ✅ Descentralización crea 7 solicitudes automáticamente al recibir
4. ✅ Cada área ve solo sus solicitudes pendientes
5. ✅ CDP bloqueado hasta que Compatibilidad se sube
6. ✅ Al subir Compatibilidad, CDP se desbloquea automáticamente
7. ✅ Descentralización ve progreso en tiempo real (X/7)
8. ✅ Solo puede enviar cuando 7/7 están completos
9. ✅ Auditoría registra: archivo_subido, solicitud_completada, documento_desbloqueado
10. ✅ Navegación: cada rol tiene "Documentos Pendientes" en menú lateral

---

## 🛠️ COMANDOS ÚTILES

```bash
# Ver servidor
php artisan serve

# Ver migraciones ejecutadas
php artisan migrate:status

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Ver usuarios y roles
php listar_roles.php

# Crear usuarios de prueba adicionales (si es necesario)
php crear_usuarios_etapa1.php
```

---

## 📝 DOCUMENTACIÓN TÉCNICA

### Archivos Clave Modificados:

1. **Migration**: `2026_02_19_144747_create_proceso_documentos_solicitados_table.php`
   - Tabla con 13 columnas: proceso_id, etapa_id, tipo_documento, estado, depende_de_solicitud_id, etc.

2. **Model**: `App/Models/ProcesoDocumentoSolicitado.php`
   - Método `marcarComoSubido()`: Actualiza estado y desbloquea dependientes
   - Método `habilitarDocumentosDependientes()`: Lógica de desbloqueo automático

3. **Controller**: `App/Http/Controllers/WorkflowController.php`
   - Método `recibir()`: Detecta etapa=1 y llama a `solicitarDocumentosEtapa1()`
   - Método `solicitarDocumentosEtapa1()`: Crea las 7 solicitudes con dependencias

4. **Controller**: `App/Http/Controllers/WorkflowFilesController.php`
   - Método `authorizeAreaOrAdmin()`: Permite subir si tiene solicitud pendiente
   - Método `store()`: Marca solicitud como 'subido', llama `habilitarDocumentosDependientes()`

5. **Controller**: `App/Http/Controllers/Area/SolicitudDocumentosController.php`
   - Vista de solicitudes pendientes por área
   - Detalle de proceso con documentos a subir

6. **Views**:
   - `resources/views/areas/planeacion.blade.php`: Muestra 7 solicitudes con progreso
   - `resources/views/areas/solicitudes.blade.php`: Listado de solicitudes por área
   - `resources/views/layouts/navigation.blade.php`: Menú con "Documentos Pendientes"

### Lógica de Dependencias:

```
Compatibilidad del Gasto (id: X)
    ↓
    depende_de_solicitud_id = X
    ↓
CDP (puede_subir: false inicialmente)
    ↓
    [Usuario sube Compatibilidad]
    ↓
    habilitarDocumentosDependientes(X)
    ↓
CDP.puede_subir = true ✅
```

---

## 🎓 CONCEPTOS CLAVE DEL SISTEMA

- **Solicitud Pendiente**: Documento que un área debe subir
- **Estado**: pendiente | subido | rechazado | observado
- **puede_subir**: Boolean que controla si el documento está desbloqueado
- **depende_de_solicitud_id**: FK a otra solicitud (para CDP → Compatibilidad)
- **area_responsable_rol**: Rol del usuario que debe subir el documento
- **autorización especial**: Áreas pueden subir a procesos fuera de su bandeja si tienen solicitud

---

¡Sistema listo para pruebas! 🚀
