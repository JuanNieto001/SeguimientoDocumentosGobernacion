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

## Documentacion

- Guia general del proyecto: `README_PROYECTO.md`
- Setup del backend: `backend/SETUP.md`
- Deploy en servidor: `backend/scripts/deploy/DEPLOY_SERVIDOR.md`
