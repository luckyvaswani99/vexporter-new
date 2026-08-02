<?php

namespace App\Console\Commands;

use App\Services\EscrowService;
use Illuminate\Console\Command;

class ReleaseEscrow extends Command
{
    protected $signature = 'vexporter:release-escrow {--days=7 : Days after delivery before funds are released}';

    protected $description = 'Release escrow for delivered sub-orders past the dispute window';

    public function handle(EscrowService $escrow): int
    {
        $days = (int) $this->option('days');

        $released = $escrow->autoReleaseEligibleSubOrders($days);

        $this->info("Released escrow on {$released} sub-order(s) delivered more than {$days} days ago.");

        return self::SUCCESS;
    }
}
