# Scripts PHP de Mantenimiento

Esta carpeta agrupa scripts auxiliares de diagnostico, validacion y carga de datos que estaban en la raiz del repositorio.

## Uso

Ejecutar desde la raiz del proyecto:

```bash
php scripts/php-maintenance/<archivo>.php
```

Ejemplos:

```bash
php scripts/php-maintenance/check_db_state.php
php scripts/php-maintenance/verificar_flujos.php
php scripts/php-maintenance/crear_datos_prueba.php
```

## Nota

Estos scripts no hacen parte del runtime web principal de Laravel; son herramientas operativas.
