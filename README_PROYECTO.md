# Sistema de Seguimiento de Documentos Contractuales

Repositorio del sistema de gestion documental y flujo de contratacion de la Gobernacion de Caldas.

## Inicio rapido

1. Instalar dependencias:

```bash
npm install
```

2. Levantar backend Laravel:

```bash
php artisan serve
```

3. Ejecutar pruebas Playwright:

```bash
npm run test:run
```

## Estructura organizada

Carpetas principales:

- `App`, `bootstrap`, `config`, `database`, `public`, `resources`, `routes`, `storage`, `tests`
- `docs/legacy-root` para documentacion historica que antes estaba en raiz
- `scripts/php-maintenance` para scripts PHP auxiliares de verificacion y mantenimiento
- `scripts/local/testing` para lanzadores locales de pruebas (opcionales)
- `scripts/deploy` para despliegue automatizado en servidor

Vistas Blade separadas por capa:

- `resources/views/backend` para vistas funcionales internas (admin, areas, procesos, reportes, dashboards).
- `resources/views/frontend` para autenticacion, perfil y vistas publicas.
- `resources/views/layouts`, `resources/views/components`, `resources/views/partials` para elementos compartidos.

Archivos de entrada que se mantienen en raiz:

- `artisan`
- `composer.json`
- `package.json`
- `playwright.config.js`
- `README.md`
- `README_PROYECTO.md`
- `README_TESTING.md`
- `SETUP.md`

## Documentacion principal

- Guía técnica actual: [DOCUMENTACION_CODIGO_ACTUAL.md](DOCUMENTACION_CODIGO_ACTUAL.md)
- Índice de documentos movidos: [docs/legacy-root/README.md](docs/legacy-root/README.md)
- Guía de pruebas: [README_TESTING.md](README_TESTING.md)
- Guía de despliegue: [docs/deploy/DEPLOY_SERVIDOR.md](docs/deploy/DEPLOY_SERVIDOR.md)

## Scripts de mantenimiento

Los scripts PHP sueltos que antes estaban en raiz ahora estan en:

- [scripts/php-maintenance](scripts/php-maintenance)

Ejemplo de uso:

```bash
php scripts/php-maintenance/check_db_state.php
```

## Despliegue automatico (servidor)

Linux:

```bash
bash scripts/deploy/deploy-app.sh
```

Windows / PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\deploy\deploy-app.ps1
```

El flujo completo de deploy esta documentado en:

- [scripts/deploy/README.md](scripts/deploy/README.md)
- [docs/deploy/DEPLOY_SERVIDOR.md](docs/deploy/DEPLOY_SERVIDOR.md)

## Notas de compatibilidad

- No se tocaron rutas web de Laravel ni controladores de negocio por la reorganizacion.
- Se priorizo orden de carpetas sin mover archivos criticos del runtime.

