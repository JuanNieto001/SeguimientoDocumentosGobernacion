# Deploy a Servidor

Este folder contiene scripts para dejar el proyecto listo en servidor.

## Linux (Ubuntu)

1. Instalar prerequisitos del servidor:

```bash
sudo bash backend/scripts/deploy/bootstrap-ubuntu.sh
```

2. Desplegar aplicacion (dependencias, build, migraciones, cache):

```bash
bash backend/scripts/deploy/deploy-app.sh
```

## Windows (local o servidor Windows)

```powershell
powershell -ExecutionPolicy Bypass -File .\backend\scripts\deploy\deploy-app.ps1
```

## Que hace deploy-app

- Instala dependencias PHP de produccion (`composer install --no-dev`)
- Instala dependencias Node en `frontend/` (`npm --prefix ../frontend ci` o `npm --prefix ../frontend install`)
- Compila frontend desde `frontend/` (`npm --prefix ../frontend run build`)
- Crea `.env` desde `.env.example` si falta
- Genera APP_KEY si hace falta
- Ejecuta migraciones (`php artisan migrate --force`)
- Crea storage link si aplica
- Limpia y optimiza cache de config/vistas
- Intenta `route:cache` solo si `route:list` funciona

## Nota importante

Si `route:list` falla por clases/controladores faltantes en rutas API, el script no bloquea el deploy: omite `route:cache` para mantener la aplicacion operativa.
