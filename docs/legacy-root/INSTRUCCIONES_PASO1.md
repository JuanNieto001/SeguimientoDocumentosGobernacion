# PASO 1 - EJECUTAR MANUALMENTE

## Abre una terminal CMD o PowerShell en:
C:\Users\USUARIO\Desktop\SeguimientoDocumentosGobernacion

## Ejecuta estos comandos en orden:

### 1. Instalar librerías npm (tomará ~2 minutos)
npm install recharts @tanstack/react-query zustand @dnd-kit/core @dnd-kit/utilities react-grid-layout nanoid --save

### 2. Ejecutar migración
php artisan migrate --path=database/migrations/2026_04_04_150001_add_scope_level_to_roles_table.php

### 3. Ejecutar seeders
php artisan db:seed --class=RoleScopeLevelSeeder
php artisan db:seed --class=DashboardBuilderPermissionSeeder

### 4. Limpiar caché de Laravel
php artisan config:clear
php artisan cache:clear

---

## ✅ Verificación
Después de ejecutar, verifica:
- package.json debe incluir las nuevas librerías
- Tabla roles debe tener columna scope_level
- Debe existir permiso dashboard.builder.access

---

## Cuando termines, avísame para continuar con el PASO 2 (Backend)
