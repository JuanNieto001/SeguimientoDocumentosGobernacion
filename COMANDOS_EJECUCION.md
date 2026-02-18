# ⚡ COMANDOS DE EJECUCIÓN INMEDIATA

## 🎯 OBJETIVO
Aplicar todas las correcciones y mejoras al backend en el menor tiempo posible.

---

## ⚠️ PASO 1: ELIMINAR MIGRACIONES DUPLICADAS (CRÍTICO)

Ejecuta estos comandos en PowerShell desde la raíz del proyecto:

```powershell
# Verificar que existen los duplicados
Get-ChildItem database\migrations\*000006*.php

# Eliminar duplicados (conservar solo las versiones 000007)
Remove-Item database\migrations\2026_02_17_000006_create_alertas_table.php
Remove-Item database\migrations\2026_02_17_000006_create_modificaciones_contractuales_table.php

# Verificar que se eliminaron
Get-ChildItem database\migrations\*000006*.php
# Debe retornar: vacío (ningún archivo)
```

---

## ✅ PASO 2: EJECUTAR NUEVAS MIGRACIONES

```powershell
# Opción A: Si NO tienes datos importantes (recomendado para desarrollo)
php artisan migrate:fresh --seed

# Opción B: Si YA tienes datos que no quieres perder
php artisan migrate
php artisan db:seed --class=WorkflowSeeder

# Verificar que todo se migró correctamente
php artisan migrate:status
```

---

## 🔍 PASO 3: VERIFICAR QUE TODO FUNCIONA

```powershell
# 1. Verificar que las nuevas columnas existen en workflows
php artisan tinker
```

Dentro de Tinker:

```php
// Verificar columnas de workflows
\DB::table('workflows')->first();
// Debe mostrar: requiere_viabilidad_economica_inicial, requiere_estudios_previos_completos, observaciones

// Verificar columna paa_id en procesos
\DB::table('procesos')->first();
// Debe mostrar: paa_id

// Verificar que los modelos cargan correctamente
\App\Models\Workflow::with('etapas')->first();
\App\Models\Proceso::with('workflow', 'etapaActual')->first();

// Salir de Tinker
exit;
```

---

## 📦 PASO 4: VERIFICAR MODELOS CREADOS

```powershell
# Listar todos los modelos creados
Get-ChildItem App\Models\*.php | Select-Object Name
```

Deberías ver:

```
Alerta.php
Etapa.php
EtapaItem.php
ModificacionContractual.php
PlanAnualAdquisicion.php
Proceso.php
ProcesoAuditoria.php
ProcesoEtapa.php
ProcesoEtapaArchivo.php
ProcesoEtapaCheck.php
TipoArchivoPorEtapa.php
User.php
Workflow.php
```

Total: **13 modelos** (antes tenías 2)

---

## 🚀 PASO 5: PROBAR RELACIONES ELOQUENT

```powershell
php artisan tinker
```

```php
// Probar relaciones de Workflow
$workflow = \App\Models\Workflow::first();
$workflow->etapas; // Debe retornar colección de etapas
$workflow->procesos; // Debe retornar colección de procesos
$workflow->primeraEtapa(); // Debe retornar la etapa con menor orden

// Probar relaciones de Proceso
$proceso = \App\Models\Proceso::first();
$proceso->workflow; // Debe retornar el workflow
$proceso->etapaActual; // Debe retornar la etapa actual
$proceso->creador; // Debe retornar el usuario que lo creó
$proceso->procesoEtapas; // Debe retornar las instancias de etapas
$proceso->archivos; // Debe retornar los archivos
$proceso->esEnCurso(); // Debe retornar true o false
$proceso->avanzarEtapa(); // Avanza a la siguiente etapa

// Probar relaciones de Etapa
$etapa = \App\Models\Etapa::first();
$etapa->workflow; // Debe retornar el workflow
$etapa->items; // Debe retornar los items (checklist)
$etapa->siguienteEtapa; // Debe retornar la siguiente etapa o null
$etapa->esUnidadSolicitante(); // Debe retornar true o false
$etapa->esPrimera(); // Debe retornar true o false
$etapa->esUltima(); // Debe retornar true o false

// Probar auditoría (crear registro de prueba)
\App\Models\ProcesoAuditoria::registrar(
    1, // proceso_id
    'test',
    'Prueba de auditoría',
    1 // etapa_id
);

// Verificar que se creó
\App\Models\ProcesoAuditoria::latest()->first();

// Probar alertas (crear alerta de prueba)
\App\Models\Alerta::crear(
    1, // user_id
    'test',
    'Prueba de alerta',
    'Este es un mensaje de prueba',
    1, // proceso_id
    'media'
);

// Verificar que se creó
\App\Models\Alerta::latest()->first();

// Salir
exit;
```

---

## 🏃 PASO 6: INICIAR SERVIDOR Y PROBAR

```powershell
# Iniciar servidor
php artisan serve

# Debe mostrar:
# Starting Laravel development server: http://127.0.0.1:8000
```

Abre tu navegador en `http://127.0.0.1:8000` y verifica que:
1. ✅ El sistema carga sin errores
2. ✅ Puedes hacer login
3. ✅ Puedes crear un nuevo proceso
4. ✅ Los workflows se cargan correctamente

---

## 📊 PASO 7: VERIFICAR DATOS SEEDED

