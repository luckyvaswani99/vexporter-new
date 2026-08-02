<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled work
|--------------------------------------------------------------------------
| Driven by a single cron entry on the server:
|   * * * * * cd /home/forge/vexporter.com && php artisan schedule:run >> /dev/null 2>&1
*/

// Prices are quoted in USD but settled in local currency.
Schedule::command('vexporter:sync-fx-rates')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();

// Money only moves after the dispute window closes.
Schedule::command('vexporter:release-escrow')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Weekly settlement run; payouts still need an admin to approve and send them.
Schedule::command('vexporter:generate-payouts')
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping()
    ->onOneServer();

/*
 * Shared hosting has no Supervisor, so the scheduler drains the queue itself:
 * one worker per minute that exits as soon as the queue is empty, and always
 * before the next tick. On a server with a real worker daemon the queue is
 * already empty, so this costs nothing — but leave it off for Redis setups
 * where a long-running worker is doing the job properly.
 */
if (config('queue.default') === 'database') {
    Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
        ->everyMinute()
        ->withoutOverlapping();
}

// Housekeeping.
Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('queue:prune-failed --hours=336')->daily();
Schedule::command('activitylog:clean --days=365')->dailyAt('03:30');
Schedule::command('auth:clear-resets')->daily();
