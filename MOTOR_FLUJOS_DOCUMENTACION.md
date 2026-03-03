# Motor de Flujos Configurable por Secretaría

## Documentación Técnica Completa

---

## 1. Arquitectura del Sistema

### Diagrama Entidad-Relación

```
┌─────────────────┐       ┌──────────────────┐       ┌──────────────────────┐
│ catalogo_pasos   │       │ secretarias       │       │ users                │
│─────────────────│       │──────────────────│       │──────────────────────│
│ id (PK)          │       │ id (PK)           │       │ id (PK)              │
│ codigo (UNIQUE)  │       │ nombre            │       │ name, email          │
│ nombre           │       │ activo            │       │ secretaria_id (FK)   │
│ descripcion      │       │                   │       │ unidad_id (FK)       │
│ icono, color     │       └────────┬──────────┘       │ roles (spatie)       │
│ tipo             │                │                   └──────────┬───────────┘
│ activo           │                │ 1:N                          │
└────────┬─────────┘                ▼                              │
         │               ┌──────────────────┐                     │
         │               │ flujos            │                     │
         │               │──────────────────│                     │
         │               │ id (PK)           │                     │
         │               │ codigo (UNIQUE)   │                     │
         │               │ nombre            │                     │
         │               │ tipo_contratacion │                     │
         │               │ secretaria_id (FK)│──────┐              │
         │               │ version_activa_id │──┐   │              │
         │               │ activo            │  │   │              │
         │               └────────┬──────────┘  │   │              │
         │                        │ 1:N         │   │              │
         │                        ▼             │   │              │
         │               ┌──────────────────────┤   │              │
         │               │ flujo_versiones      │   │              │
         │               │──────────────────────│   │              │
         │               │ id (PK) ◄────────────┘   │              │
         │               │ flujo_id (FK)        │    │              │
         │               │ numero_version       │    │              │
         │               │ estado (enum)        │    │              │
         │               │ motivo_cambio        │    │              │
         │               │ creado_por (FK) ─────┼────┼──────────────┘
         │               │ publicada_at         │    │
         │               └────────┬─────────────┘    │
         │                        │ 1:N              │
         │                        ▼                  │
         │               ┌──────────────────────┐    │
         │    N:1         │ flujo_pasos          │    │
         └───────────────│──────────────────────│    │
                         │ id (PK)              │    │
                         │ flujo_version_id (FK)│    │
                         │ catalogo_paso_id (FK)│    │
                         │ orden                │    │
                         │ nombre_personalizado │    │
                         │ es_obligatorio       │    │
                         │ es_paralelo          │    │
                         │ dias_estimados       │    │
                         │ area_responsable_def │    │
                         └──┬─────┬─────┬───────┘    │
                            │     │     │             │
                   ┌────────┘     │     └────────┐    │
                   ▼              ▼              ▼    │
     ┌─────────────────┐ ┌──────────────┐ ┌─────────────────────┐
     │ flujo_paso_      │ │ flujo_paso_  │ │ flujo_paso_         │
     │ condiciones      │ │ documentos   │ │ responsables        │
     │─────────────────│ │──────────────│ │─────────────────────│
     │ campo, operador  │ │ nombre       │ │ rol                 │
     │ valor, accion    │ │ tipo_archivo │ │ user_id (FK)        │
     │ descripcion      │ │ obligatorio  │ │ unidad_id (FK)      │
     │ prioridad        │ │ max_archivos │ │ tipo (ejecutor...)   │
     └─────────────────┘ │ plantilla_url│ │ es_principal         │
                          └──────────────┘ └─────────────────────┘

     ┌──────────────────────┐       ┌──────────────────────────┐
     │ flujo_instancias      │       │ flujo_instancia_pasos     │
     │──────────────────────│  1:N  │──────────────────────────│
     │ id (PK)               │──────▶│ id (PK)                  │
     │ codigo_proceso (UNIQ) │       │ instancia_id (FK)        │
     │ flujo_id (FK)         │       │ flujo_paso_id (FK)       │
     │ flujo_version_id (FK) │       │ orden                    │
     │ secretaria_id (FK)    │       │ estado (enum)            │
     │ unidad_id (FK)        │       │ omitido_por_condicion    │
     │ objeto, monto         │       │ recibido_por/at          │
     │ estado (enum)         │       │ completado_por/at        │
     │ paso_actual_id (FK)   │       │ devuelto_por/at          │
     │ creado_por (FK)       │       │ motivo_devolucion        │
     └──────────────────────┘       └──────────┬───────────────┘
                                                │ 1:N
                                                ▼
                                     ┌──────────────────────────┐
                                     │ flujo_instancia_docs      │
                                     │──────────────────────────│
                                     │ instancia_paso_id (FK)   │
                                     │ flujo_paso_doc_id (FK)   │
                                     │ nombre_archivo           │
                                     │ ruta_archivo             │
                                     │ subido_por (FK)          │
                                     │ estado                   │
                                     └──────────────────────────┘
```

