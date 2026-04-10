#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

echo "[1/12] Instalando dependencias PHP (produccion)..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "[2/12] Instalando dependencias Node..."
if [ -f ../frontend/package-lock.json ]; then
  npm --prefix ../frontend ci --no-audit --no-fund
else
  npm --prefix ../frontend install --no-audit --no-fund
fi

echo "[3/12] Compilando assets frontend..."
npm --prefix ../frontend run build

echo "[4/12] Preparando .env..."
if [ ! -f .env ]; then
  cp .env.example .env
  echo "  - .env creado desde .env.example"
fi

echo "[5/12] Generando APP_KEY si hace falta..."
if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

echo "[6/12] Ejecutando migraciones..."
php artisan migrate --force

echo "[7/12] Sincronizando roles y permisos..."
php artisan db:seed --class=RolesAndPermissionsSeeder --force

echo "[8/12] Creando secretarias y unidades base..."
php artisan db:seed --class=SecretariasUnidadesSeeder --force

echo "[9/12] Inicializando motor de flujos (CD-PN)..."
php artisan db:seed --class=MotorFlujosBootstrapSeeder --force

echo "[10/12] Verificando link de storage..."
php artisan storage:link || true

echo "[11/12] Limpiando y optimizando cache segura..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan view:cache

echo "[12/12] Intentando cache de rutas (opcional)..."
if php artisan route:list >/dev/null 2>&1; then
  php artisan route:cache
  echo "  - route:cache aplicado"
else
  echo "  - ADVERTENCIA: route:list fallo, se omite route:cache para no bloquear deploy"
fi

echo "Deploy completado."
