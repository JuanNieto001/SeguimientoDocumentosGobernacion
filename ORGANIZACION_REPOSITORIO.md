# Organizacion del Repositorio

Fecha: 2026-04-09

## Cambios aplicados

1. Se despejo la raiz del proyecto moviendo documentacion historica a `docs/legacy-root`.
2. Se movieron scripts PHP auxiliares a `scripts/php-maintenance`.
3. Se mantuvieron en raiz los archivos criticos para runtime y herramientas (`artisan`, `composer.json`, `package.json`, configs, scripts de arranque de uso frecuente).

## Estructura recomendada

- `docs/legacy-root`: documentacion historica, reportes y guias previas.
- `scripts/php-maintenance`: scripts de chequeo, carga y soporte operativo.
- `tests`: pruebas automatizadas.
- `resources`: frontend y vistas.
- `routes`: rutas web/api.
- `App`: logica backend.

## Criterio de seguridad aplicado

- No se movieron controladores, rutas, vistas ni configuraciones base de Laravel.
- No se alteraron URLs funcionales del sistema.
- Se validaron rutas web principales por HTTP despues del ordenamiento.
