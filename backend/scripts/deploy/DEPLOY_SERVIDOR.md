# Guia de Despliegue a Servidor

Esta guia deja el proyecto listo para subir a un servidor, pero puedes seguir trabajando localmente.

## 1) Subir codigo al servidor

Opciones comunes:

- `git clone` en el servidor
- subir zip y descomprimir en carpeta de proyecto

## 2) Instalar prerequisitos (Ubuntu)

```bash
sudo bash backend/scripts/deploy/bootstrap-ubuntu.sh
```

## 3) Configurar variables de entorno

- Copiar `.env.example` a `.env` (si no existe)
- Ajustar DB, APP_URL, MAIL, etc.

Nota: si ejecutas el deploy automatico y no existe `.env`, el script lo crea desde `.env.example` y genera `APP_KEY`. Aun asi, revisa y ajusta las variables antes de usar en produccion.

Ejemplo minimo:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_db
DB_USERNAME=usuario_db
DB_PASSWORD=clave_db
```

## 4) Ejecutar despliegue automatico

Linux:

```bash
bash backend/scripts/deploy/deploy-app.sh
```

Windows PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File .\backend\scripts\deploy\deploy-app.ps1
```

## 5) Web server

Para produccion usa Nginx/Apache apuntando a `backend/public/`.

## 6) Verificacion rapida

- `/login` responde
- assets compilados existen en `backend/public/build`
- `cd backend && php artisan migrate:status` sin errores

## 7) Nota sobre cache de rutas

Si el proyecto tiene referencias a controladores API faltantes, `route:cache` puede fallar. El script de deploy lo omite automaticamente para no romper el arranque.
