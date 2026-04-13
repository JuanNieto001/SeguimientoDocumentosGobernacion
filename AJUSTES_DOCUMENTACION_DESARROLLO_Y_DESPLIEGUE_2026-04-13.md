# Ajustes para DOCUMENTACION_DESARROLLO_Y_DESPLIEGUE

Fecha base de cambios: 2026-04-13
Alcance: Ajustes ya implementados en codigo para evitar inconsistencias entre documento y sistema.

## 1) Requisitos eliminados (mantener fuera del documento)
- RF-18 (MFA opcional interno).
- RF-27 (supervision/pagos completos en esta fase).
- RNF-19 (limite de sesiones simultaneas como requisito independiente; queda absorbido por control de sesion implementado).
- CU-04, CU-20, CU-22, CU-28.
- RN-21, RN-26, RN-27.

## 2) Cambios que SI deben quedar como implementados (SI ESTA)

### Seguridad, autenticacion y sesiones
- Cierre automatico por inactividad configurable con valor por defecto de 30 minutos.
- Politica de contrasena robusta (minimo 8, mayuscula, numero, simbolo y verificacion de credenciales comprometidas).
- Bloqueo por intentos fallidos configurable (por defecto 5 intentos, ventana configurable).
- Bloqueo de ingreso para cuentas inactivas con registro en auditoria.
- Limite de sesiones simultaneas configurable y cierre de sesiones excedentes al iniciar sesion.
- Cierre forzado de sesiones activas por administrador desde panel de usuarios.
- Expiracion del token de recuperacion de contrasena configurable por entorno.

### Auditoria y administracion
- Registro de eventos de autenticacion ampliado: login exitoso/fallido, logout, reset/cambio de contrasena, cuenta desactivada, cierre forzado de sesion.
- Registro de eventos administrativos de cuentas: creacion, actualizacion, activacion, desactivacion, eliminacion y cambio de roles.
- Exportacion CSV de eventos de autenticacion para auditoria.
- Alias administrativo en ingles para navegacion y soporte documental: /admin/users (redireccion a /admin/usuarios).

### Reportes y consultas
- Exportacion PDF real implementada.
- Exportacion XLSX real implementada.
- Exportaciones disponibles para: estado general, por dependencia, actividad por actor, certificados por vencer y eficiencia.
- Auditoria por proceso exportable en PDF.

### Gestion documental y validaciones
- Restriccion de carga/reemplazo de archivos a formatos PDF, DOCX y XLSX.
- Bloqueo de avance de etapa si existen documentos aprobados con vigencia vencida en la etapa actual.
- Alerta automatica para certificados vencidos (ademas de proximos a vencer).

### Operacion, disponibilidad y recuperacion
- Health checks periodicos de sistema (DB, cache, ruta de backups, concurrencia activa, tiempo de respuesta).
- Registro historico de salud del sistema para calculo de disponibilidad.
- Comando de reporte de disponibilidad contra objetivo configurado (95% por defecto).
- Respaldo automatico de base de datos y archivos documentales.
- Depuracion automatica de respaldos por retencion configurable.
- Flujo de restauracion con modo simulacion y modo forzado.

## 3) Ajustes de redaccion recomendados en el documento
- Donde diga "exportacion PDF/Excel pendiente o placeholder", cambiar a "exportacion PDF y XLSX implementada".
- Donde diga "timeout por defecto 120 minutos", cambiar a "timeout por defecto 30 minutos (configurable)".
- Donde diga "sin control de sesiones simultaneas", cambiar a "control configurable de sesiones simultaneas y cierre forzado por administrador".
- Donde diga "solo /admin/users", cambiar por "panel principal en /admin/usuarios con alias /admin/users".
- Donde diga "validacion de vigencias solo por alerta", cambiar por "alerta + bloqueo de avance si hay vigencias vencidas en etapa actual".
- Donde diga "sin backup/recuperacion implementada", cambiar por "backup, prune, health-check, availability-report y restore implementados".

## 4) Comandos operativos que deben aparecer en despliegue
Ejecutar en backend:

```bash
php artisan migrate --force
php artisan system:health-check
php artisan system:availability-report --days=30
php artisan system:backup
php artisan system:backup-prune
php artisan system:restore <backup_dir>         # simulacion
php artisan system:restore <backup_dir> --force # restauracion real
```

Scheduler (requerido):
- alertas:generar -> cada hora.
- contratos-aplicaciones:sync-secop -> cada 6 horas.
- system:health-check -> cada 5 minutos.
- system:backup -> diario 02:00.
- system:backup-prune -> diario 02:30.

## 5) Notas de infraestructura para no generar errores
- Para backups MySQL se requiere binario mysqldump disponible en servidor.
- Para restore MySQL se requiere binario mysql disponible en servidor.
- El directorio de backups por defecto es backend/storage/backups (configurable por entorno).
- Se debe mantener SESSION_DRIVER=database para control de sesiones activas y cierre forzado.

## 6) Variables de entorno nuevas a documentar
- SESSION_MAX_CONCURRENT
- AUTH_MAX_LOGIN_ATTEMPTS
- AUTH_LOCKOUT_SECONDS
- AUTH_PASSWORD_RESET_EXPIRE
- OPS_TARGET_AVAILABILITY
- OPS_RTO_MINUTES
- OPS_TARGET_CONCURRENT_USERS
- BACKUP_TARGET_PATH
- BACKUP_RETENTION_DAYS
- BACKUP_FILES_SOURCE_PATH
- MYSQLDUMP_BINARY
- MYSQL_BINARY
