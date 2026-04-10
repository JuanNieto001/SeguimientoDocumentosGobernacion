# SeguimientoDocumentosGobernacion

Repositorio reorganizado con separacion clara de capas:

- `backend/`: aplicacion Laravel (PHP), rutas, vistas Blade, migraciones, scripts de deploy y mantenimiento.
- `frontend/`: assets y toolchain (Vite, Tailwind, React) para compilar en `backend/public/build`.

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

3. Backend (terminal 1):

```bash
cd backend
php artisan serve
```

4. Frontend (terminal 2):

```bash
cd frontend
npm run dev
```

## Dashboards por rol

- Panel de Control (ejecutivo): roles con dashboard_scope global/secretaria/unidad (admin, admin_general, gobernador, secretario, jefe_unidad).
- Dashboard personal: roles de area documentos (compras, talento_humano, rentas, contabilidad, inversiones_publicas, presupuesto, radicacion) y otros.
- Si un usuario tiene varios roles, se usa el alcance mas alto: global > secretaria > unidad > propios.

## Documentacion

- Guia general del proyecto: `README_PROYECTO.md`
- Setup del backend: `backend/SETUP.md`
- Deploy en servidor: `backend/scripts/deploy/DEPLOY_SERVIDOR.md`