---

## 2. Tablas del Sistema (10 tablas)

| # | Tabla | Propósito |
|---|-------|-----------|
| 1 | `catalogo_pasos` | Catálogo global de pasos reutilizables |
| 2 | `flujos` | Flujos de contratación por Secretaría |
| 3 | `flujo_versiones` | Versionado de flujos (historial) |
| 4 | `flujo_pasos` | Pasos asignados a una versión de flujo |
| 5 | `flujo_paso_condiciones` | Condiciones opcionales por paso |
| 6 | `flujo_paso_responsables` | Responsables por paso y Secretaría |
| 7 | `flujo_paso_documentos` | Documentos requeridos por paso |
| 8 | `flujo_instancias` | Procesos en ejecución |
| 9 | `flujo_instancia_pasos` | Estado de cada paso en un proceso |
| 10 | `flujo_instancia_docs` | Documentos subidos en ejecución |

---

## 3. Relaciones entre Tablas

```
secretarias ──1:N──▶ flujos ──1:N──▶ flujo_versiones ──1:N──▶ flujo_pasos
                                                                    │
                                            ┌───────────────────────┼────────────────────┐
                                            ▼                       ▼                    ▼
                                   flujo_paso_condiciones  flujo_paso_documentos  flujo_paso_responsables

catalogo_pasos ──1:N──▶ flujo_pasos (reutilización)

flujos ──1:N──▶ flujo_instancias ──1:N──▶ flujo_instancia_pasos ──1:N──▶ flujo_instancia_docs
```

---

## 4. Buenas Prácticas

### 4.1 Principio "Solo Datos, No Código"

> **Regla de Oro:** Si una Secretaría cambia su flujo, SOLO se modifica la base de datos.

```sql
-- Ejemplo: Agregar un paso a Gobierno entre orden 3 y 4
-- 1. Reordenar pasos existentes:
UPDATE flujo_pasos SET orden = orden + 1
WHERE flujo_version_id = :version_id AND orden >= 4;

-- 2. Insertar nuevo paso:
INSERT INTO flujo_pasos (flujo_version_id, catalogo_paso_id, orden, ...)
VALUES (:version_id, :catalogo_paso_id, 4, ...);
```

### 4.2 Separación de Configuración vs Ejecución

| Capa | Tablas | Quién modifica |
|------|--------|----------------|
| **Catálogo** | `catalogo_pasos` | Admin general del sistema |
| **Configuración** | `flujos`, `flujo_versiones`, `flujo_pasos`, `flujo_paso_*` | Admin de la unidad solicitante |
| **Ejecución** | `flujo_instancias`, `flujo_instancia_pasos`, `flujo_instancia_docs` | Usuarios del proceso |

### 4.3 Seguridad por Roles

```
admin_general     → CRUD de todo (catálogo, flujos de cualquier secretaría)
admin_unidad      → CRUD de flujos SOLO de su secretaría
unidad_solicitante → Crear instancias, operar pasos asignados
juridica, secop... → Operar solo los pasos donde son área responsable
```

### 4.4 Inmutabilidad de Versiones Activas

- Una versión **activa** NO se puede modificar (los procesos en curso la usan).
- Para cambiar un flujo: crear nueva versión (borrador) → editar → publicar.
- Los procesos **ya iniciados** siguen usando la versión con la que arrancaron.

