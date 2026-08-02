<?php

namespace App\Console\Commands;

use App\Support\PaymentMethods;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Run before (and right after) every production deploy. Everything checked
 * here has bitten a Laravel app in production at least once.
 */
class Preflight extends Command
{
    protected $signature = 'vexporter:preflight {--strict : Treat warnings as failures}';

    protected $description = 'Verify this environment is safe to serve production traffic';

    /** @var array<int, array{0: string, 1: string, 2: string}> */
    private array $results = [];

    public function handle(): int
    {
        $this->checkAppKey();
        $this->checkDebug();
        $this->checkEnvironment();
        $this->checkUrl();
        $this->checkDatabase();
        $this->checkMigrations();
        $this->checkQueue();
        $this->checkCacheAndSession();
        $this->checkStorage();
        $this->checkPublishedAssets();
        $this->checkPaymentMethods();
        $this->checkPaymentCredentials();
        $this->checkMail();

        $this->table(['Check', 'Result', 'Detail'], $this->results);

        $failures = collect($this->results)->where(1, 'FAIL')->count();
        $warnings = collect($this->results)->where(1, 'WARN')->count();

        if ($failures > 0) {
            $this->error("{$failures} check(s) failed — do not serve traffic until these are fixed.");

            return self::FAILURE;
        }

        if ($warnings > 0 && $this->option('strict')) {
            $this->error("{$warnings} warning(s) with --strict.");

            return self::FAILURE;
        }

        $this->info($warnings > 0 ? "Ready, with {$warnings} warning(s)." : 'All checks passed.');

        return self::SUCCESS;
    }

    private function recordPass(string $check, string $detail = ''): void
    {
        $this->results[] = [$check, 'OK', $detail];
    }

    private function recordWarn(string $check, string $detail): void
    {
        $this->results[] = [$check, 'WARN', $detail];
    }

    private function recordFail(string $check, string $detail): void
    {
        $this->results[] = [$check, 'FAIL', $detail];
    }

    private function checkAppKey(): void
    {
        config('app.key')
            ? $this->recordPass('App key', 'set')
            : $this->recordFail('App key', 'APP_KEY is empty — run php artisan key:generate');
    }

    private function checkDebug(): void
    {
        if (! config('app.debug')) {
            $this->recordPass('Debug mode', 'off');

            return;
        }

        app()->isProduction()
            ? $this->recordFail('Debug mode', 'APP_DEBUG=true in production leaks stack traces and env values')
            : $this->recordWarn('Debug mode', 'on (fine outside production)');
    }

    private function checkEnvironment(): void
    {
        $this->recordPass('Environment', config('app.env'));
    }

    private function checkUrl(): void
    {
        $url = (string) config('app.url');

        if (! app()->isProduction()) {
            $this->recordPass('App URL', $url);

            return;
        }

        str_starts_with($url, 'https://')
            ? $this->recordPass('App URL', $url)
            : $this->recordFail('App URL', "APP_URL should be https in production ({$url})");
    }

    private function checkDatabase(): void
    {
        try {
            DB::connection()->getPdo();

            $driver = DB::connection()->getDriverName();

            $driver === 'sqlite' && app()->isProduction()
                ? $this->recordWarn('Database', 'SQLite in production — MySQL is expected for this workload')
                : $this->recordPass('Database', $driver);
        } catch (Throwable $e) {
            $this->recordFail('Database', 'cannot connect: '.$e->getMessage());
        }
    }

    private function checkMigrations(): void
    {
        try {
            $pending = collect(app('migrator')->getMigrationFiles(database_path('migrations')))
                ->keys()
                ->diff(app('migrator')->getRepository()->getRan())
                ->count();

            $pending === 0
                ? $this->recordPass('Migrations', 'up to date')
                : $this->recordFail('Migrations', "{$pending} pending — run php artisan migrate --force");
        } catch (Throwable $e) {
            $this->recordFail('Migrations', $e->getMessage());
        }
    }

