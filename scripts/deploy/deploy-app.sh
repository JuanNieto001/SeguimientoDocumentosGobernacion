#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

echo "[1/9] Instalando dependencias PHP (produccion)..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "[2/9] Instalando dependencias Node..."
if [ -f package-lock.json ]; then
  npm ci --no-audit --no-fund
else
  npm install --no-audit --no-fund
fi

echo "[3/9] Compilando assets frontend..."
npm run build

echo "[4/9] Preparando .env..."
if [ ! -f .env ]; then
  cp .env.example .env
  echo "  - .env creado desde .env.example"
fi

echo "[5/9] Generando APP_KEY si hace falta..."
if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

echo "[6/9] Ejecutando migraciones..."
php artisan migrate --force

echo "[7/9] Verificando link de storage..."
php artisan storage:link || true

echo "[8/9] Limpiando y optimizando cache segura..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan view:cache

echo "[9/9] Intentando cache de rutas (opcional)..."
if php artisan route:list >/dev/null 2>&1; then
  php artisan route:cache
  echo "  - route:cache aplicado"
else
  echo "  - ADVERTENCIA: route:list fallo, se omite route:cache para no bloquear deploy"
fi

echo "Deploy completado."