### 4.5 Evaluación de Condiciones

Las condiciones se evalúan al **iniciar** una instancia y determinan si un paso se omite o se refuerza:

```php
// Ejemplo: Si monto > 50M, notificar al jurídico
FlujoPasoCondicion::create([
    'flujo_paso_id' => $pasoJuridica->id,
    'campo'         => 'monto_estimado',
    'operador'      => '>',
    'valor'         => '50000000',
    'accion'        => 'notificar',
    'descripcion'   => 'Monto supera $50M - revisión especial',
]);
```

Operadores disponibles: `>`, `<`, `>=`, `<=`, `==`, `!=`, `in`, `not_in`, `between`, `contains`.

---

## 5. Versionado de Flujos

### Flujo de Trabajo para Cambiar un Flujo

```
    ┌─────────────────────┐
    │ 1. Crear nueva       │
    │    versión (borrador) │
    └─────────┬───────────┘
              ▼
    ┌─────────────────────┐
    │ 2. Editar pasos      │
    │    (agregar/quitar/   │
    │    reordenar)         │
    └─────────┬───────────┘
              ▼
    ┌─────────────────────┐
    │ 3. Publicar versión  │
    │    (se vuelve activa) │
    └─────────┬───────────┘
              ▼
    ┌─────────────────────┐
    │ 4. Versión anterior   │
    │    → archivada        │
    │    (procesos en curso  │
    │    no se afectan)      │
    └─────────────────────┘
```

### Ejemplo Práctico

```php
// El admin de la Sec. Gobierno quiere agregar "Viabilidad Económica" a su flujo

// 1. Crear nueva versión
$flujo = Flujo::where('codigo', 'CD_PN_GOBIERNO')->first();
$nuevaVersion = $flujo->crearVersion($user->id, 'Agregar viabilidad económica');

// 2. La nueva versión ya tiene los pasos de la anterior (duplicados)
// 3. Agregar el nuevo paso
FlujoPaso::create([
    'flujo_version_id'         => $nuevaVersion->id,
    'catalogo_paso_id'         => CatalogoPaso::where('codigo', 'VIAB_ECONOMICA')->first()->id,
    'orden'                    => 1, // después de DEF_NECESIDAD
    'area_responsable_default' => 'planeacion',
    'es_obligatorio'           => true,
]);

// 4. Reordenar los demás pasos...
// 5. Publicar
$nuevaVersion->publicar();
```

---

## 6. API Endpoints

### Catálogo
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/motor-flujos/catalogo-pasos` | Listar pasos del catálogo |
| POST | `/api/motor-flujos/catalogo-pasos` | Crear paso (admin) |

### Flujos
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/motor-flujos/secretarias/{id}/flujos` | Flujos de una Secretaría |
| POST | `/api/motor-flujos/flujos` | Crear flujo (admin unidad) |
| GET | `/api/motor-flujos/flujos/{id}/pasos` | Pasos del flujo activo |

### Versiones
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/motor-flujos/flujos/{id}/versiones` | Historial de versiones |
| POST | `/api/motor-flujos/flujos/{id}/versiones` | Crear nueva versión |
| POST | `/api/motor-flujos/versiones/{id}/publicar` | Publicar versión |

### Pasos (en versión borrador)
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/motor-flujos/versiones/{id}/pasos` | Agregar paso |
| PUT | `/api/motor-flujos/pasos/{id}` | Actualizar paso |
| DELETE | `/api/motor-flujos/pasos/{id}` | Eliminar paso |
| POST | `/api/motor-flujos/pasos/{id}/condiciones` | Agregar condición |
| POST | `/api/motor-flujos/pasos/{id}/documentos` | Agregar documento |
| POST | `/api/motor-flujos/pasos/{id}/responsables` | Agregar responsable |

