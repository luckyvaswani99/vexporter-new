# Deploying VEXPORTER to Hostinger shared hosting

For a VPS, follow [`DEPLOYMENT.md`](DEPLOYMENT.md) instead — it is the better
setup for this app. This file covers Hostinger **Web Hosting (Business plan or
above)**, which is workable because it has SSH, Composer and cron.

> **Do not use hPanel's "Node.js / Vite" quick-install deploy.** That flow
> builds a JavaScript site and serves the output statically. VEXPORTER is a PHP
> application; Vite only bundles its CSS and JS. Deployed that way the site has
> no PHP runtime and will not load.

---

## 0. What the plan must have

Check these in hPanel before starting. If any is missing, the plan is not
enough and Laravel will not run:

| hPanel section | Needs to show |
|---|---|
| Advanced → SSH Access | Enabled, with host/port/username |
| Advanced → Cron Jobs | Available |
| Advanced → PHP Configuration | PHP **8.3 or 8.4** selectable |
| Databases → MySQL Databases | Can create a database |

Required PHP extensions (PHP Configuration → PHP extensions):
`bcmath`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `pdo_mysql`, `zip`.

---

## 1. Build the assets before you push

Shared hosting has no Node, so the Vite bundle travels inside the repository.
`public/build/` is committed on purpose.

On your machine, every time CSS or JS changes:

```bash
npm run build && git add public/build && git commit -m "Rebuild assets" && git push
```

Skip this and the site loads with no styling.

---

## 2. Create the database

hPanel → Databases → MySQL Databases. Note the database name, username and
password — Hostinger prefixes both with your account id
(e.g. `u673430861_vexporter`).

---

## 3. Pull the code

hPanel → Advanced → **Git**. Repository
`https://github.com/luckyvaswani99/vexporter-new`, branch `main`, directory
`public_html`. Deploy.

The repository root ships an [`.htaccess`](.htaccess) that forwards every
request into `public/` and blocks `.env`, `storage/`, `vendor/` and the rest,
so the app works with `public_html` as the document root. If your plan lets you
change the website root directory, point it at `public_html/public` instead and
delete that file — one less moving part.

---

## 4. First-run setup over SSH

```bash
cd ~/public_html

cp .env.hostinger.example .env
nano .env                      # fill in APP_URL, DB_*, MAIL_*

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan storage:link

php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=CatalogSeeder --force
php artisan db:seed --class=ContentSeeder --force

php artisan filament:assets
php artisan optimize
php artisan vexporter:preflight
```

`preflight` must come back with no failures before you point a real domain at
this. Warnings about payment credentials are expected until the gateway keys
are in.

Create the first admin (there is no public admin signup):

```bash
php artisan tinker --execute="\$u = App\Models\User::create(['name' => 'Ops', 'email' => 'ops@your-domain.com', 'password' => 'CHANGE-ME', 'type' => 'admin', 'email_verified_at' => now()]); \$u->syncRoles('admin');"
```

Sign in at `/admin`, change that password, and enrol two-factor from the
profile menu.

**If `php` runs the wrong version**, use the versioned binary — Hostinger
usually exposes `/usr/bin/php8.3` or `/opt/alt/php83/usr/bin/php`. Check with
`php -v` and substitute it in every command below as well.

---

## 5. One cron entry

hPanel → Advanced → Cron Jobs. Every minute:

```
cd ~/public_html && /usr/bin/php artisan schedule:run >/dev/null 2>&1
```

This single entry drives everything: FX rates, hourly escrow release, the
weekly payout batch, log housekeeping — **and the queue**. On shared hosting
there is no Supervisor, so `routes/console.php` schedules a worker that drains
the database queue each minute and exits. Without this cron, notifications,
webhook handling and PDF generation silently never run.

---

## 6. Later deploys

```bash
cd ~/public_html
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan filament:assets
php artisan optimize
php artisan vexporter:preflight
```

Remember step 1 — assets are built locally, not on the server.

---

## 7. What you give up versus a VPS

Worth knowing before this becomes the permanent home:

- **No Redis.** Cache, sessions and the queue all run on MySQL. Fine at launch;
  it becomes the bottleneck well before the database does.
- **Queue latency is up to a minute**, because cron is the trigger. Order
  emails and webhook processing are not instant.
- **No long-running worker**, so a job that takes more than ~50 seconds gets
  cut off and retried.
- **No horizontal scaling** and no zero-downtime deploys.
- **Media on local disk**, so backups have to cover files as well as the
  database.

Move to a VPS when order volume makes any of these hurt; `DEPLOYMENT.md` is
already written for it.
