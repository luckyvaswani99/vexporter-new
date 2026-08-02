<?php

namespace App\Console\Commands;

use App\Services\FxService;
use Illuminate\Console\Command;

class SyncExchangeRates extends Command
{
    protected $signature = 'vexporter:sync-fx-rates';

    protected $description = 'Sync currency exchange rates against USD';

    public function handle(FxService $fxService): int
    {
        $this->info('Syncing exchange rates...');
        $fxService->syncRates();
        $this->info('Exchange rates synced successfully.');

        return Command::SUCCESS;
    }
}
