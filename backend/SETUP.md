# Sistema de Seguimiento de Documentos - Gobernación

Sistema web de seguimiento y trazabilidad del proceso de contratación tipo "bandeja por áreas".

## 🚀 Características Principales

- **5 Modalidades de Contratación**: CD_PN, MC, SA, LP, CM
- **Flujo por Etapas**: Cada proceso avanza secuencialmente según su modalidad
- **Gestión de Archivos**: Subida, descarga y eliminación de documentos por etapa
- **Checklist Dinámico**: Validación de requisitos por etapa
- **Roles y Permisos**: Admin, Unidad Solicitante, Planeación, Hacienda, Jurídica, SECOP
- **Trazabilidad Completa**: Registro de fechas, usuarios y acciones

## 📋 Requisitos

- PHP >= 8.2
- Composer
- SQLite o MySQL
- Node.js y NPM (para frontend)

## 🔧 Instalación

### 1. Clonar o descargar el proyecto

```bash
cd SeguimientoDocumentosGobernacion/backend
```

### 2. Instalar dependencias

```bash
composer install
npm --prefix ../frontend install
```

### 3. Configurar entorno

El script de setup crea `.env` desde `.env.example` y genera `APP_KEY` si faltan.
Si usas MySQL, edita:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_bd
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 4. Inicializar base de datos

Ejecuta el script de inicialización (crea `.env`/`APP_KEY` si faltan, corre migraciones y seeders):

```powershell
.\scripts\local\setup\init.ps1
```

O manualmente:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### 5. Compilar assets frontend

```bash
npm --prefix ../frontend run dev
```

### 6. Iniciar servidor

```bash
php artisan serve
```

Accede a: `http://localhost:8000`

### 7. Dejar listo para servidor (deploy automatico)

Linux:

```bash
bash scripts/deploy/deploy-app.sh
```

Windows PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\deploy\deploy-app.ps1
```

Guia completa:

- [scripts/deploy/DEPLOY_SERVIDOR.md](scripts/deploy/DEPLOY_SERVIDOR.md)

## 👥 Usuarios de Prueba

Se crean dos grupos de usuarios (segun los seeders):

### UsuariosPruebaSeeder (password: Caldas2025*)

| Rol | Email | Password |
|-----|-------|----------|
| Admin general | admin@caldas.gov.co | Caldas2025* |
| Admin secretaria Planeacion | admin.planeacion@caldas.gov.co | Caldas2025* |
| Admin secretaria Hacienda | admin.hacienda@caldas.gov.co | Caldas2025* |
| Admin secretaria Juridica | admin.juridica@caldas.gov.co | Caldas2025* |
| Consulta | consulta1@caldas.gov.co | Caldas2025* |

Otros usuarios (profesionales, revisores y consulta) estan definidos en UsuariosPruebaSeeder.

### AreaUsersSeeder (password: 12345)

| Rol/Area | Email | Password |
|----------|-------|----------|
| Jefe Unidad Sistemas (unidad solicitante) | jefe.sistemas@demo.com | 12345 |
| Planeacion (Descentralizacion) | descentralizacion@demo.com | 12345 |
| Rentas | rentas@demo.com | 12345 |
| Contabilidad | contabilidad@demo.com | 12345 |
| Presupuesto | presupuesto@demo.com | 12345 |
| Compras | compras@demo.com | 12345 |
| Talento Humano | talentohumano@demo.com | 12345 |
| SECOP | secop@demo.com | 12345 |
| Radicacion | radicacion@demo.com | 12345 |

Para probar el panel ejecutivo de jefe de unidad, asigna el rol `jefe_unidad` a un usuario con unidad_id.

## 🧭 Dashboards por rol

- Panel de Control (ejecutivo): roles con dashboard_scope global/secretaria/unidad (admin, admin_general, gobernador, secretario, jefe_unidad).
- Dashboard personal: roles de area documentos (compras, talento_humano, rentas, contabilidad, inversiones_publicas, presupuesto, radicacion) y otros.
- Si un usuario tiene varios roles, se usa el alcance mas alto: global > secretaria > unidad > propios.

## 📁 Estructura del Proyecto

```
App/
├── Http/
│   ├── Controllers/
│   │   ├── Area/              # Controladores por área
│   │   ├── ProcesoController.php
│   │   ├── WorkflowController.php
│   │   └── WorkflowFilesController.php
│   └── Middleware/
├── Models/
└── Providers/

