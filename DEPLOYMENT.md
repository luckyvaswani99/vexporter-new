# VEXPORTER — Deployment runbook

Everything needed to take this repository from a laptop to production, and to
keep it running. Commands assume Laravel Forge on Ubuntu; adapt paths if you
provision manually.

---

## 1. Server requirements

| Component | Version | Why |
|---|---|---|
| Ubuntu | 24.04 LTS | Forge default |
| PHP | 8.4 (FPM) | `composer.json` requires ^8.3 |
| MySQL | 8.0 | JSON columns, generated demo volume, transactions |
| Redis | 7 | cache, sessions **and** the queue |
| Nginx | latest | TLS termination, static assets |
| Node | 22 LTS | asset build |
| Supervisor | latest | queue workers |

PHP extensions: `bcmath curl dom fileinfo gd intl mbstring pdo_mysql redis zip`.

Sizing for launch: 2 vCPU / 4 GB app server, managed MySQL, 1 GB Redis. Media
and documents live on S3/R2, so disk stays small.

---

## 2. First deploy

```bash
# On the server, as the deploy user
git clone git@github.com:<org>/vexporter.git vexporter.com
cd vexporter.com

cp .env.production.example .env      # fill in every blank
php artisan key:generate
php artisan storage:link

composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan filament:assets          # panel CSS/JS lives outside the Vite build

php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force        # roles & permissions
php artisan db:seed --class=CatalogSeeder --force     # verticals, categories, attributes
php artisan db:seed --class=ContentSeeder --force     # currencies, legal pages

php artisan optimize
php artisan vexporter:preflight --strict
```

Create the first admin (there is no public admin signup):

```bash
php artisan tinker --execute="\$u = App\Models\User::create(['name' => 'Ops', 'email' => 'ops@vexporter.com', 'password' => 'CHANGE-ME', 'type' => 'admin', 'email_verified_at' => now()]); \$u->syncRoles('admin');"
```

Sign in at `/admin`, then enrol two-factor authentication from the profile menu.

---

## 3. Ongoing deploys

Point Forge's deploy script at [`deploy.sh`](deploy.sh), or run it from CI:

```bash
DEPLOY_BRANCH=main ./deploy.sh
```

It pulls, installs, builds, migrates, re-caches, restarts workers, runs
preflight and lifts maintenance mode. `php artisan down --secret=...` lets the
team keep browsing during the window.

**Rollback:** deploys are not atomic by default. Either enable Forge's
zero-downtime deployments (release folders + symlink) or roll back with:

```bash
git reset --hard <previous-sha>
composer install --no-dev --optimize-autoloader
php artisan optimize && php artisan queue:restart
```

Migrations are **not** auto-reverted — write them to be backwards compatible
(add columns nullable, drop them in a later release).

---

## 4. Queue workers

Every notification, webhook handler and PDF render is queued. Without a worker
the marketplace looks broken but silent.

Forge → Daemons:

```
Command:   php /home/forge/vexporter.com/artisan queue:work redis --tries=3 --max-time=3600 --backoff=10
Directory: /home/forge/vexporter.com
Processes: 2
```

Webhooks and payouts are latency-sensitive; if you split queues, run a
dedicated worker for `--queue=webhooks,default`.

---

## 5. Scheduler

One cron entry drives everything in `routes/console.php`:

```
* * * * * cd /home/forge/vexporter.com && php artisan schedule:run >> /dev/null 2>&1
```

| Task | Cadence | Purpose |
|---|---|---|
| `vexporter:sync-fx-rates` | daily 02:00 | USD quotes → settlement currencies |
| `vexporter:release-escrow` | hourly | releases funds once the dispute window closes |
| `vexporter:generate-payouts` | Mondays 03:00 | builds the weekly settlement batch (admin still approves) |
| `queue:prune-batches` / `queue:prune-failed` | daily | housekeeping |
| `activitylog:clean --days=365` | daily 03:30 | audit-log retention |

Verify with `php artisan schedule:list`.

---

## 6. Webhooks

Register these endpoints in each dashboard **and** copy the signing secret into
`.env` — unsigned webhooks are rejected:

| Gateway | URL | Events |
|---|---|---|
| Razorpay | `https://vexporter.com/webhooks/razorpay` | `payment.captured`, `payment.failed`, `refund.processed` |
| Stripe | `https://vexporter.com/webhooks/stripe` | `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded` |

Deliveries are recorded in `webhook_events` and de-duplicated by event id, so
replays are safe.

---

## 7. Backups

Managed database snapshots (Forge/RDS) are the baseline: **daily, 30-day
retention, restore tested quarterly**. Add file backups for the private disk if
KYC/COA paperwork is stored locally rather than on S3.

For self-managed backups, `spatie/laravel-backup` is the drop-in option:

```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

then schedule `backup:run` and `backup:clean` alongside the tasks above.

A restore is only real once you have replayed it into staging.

---

## 8. Monitoring

- **Errors:** Sentry (`SENTRY_LARAVEL_DSN`) — alert on new issues and on error rate.
- **Uptime:** hit `/up` (Laravel health endpoint) every minute from outside the VPC.
- **Queues:** alert if `queue:failed` is non-empty or worker count drops.
- **Business:** the admin dashboard already surfaces GMV, pending vendors and
  open RFQs; wire the same numbers to a daily digest if the team wants it.
- **Logs:** `LOG_STACK=daily` with 14-day retention, shipped to Papertrail or
  Forge's log viewer.

---

## 9. Go-live checklist

- [ ] `php artisan vexporter:preflight --strict` passes on the production server
- [ ] `APP_DEBUG=false`, `APP_ENV=production`, `APP_URL` on https
- [ ] Real Razorpay **and** Stripe credentials + webhook secrets in place
- [ ] Webhook endpoints registered and a test event delivered end to end
- [ ] Queue worker + scheduler running (`schedule:list`, `queue:work` daemon up)
- [ ] TLS certificate issued and auto-renewing; HSTS confirmed in response headers
- [ ] Admin 2FA enrolled, then flip `isRequired: true` in `AdminPanelProvider`
- [ ] Database backups running and one restore rehearsed
- [ ] Sentry receiving events; uptime monitor green
- [ ] Legal pages reviewed by counsel (terms, privacy, vendor agreement,
      prohibited items — pharma especially)
- [ ] Seed data removed or clearly marked before real vendors onboard
