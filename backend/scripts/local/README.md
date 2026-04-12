# Scripts Locales

Esta carpeta agrupa scripts para operacion local del proyecto.

- `setup/`: scripts locales para inicializar el proyecto, levantar servidor y utilidades de red en Windows.

Notas de uso:

- `setup/init.ps1` ahora valida y compila assets frontend cuando falta `public/build/manifest.json`.
- `setup/iniciar_servidor.bat` ejecuta una verificacion previa de Vite (manifest/hot) e intenta compilar antes de iniciar Laravel.