```powershell
php artisan tinker
```

```php
// Verificar workflows
\App\Models\Workflow::count(); // Debe retornar 5 (CD_PN, MC, SA, LP, CM)

// Verificar etapas por workflow
\App\Models\Workflow::withCount('etapas')->get()->pluck('etapas_count', 'codigo');
// Debe mostrar cuántas etapas tiene cada workflow

// Verificar PAA
\App\Models\PlanAnualAdquisicion::count(); // Debe retornar 5 (ejemplos del seeder)

// Verificar usuarios por rol
\Spatie\Permission\Models\Role::withCount('users')->get()->pluck('users_count', 'name');
// Debe mostrar cuántos usuarios hay por cada rol

// Verificar tipos de archivo por etapa
\App\Models\TipoArchivoPorEtapa::count();
// Debe retornar un número significativo (decenas)

exit;
```

---

## 🔧 PASO 8: LIMPIAR CACHÉ (OPCIONAL)

```powershell
# Limpiar todos los cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar autoloader
composer dump-autoload

# Recrear configuración en caché (solo en producción)
# php artisan config:cache
# php artisan route:cache
```

---

## 📝 PASO 9: VERIFICAR ESTRUCTURA FINAL

```powershell
# Ver estructura de App/Models
tree /F App\Models

# Ver estructura de database/migrations (últimas 5)
Get-ChildItem database\migrations\*.php | Sort-Object Name | Select-Object -Last 5
```

---

## ✅ CHECKLIST DE VERIFICACIÓN

Marca cada item conforme lo vayas completando:

- [ ] Eliminé migraciones duplicadas (000006)
- [ ] Ejecuté `php artisan migrate:fresh --seed`
- [ ] Verifiqué en Tinker que las columnas existen
- [ ] Verifiqué que tengo 13 modelos en App/Models
- [ ] Probé relaciones Eloquent en Tinker
- [ ] Creé registro de auditoría de prueba
- [ ] Creé alerta de prueba
- [ ] Inicié servidor con `php artisan serve`
- [ ] El sistema carga sin errores 500
- [ ] Puedo hacer login
- [ ] Puedo crear un proceso
- [ ] Los workflows se cargan correctamente
- [ ] Verifiqué conteo de workflows (5)
- [ ] Verifiqué conteo de PAA (5)
- [ ] Limpié cachés

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### Error: "SQLSTATE[42S22]: Column not found"

**Problema**: Las nuevas columnas no existen en la BD.

**Solución**:
```powershell
php artisan migrate:fresh --seed
```

### Error: "Class not found"

**Problema**: Autoloader no encuentra los nuevos modelos.

**Solución**:
```powershell
composer dump-autoload
```

### Error: "Base table or view not found"

**Problema**: Las tablas no se crearon correctamente.

**Solución**:
```powershell
# Verificar conexión a BD
php artisan db:show

# Re-crear todo
php artisan migrate:fresh --seed
```

### Error: "Foreign key constraint fails"

**Problema**: Orden incorrecto de migraciones o seeders.

**Solución**:
```powershell
php artisan migrate:fresh
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=AreaUsersSeeder
php artisan db:seed --class=WorkflowSeeder
php artisan db:seed --class=PAASeeder
php artisan db:seed --class=TiposArchivoSeeder
```

### Error: "Duplicate entry for key"

**Problema**: Intentas seedear datos que ya existen.

**Solución**:
```powershell
php artisan migrate:fresh --seed
```

---

## 📊 RESULTADOS ESPERADOS

Al finalizar estos pasos, deberías tener:

| Item | Antes | Después |
|------|-------|---------|
| Modelos Eloquent | 2 | 13 |
| Migraciones | 15 | 17 |
| Columnas en workflows | 3 | 6 |
| Columnas en procesos | 7 | 8 |
| Relaciones configuradas | 0 | 50+ |
| Scopes útiles | 0 | 30+ |
| Métodos helper | 5 | 40+ |

---

## 🎉 ¡ÉXITO!

Si completaste todos los pasos y el checklist, tu backend ahora tiene:

✅ Base de datos completa y correcta  
✅ 13 modelos Eloquent con relaciones  
✅ Validaciones automáticas  
✅ Sistema de auditoría preparado  
✅ Sistema de alertas preparado  
✅ Gestión de PAA  
✅ Gestión de archivos  
✅ Flujos de workflows completos  

---

## 📚 PRÓXIMOS PASOS

Ahora que tienes la base sólida:

1. **Lee** [PLAN_IMPLEMENTACION_PRIORITARIO.md](PLAN_IMPLEMENTACION_PRIORITARIO.md)
2. **Implementa** Fase 1: Agregar Etapa 0 a workflows
3. **Crea** los controladores faltantes (PAA, Planeación, Hacienda, Jurídica, SECOP)
4. **Implementa** auditoría en controladores existentes
5. **Crea** AlertaService y comando de alertas automáticas
6. **Desarrolla** DashboardController con indicadores

---

**Tiempo estimado de ejecución**: 15-20 minutos  
**Dificultad**: Baja (solo comandos)  
**Prerequisitos**: PowerShell, Composer, PHP, Laravel instalados

**Generado por**: GitHub Copilot (Claude Sonnet 4.5)  
**Fecha**: 17 de Febrero de 2026
