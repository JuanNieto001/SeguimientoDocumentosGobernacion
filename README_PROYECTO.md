# Sistema de Seguimiento de Documentos Contractuales

Repositorio del sistema de gestion documental y flujo de contratacion de la Gobernacion de Caldas.

## Inicio rapido

1. Instalar dependencias:

```bash
cd backend && composer install
cd ../frontend && npm install
```

2. Inicializar backend (crea `.env` y `APP_KEY` si faltan):

```powershell
cd backend
.\scripts\local\setup\init.ps1
```

3. Iniciar backend Laravel (terminal 1, desde `backend/`):

```bash
cd backend
php artisan serve
```

4. Iniciar frontend Vite (terminal 2, desde `frontend/`):

```bash
cd frontend
npm run dev
```

## Estructura organizada

Carpetas principales:

- `backend/` para API Laravel, vistas Blade, rutas, base de datos, deploy y scripts PHP.
- `frontend/` para toolchain Node (Vite/Tailwind) y fuentes `resources/js` y `resources/css`.

Vistas Blade separadas por capa:

- `resources/views/backend` para vistas funcionales internas (admin, areas, procesos, reportes, dashboards).
- `resources/views/frontend` para autenticacion, perfil y vistas publicas.
- `resources/views/layouts`, `resources/views/components`, `resources/views/partials` para elementos compartidos.

Archivos principales por carpeta:

- `backend/artisan`, `backend/composer.json`, `backend/SETUP.md`
- `frontend/package.json`, `frontend/vite.config.js`, `frontend/tailwind.config.js`
- `README.md`, `README_PROYECTO.md`

## Documentacion principal

- Guía de despliegue: [backend/scripts/deploy/DEPLOY_SERVIDOR.md](backend/scripts/deploy/DEPLOY_SERVIDOR.md)

## Scripts de mantenimiento

Los scripts PHP sueltos que antes estaban en raiz ahora estan en:

- [backend/scripts/php-maintenance](backend/scripts/php-maintenance)

Ejemplo de uso:

```bash
php backend/scripts/php-maintenance/check_db_state.php
```

## Despliegue automatico (servidor)

Linux:

```bash
bash backend/scripts/deploy/deploy-app.sh
```

Windows / PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File .\backend\scripts\deploy\deploy-app.ps1
```

El flujo completo de deploy esta documentado en:

- [backend/scripts/deploy/README.md](backend/scripts/deploy/README.md)
- [backend/scripts/deploy/DEPLOY_SERVIDOR.md](backend/scripts/deploy/DEPLOY_SERVIDOR.md)

## Notas de compatibilidad

- No se tocaron rutas web de Laravel ni controladores de negocio por la reorganizacion.
- Se priorizo orden de carpetas sin mover archivos criticos del runtime.