database/
├── migrations/
│   ├── create_workflow_tables.php
│   └── create_proceso_etapa_archivos_table.php
└── seeders/
    ├── RolesAndPermissionsSeeder.php
    ├── AdminUserSeeder.php
    ├── AreaUsersSeeder.php
    └── WorkflowSeeder.php

resources/
└── views/
    ├── areas/              # Vistas de bandejas por área
    ├── procesos/
    └── layouts/

routes/
└── web.php
```

## 🔄 Flujo de Trabajo

### 1. Crear Solicitud (Unidad Solicitante)

- Usuario con rol "Unidad Solicitante" crea un nuevo proceso
- Selecciona modalidad de contratación
- Ingresa código, objeto y descripción

### 2. Subir Archivos Iniciales (Unidad)

- Unidad sube archivos requeridos:
  - ✅ Borrador de Estudios Previos
  - ✅ Formato de Necesidades
  - 📎 Anexos/Cotizaciones (opcional)

### 3. Enviar a Siguiente Etapa

- Una vez subidos los archivos requeridos, Unidad envía
- El proceso avanza automáticamente a la siguiente etapa según el workflow

### 4. Proceso en Otras Áreas

- Cada área recibe el proceso en su bandeja
- Marca "Recibí" y completa su checklist
- Envía a la siguiente etapa

## 🗃️ Base de Datos

### Tablas Principales

- `workflows`: Tipos de contratación
- `etapas`: Etapas por workflow
- `procesos`: Solicitudes creadas
- `proceso_etapas`: Instancias de etapas por proceso
- `proceso_etapa_archivos`: Archivos subidos por etapa
- `proceso_etapa_checks`: Checklist por etapa

### Relaciones

```
workflows (1) → (N) etapas → (1) next_etapa_id
procesos (1) → (N) proceso_etapas → (1) etapas
proceso_etapas (1) → (N) proceso_etapa_archivos
proceso_etapas (1) → (N) proceso_etapa_checks → (1) etapa_items
```

## 📝 Archivos

### Tipos de Archivos Soportados

- `borrador_estudios_previos`: Borrador de Estudios Previos (requerido en Unidad)
- `formato_necesidades`: Formato de Necesidades (requerido en Unidad)
- `anexo`: Documentos adicionales
- `cotizacion`: Cotizaciones de proveedores
- `otro`: Otros documentos

### Ubicación de Archivos

```
storage/app/public/procesos/{proceso_id}/etapa_{etapa_id}/{archivo}
```

Los archivos son accesibles públicamente a través de:

```
http://localhost:8000/storage/procesos/{proceso_id}/etapa_{etapa_id}/{archivo}
```

## 🔒 Seguridad y Permisos

### Por Rol

- **Admin**: Acceso total a todo el sistema
- **Unidad Solicitante**: Crea solicitudes, sube archivos iniciales
- **Áreas** (Planeación, Hacienda, Jurídica, SECOP): Solo ve procesos en su bandeja actual

### Validaciones de Archivos

- Solo el área actual puede subir/eliminar archivos
- Admin puede eliminar cualquier archivo
- Todos los usuarios autorizados pueden descargar
- Tamaño máximo: 10MB por archivo

## 🚧 Estado Actual

### ✅ Implementado

- Estructura de workflows y etapas
- Creación de solicitudes
- Avance por etapas con validaciones
- Sistema de archivos completo
- Roles y permisos por área
- Migraciones y seeders

### ⏳ Pendiente

- Validación de flujos con abogados
- Definición final de checklists por etapa
- Frontend mejorado (UI/UX)
- Notificaciones y alertas
- Reportes y dashboard
- Historial de auditoría detallado

## 🛠️ Desarrollo

### Resetear Base de Datos

```bash
php artisan migrate:fresh --seed
```

### Limpiar Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Ver Rutas

```bash
php artisan route:list
```

## 📞 Soporte

Para dudas o problemas técnicos, contacta al equipo de desarrollo.

---

**Versión**: 1.0.0  
**Fecha**: Febrero 2026  
**Estado**: Desarrollo