    private function checkQueue(): void
    {
        $driver = config('queue.default');

        $driver === 'sync' && app()->isProduction()
            ? $this->recordFail('Queue', 'sync driver runs jobs in the request — use redis or database with a worker')
            : $this->recordPass('Queue', $driver);
    }

    private function checkCacheAndSession(): void
    {
        $cache = config('cache.default');
        $session = config('session.driver');

        $cache === 'array' && app()->isProduction()
            ? $this->recordFail('Cache', 'array driver does not persist between requests')
            : $this->recordPass('Cache', $cache);

        $session === 'array' && app()->isProduction()
            ? $this->recordFail('Session', 'array driver logs everyone out on every request')
            : $this->recordPass('Session', $session);
    }

    private function checkStorage(): void
    {
        try {
            Storage::disk('local')->put('preflight.txt', (string) now());
            Storage::disk('local')->delete('preflight.txt');

            $this->recordPass('Private storage', 'writable');
        } catch (Throwable $e) {
            $this->recordFail('Private storage', $e->getMessage());
        }

        // file_exists() rather than is_link()/is_dir(): on Windows the symlink
        // artisan creates reports as neither, but still serves.
        file_exists(public_path('storage'))
            ? $this->recordPass('Public storage link', 'present')
            : $this->recordWarn('Public storage link', 'missing — run php artisan storage:link');
    }

    /**
     * Filament ships its own CSS/JS outside the Vite build. Miss `filament:assets`
     * and both panels render as unstyled HTML with no working interactions.
     */
    private function checkPublishedAssets(): void
    {
        is_file(public_path('build/manifest.json'))
            ? $this->recordPass('Vite build', 'manifest present')
            : $this->recordFail('Vite build', 'public/build/manifest.json missing — run npm run build');

        is_file(public_path('css/filament/filament/app.css')) && is_dir(public_path('js/filament'))
            ? $this->recordPass('Filament assets', 'published')
            : $this->recordFail('Filament assets', 'panels will render unstyled — run php artisan filament:assets');
    }

    /**
     * A method offered at checkout that cannot actually take money is worse
     * than one that is switched off.
     */
    private function checkPaymentMethods(): void
    {
        $enabled = PaymentMethods::enabledGateways();

        if ($enabled === []) {
            $this->recordFail('Payment methods', 'none enabled — buyers cannot pay for an order');

            return;
        }

        $this->recordPass('Payment methods', implode(', ', $enabled));

        if (in_array('bank_transfer', $enabled, true) && PaymentMethods::bankDetails() === []) {
            $this->recordWarn('Wire details', 'bank wire is offered but no account is set in Admin → Finance → Payment methods');
        }
    }

    private function checkPaymentCredentials(): void
    {
        foreach (['razorpay' => 'services.razorpay', 'stripe' => 'services.stripe'] as $gateway => $key) {
            if (! PaymentMethods::isEnabled($gateway)) {
                $this->recordPass(ucfirst($gateway), 'not offered at checkout');

                continue;
            }

            $secret = (string) config("{$key}.secret");
            $webhook = (string) config("{$key}.webhook_secret");

            if ($secret === '' || str_contains($secret, 'dummy')) {
                app()->isProduction()
                    ? $this->recordFail(ucfirst($gateway), 'placeholder credentials — payments will fail')
                    : $this->recordWarn(ucfirst($gateway), 'placeholder credentials (test mode)');

                continue;
            }

            str_contains($webhook, 'dummy') || $webhook === ''
                ? $this->recordWarn(ucfirst($gateway), 'webhook secret not set — webhooks cannot be verified')
                : $this->recordPass(ucfirst($gateway), 'credentials configured');
        }
    }

    private function checkMail(): void
    {
        $mailer = config('mail.default');

        $mailer === 'log' && app()->isProduction()
            ? $this->recordFail('Mail', 'log driver — buyers and vendors will never receive email')
            : $this->recordPass('Mail', $mailer);
    }
}
