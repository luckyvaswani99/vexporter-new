#!/usr/bin/env bash
#
# VEXPORTER deploy script (Laravel Forge "Deploy Script" or any CI runner).
# Safe to re-run; every step is idempotent.
#
set -euo pipefail

APP_DIR="${APP_DIR:-$(pwd)}"
PHP="${PHP:-php}"

cd "$APP_DIR"

echo "▸ Pulling code"
git pull origin "${DEPLOY_BRANCH:-main}"

echo "▸ Installing PHP dependencies"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "▸ Building front-end assets"
npm ci
npm run build

# Filament serves its own CSS/JS from public/, outside the Vite build.
echo "▸ Publishing Filament assets"
$PHP artisan filament:assets

echo "▸ Maintenance window"
# `--render` keeps a branded page up; the secret lets the team keep browsing.
$PHP artisan down --render="errors::503" --secret="${DEPLOY_SECRET:-deploying}" --retry=60 || true

echo "▸ Migrating"
$PHP artisan migrate --force

echo "▸ Caching config, routes, views and events"
$PHP artisan optimize

echo "▸ Ensuring public storage link"
$PHP artisan storage:link || true

echo "▸ Restarting workers"
$PHP artisan queue:restart

echo "▸ Preflight"
$PHP artisan vexporter:preflight

echo "▸ Back online"
$PHP artisan up

echo "✓ Deploy complete"
