#!/usr/bin/env bash
set -euo pipefail

if [ "${EUID}" -ne 0 ]; then
  echo "Ejecuta este script como root: sudo bash scripts/deploy/bootstrap-ubuntu.sh"
  exit 1
fi

echo "[1/6] Actualizando paquetes..."
apt-get update -y

echo "[2/6] Instalando paquetes base..."
apt-get install -y git curl unzip ca-certificates gnupg lsb-release software-properties-common

echo "[3/6] Instalando PHP y extensiones comunes para Laravel..."
apt-get install -y php-cli php-fpm php-common php-mbstring php-xml php-curl php-zip php-bcmath php-intl php-mysql php-sqlite3

echo "[4/6] Instalando Composer (si falta)..."
if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  chmod +x /usr/local/bin/composer
fi

echo "[5/6] Instalando Node.js 20 (si falta)..."
if ! command -v node >/dev/null 2>&1; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
fi

echo "[6/6] Versiones instaladas:"
php -v | head -n 1
composer --version
node -v
npm -v

echo "Bootstrap Ubuntu completado."
