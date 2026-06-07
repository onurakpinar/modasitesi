#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Composer production install"
composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

echo "==> NPM ci + build"
npm ci --ignore-scripts
npm run build

echo "==> Artisan package:discover + optimize (production cache)"
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php bootstrap/cache/routes*.php
php artisan package:discover --ansi
php artisan optimize

if docker info >/dev/null 2>&1; then
    echo "==> Docker image build"
    docker build -t modapusula:deploy-test .
else
    echo "==> Docker daemon yok; imaj build atlandı (composer + npm başarılı)"
fi

echo "==> Dev bağımlılıkları geri yükle"
composer install --no-interaction --quiet

echo "==> Deploy build test PASSED"