### Instancias (procesos)
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/motor-flujos/instancias` | Crear e iniciar proceso |
| GET | `/api/motor-flujos/instancias/{id}` | Detalle del proceso |
| GET | `/api/motor-flujos/flujos/{id}/instancia-activa` | Instancia activa del usuario |
| POST | `/api/motor-flujos/instancias/{id}/avanzar` | Avanzar al siguiente paso |
| POST | `/api/motor-flujos/instancias/{id}/devolver` | Devolver a paso anterior |

---

## 7. Archivos Creados

```
database/
  migrations/
    2026_03_03_000001_create_motor_flujos_configurable.php   ← Migración (10 tablas)
  seeders/
    MotorFlujosSeeder.php                                     ← Datos de ejemplo
  schema/
    consultas_motor_flujos.sql                                ← 7 consultas SQL útiles

App/
  Models/
    CatalogoPaso.php          ← Catálogo de pasos
    Flujo.php                 ← Flujos por Secretaría
    FlujoVersion.php          ← Versionado
    FlujoPaso.php             ← Pasos en un flujo
    FlujoPasoCondicion.php    ← Condiciones por paso
    FlujoPasoResponsable.php  ← Responsables por paso
    FlujoPasoDocumento.php    ← Documentos por paso
    FlujoInstancia.php        ← Procesos en ejecución
    FlujoInstanciaPaso.php    ← Estado de pasos en ejecución
    FlujoInstanciaDoc.php     ← Documentos subidos

  Http/
    Controllers/Api/
      MotorFlujoController.php  ← Controlador API completo
    Middleware/
      CheckAdminUnidad.php      ← Middleware de autorización

routes/
  api.php                       ← Rutas registradas

bootstrap/
  app.php                       ← Middleware registrado

resources/js/
  WorkflowApp.jsx               ← Componente React completo
```

---

## 8. Ejecución

```bash
# 1. Ejecutar migración
php artisan migrate

# 2. Poblar datos de ejemplo
php artisan db:seed --class=MotorFlujosSeeder

# 3. Verificar tablas
php artisan tinker
>>> \App\Models\Flujo::with('versionActiva.pasos.catalogoPaso')->get();
```

---

## 9. Ejemplo: Agregar un Flujo para Nueva Secretaría (Solo BD)

```sql
-- Sin tocar código: crear flujo para Secretaría de Cultura

-- 1. Crear el flujo
INSERT INTO flujos (codigo, nombre, tipo_contratacion, secretaria_id, activo, created_at, updated_at)
VALUES ('CD_PN_CULTURA', 'CD Persona Natural - Sec. Cultura', 'cd_pn',
        (SELECT id FROM secretarias WHERE nombre LIKE '%Cultura%'), 1, NOW(), NOW());

-- 2. Crear versión
INSERT INTO flujo_versiones (flujo_id, numero_version, estado, publicada_at, created_at, updated_at)
VALUES (LAST_INSERT_ID(), 1, 'activa', NOW(), NOW(), NOW());

SET @version_id = LAST_INSERT_ID();
UPDATE flujos SET version_activa_id = @version_id WHERE codigo = 'CD_PN_CULTURA';

-- 3. Agregar pasos del catálogo en el orden deseado
INSERT INTO flujo_pasos (flujo_version_id, catalogo_paso_id, orden, area_responsable_default, activo, created_at, updated_at)
VALUES
    (@version_id, (SELECT id FROM catalogo_pasos WHERE codigo = 'DEF_NECESIDAD'), 0, 'unidad_solicitante', 1, NOW(), NOW()),
    (@version_id, (SELECT id FROM catalogo_pasos WHERE codigo = 'VAL_CONTRATISTA'), 1, 'unidad_solicitante', 1, NOW(), NOW()),
    (@version_id, (SELECT id FROM catalogo_pasos WHERE codigo = 'ELAB_DOCS'), 2, 'unidad_solicitante', 1, NOW(), NOW()),
    (@version_id, (SELECT id FROM catalogo_pasos WHERE codigo = 'RAD_JURIDICA'), 3, 'juridica', 1, NOW(), NOW()),
    (@version_id, (SELECT id FROM catalogo_pasos WHERE codigo = 'ARL_INICIO'), 4, 'unidad_solicitante', 1, NOW(), NOW());

-- ✅ Listo. La Secretaría de Cultura ya tiene su flujo de 5 pasos.
-- El sistema lo carga automáticamente para los usuarios de esa secretaría.
```
