<?php

namespace App\Console\Commands;

use App\Services\PayoutService;
use App\Support\Money;
use Illuminate\Console\Command;

class GeneratePayoutBatch extends Command
{
    protected $signature = 'vexporter:generate-payouts {--vendor= : Restrict the batch to a single vendor id}';

    protected $description = 'Create payout records for vendors whose escrow has been released';

    public function handle(PayoutService $payouts): int
    {
        $vendorId = $this->option('vendor') ? (int) $this->option('vendor') : null;

        $batch = $payouts->generateBatch($vendorId);

        if ($batch->isEmpty()) {
            $this->info('Nothing to pay out.');

            return self::SUCCESS;
        }

        $this->table(
            ['Payout', 'Vendor', 'Amount'],
            $batch->map(fn ($payout) => [
                $payout->id,
                $payout->vendor?->name ?? $payout->vendor_id,
                Money::format($payout->amount, $payout->currency),
            ])->all(),
        );

        $this->info("Created {$batch->count()} payout(s). Approve and settle them from the admin panel.");

        return self::SUCCESS;
    }
}
