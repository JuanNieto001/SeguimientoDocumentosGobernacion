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
- Sincroniza roles/permisos y scope del dashboard (`php artisan db:seed --class=RolesAndPermissionsSeeder --force`)
- Crea secretarías y unidades base (`php artisan db:seed --class=SecretariasUnidadesSeeder --force`)
- Inicializa el Motor de Flujos en CD-PN (`php artisan db:seed --class=MotorFlujosBootstrapSeeder --force`)
- Crea storage link si aplica
- Limpia y optimiza cache de config/vistas
- Intenta `route:cache` solo si `route:list` funciona

## Verificacion rapida del Motor de Flujos

Ejecuta desde `backend/` para confirmar que solo existe CD-PN y que tiene 10 pasos:

```bash
php artisan tinker --execute="dump([
	'flujos' => DB::table('flujos')->select('codigo','activo')->orderBy('codigo')->get(),
	'pasos_cd_pn' => DB::table('flujo_pasos')
		->join('flujo_versiones','flujo_versiones.id','=','flujo_pasos.flujo_version_id')
		->join('flujos','flujos.id','=','flujo_versiones.flujo_id')
		->where('flujos.codigo','CD_PN')
		->count(),
	'workflows' => DB::table('workflows')->select('codigo','activo')->orderBy('codigo')->get(),
]);"
```

## Nota importante

Si `route:list` falla por clases/controladores faltantes en rutas API, el script no bloquea el deploy: omite `route:cache` para mantener la aplicacion operativa.
