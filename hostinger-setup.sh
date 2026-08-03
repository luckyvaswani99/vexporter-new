#!/usr/bin/env bash
#
# First-run setup for VEXPORTER on Hostinger shared hosting.
#
#   cd ~/public_html && bash hostinger-setup.sh
#
# Safe to re-run: every step is idempotent, and it stops rather than guessing
# whenever something needs a human decision.
#
set -uo pipefail

step()  { printf '\n\033[1;36m▸ %s\033[0m\n' "$1"; }
ok()    { printf '  \033[0;32m✓\033[0m %s\n' "$1"; }
warn()  { printf '  \033[0;33m!\033[0m %s\n' "$1"; }
die()   { printf '\n\033[0;31m✗ %s\033[0m\n\n' "$1"; exit 1; }

cd "$(dirname "$0")"

# --- PHP ----------------------------------------------------------------------
step "Finding a PHP 8.4+ binary"

PHP=""
for candidate in php8.4 /usr/bin/php8.4 /opt/alt/php84/usr/bin/php php; do
    if command -v "$candidate" >/dev/null 2>&1; then
        version="$("$candidate" -r 'echo PHP_VERSION;' 2>/dev/null || true)"
        major_minor="$("$candidate" -r 'echo PHP_MAJOR_VERSION * 100 + PHP_MINOR_VERSION;' 2>/dev/null || echo 0)"

        if [ "${major_minor:-0}" -ge 804 ]; then
            PHP="$candidate"
            ok "$candidate — PHP $version"
            break
        fi
    fi
done

[ -n "$PHP" ] || die "No PHP 8.4+ found. Set the version in hPanel → Advanced → PHP Configuration, then re-run."

# On shared hosting the shell's default `php` is the system one and has nothing
# to do with the version Apache serves the site with — that comes from hPanel.
# Worth surfacing, but not worth blocking on: a stale shell PHP is normal.
default_version="$(php -r 'echo PHP_MAJOR_VERSION * 100 + PHP_MINOR_VERSION;' 2>/dev/null || echo 0)"

if [ "${default_version:-0}" -lt 804 ]; then
    warn "Shell default 'php' is $(php -r 'echo PHP_VERSION;' 2>/dev/null || echo 'unknown') — using $PHP instead."
    warn "Confirm the *website* is on 8.4 in hPanel → Advanced → PHP Configuration."
fi

step "Checking PHP extensions"
missing=""
for ext in bcmath curl dom fileinfo gd intl mbstring pdo_mysql zip; do
    "$PHP" -m | grep -qi "^${ext}$" || missing="$missing $ext"
done

if [ -n "$missing" ]; then
    die "Missing PHP extensions:$missing
  Enable them in hPanel → Advanced → PHP Configuration → PHP extensions, then re-run."
fi
ok "all required extensions present"

# --- Composer -----------------------------------------------------------------
step "Locating Composer"

# Always drive Composer with the PHP we picked. The `composer` on PATH has its
# own shebang pointing at the default interpreter, which resolves the platform
# requirements against the wrong version.
composer_bin="$(command -v composer 2>/dev/null || true)"

if [ -n "$composer_bin" ] && "$PHP" "$composer_bin" --version >/dev/null 2>&1; then
    COMPOSER="$PHP $composer_bin"
    ok "system composer, running on $("$PHP" -r 'echo PHP_VERSION;')"
elif [ -f composer.phar ]; then
    COMPOSER="$PHP composer.phar"
    ok "local composer.phar"
else
    warn "not found — downloading composer.phar"
    "$PHP" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
        && "$PHP" composer-setup.php --quiet \
        && rm -f composer-setup.php \
        || die "Could not download Composer. Install it manually and re-run."
    COMPOSER="$PHP composer.phar"
    ok "downloaded"
fi

# --- Environment --------------------------------------------------------------
step "Checking .env"

if [ ! -f .env ]; then
    cp .env.hostinger.example .env
    die ".env created from the Hostinger template.

  Edit it now, then re-run this script:
      nano .env

  You must fill in:
      APP_URL       your https:// domain
      DB_DATABASE   from hPanel → Databases → MySQL Databases
      DB_USERNAME   same place
      DB_PASSWORD   same place"
fi
ok ".env present"

for key in DB_DATABASE DB_USERNAME DB_PASSWORD; do
    value="$(grep -E "^${key}=" .env | head -1 | cut -d= -f2-)"
    [ -n "$value" ] || die "${key} is still empty in .env — fill it in and re-run."
done
ok "database credentials filled in"

# --- Dependencies -------------------------------------------------------------
step "Installing PHP dependencies"
$COMPOSER install --no-dev --optimize-autoloader --no-interaction \
    || die "composer install failed — read the error above."
ok "vendor/ ready"

step "Application key"
if grep -qE '^APP_KEY=.+' .env; then
    ok "already set"
else
    "$PHP" artisan key:generate --force && ok "generated"
fi

# --- Assets -------------------------------------------------------------------
step "Front-end assets"
if [ -f public/build/manifest.json ]; then
    ok "Vite bundle shipped with the repo"
else
    warn "public/build/manifest.json missing — the site will load unstyled."
    warn "Run 'npm run build' on your own machine, commit public/build, push, then git pull here."
fi

step "Filament panel assets"
"$PHP" artisan filament:assets >/dev/null && ok "published"

step "Public storage link"
if [ -e public/storage ]; then
    ok "already linked"
else
    "$PHP" artisan storage:link >/dev/null && ok "linked"
fi

# --- Database -----------------------------------------------------------------
step "Running migrations"
"$PHP" artisan migrate --force || die "Migration failed — check the DB_* values in .env."
ok "schema up to date"

step "Seeding reference data"
# Roles, verticals/categories/attributes, currencies and legal pages only.
# No demo vendors, products or orders — this is a real store.
for seeder in RoleSeeder CatalogSeeder ContentSeeder; do
    "$PHP" artisan db:seed --class="$seeder" --force >/dev/null 2>&1 \
        && ok "$seeder" \
        || warn "$seeder skipped (probably already seeded)"
done

# --- Caches -------------------------------------------------------------------
step "Caching config, routes and views"
"$PHP" artisan optimize >/dev/null && ok "cached"

# --- Verdict ------------------------------------------------------------------
step "Preflight"
"$PHP" artisan vexporter:preflight
result=$?

printf '\n'
if [ $result -eq 0 ]; then
    printf '\033[0;32m✓ Setup complete.\033[0m\n\n'
else
    printf '\033[0;31m✗ Preflight reported failures — fix those before going live.\033[0m\n\n'
fi

cat <<EOF
Two things are still yours to do:

  1. Cron — hPanel → Advanced → Cron Jobs, every minute:
       cd $(pwd) && $PHP artisan schedule:run >/dev/null 2>&1

     This drives the queue as well as the scheduled jobs. Without it,
     notifications, webhooks and PDFs never run.

  2. Your admin account — pick your own password, do not paste one you have
     shared anywhere:
       $PHP artisan tinker

     then:
       \$u = App\\Models\\User::create(['name' => 'Ops', 'email' => 'you@your-domain.com', 'password' => 'YOUR-PASSWORD', 'type' => 'admin', 'email_verified_at' => now()]);
       \$u->syncRoles('admin');

     Sign in at /admin and enrol two-factor from the profile menu.
EOF

exit $result
