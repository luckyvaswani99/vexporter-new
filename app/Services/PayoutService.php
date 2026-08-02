<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Payout;
use App\Models\SubOrder;
use App\Models\Vendor;
use App\Payments\PaymentManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    public function __construct(private PaymentManager $paymentManager) {}

    /**
     * Group eligible sub-orders by vendor and create pending Payout batches.
     *
     * @return Collection<int, Payout>
     */
    public function generateBatch(?int $vendorId = null): Collection
    {
        $query = SubOrder::where('payout_status', SubOrder::PAYOUT_ELIGIBLE)
            ->whereNotNull('escrow_released_at');

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        $subOrders = $query->with('order')->get();

        if ($subOrders->isEmpty()) {
            return collect();
        }

        $payouts = collect();

        DB::transaction(function () use ($subOrders, &$payouts) {
            foreach ($subOrders->groupBy('vendor_id') as $vId => $items) {
                $vendor = Vendor::find($vId);
                if (! $vendor) {
                    continue;
                }

                $totalAmount = (int) $items->sum('vendor_payout_amount');
                $currency = $items->first()->order?->currency ?? config('vexporter.default_currency', 'USD');
                $subOrderIds = $items->pluck('id')->all();

                $payout = Payout::create([
                    'vendor_id' => $vendor->id,
                    'period_start' => $items->min('created_at')?->toDateString(),
                    'period_end' => $items->max('created_at')?->toDateString(),
                    'amount' => $totalAmount,
                    'currency' => $currency,
                    'status' => 'pending',
                    'sub_order_ids' => $subOrderIds,
                ]);

                $payouts->push($payout);
            }
        });

        return $payouts;
    }

    /**
     * Process payout transfer via gateway or manual bank transfer.
     */
    public function processPayout(Payout $payout, ?string $gatewayName = null): bool
    {
        if ($payout->status === 'paid') {
            return true;
        }

        $gatewayName = $gatewayName ?? $payout->vendor->payout_method ?? 'bank_transfer';
        $gateway = $this->paymentManager->driver($gatewayName);

        $subOrders = SubOrder::whereIn('id', $payout->sub_order_ids ?? [])->get();
        $allSuccess = true;

        DB::transaction(function () use ($payout, $subOrders, $gateway, $gatewayName, &$allSuccess) {
            foreach ($subOrders as $subOrder) {
                $result = $gateway->transferToVendor($subOrder);

                if (! $result->isSuccess) {
                    $allSuccess = false;
                    $payout->update([
                        'status' => 'failed',
                        'failure_reason' => $result->errorMessage ?? 'Transfer failed.',
                    ]);

                    return;
                }
            }

            if ($allSuccess) {
                $payout->update([
                    'status' => 'paid',
                    'gateway' => $gatewayName,
                    'gateway_transfer_id' => 'batch_trf_'.$payout->id.'_'.time(),
                    'processed_at' => now(),
                ]);

                SubOrder::whereIn('id', $payout->sub_order_ids ?? [])->update([
                    'payout_status' => SubOrder::PAYOUT_PAID,
                ]);

                LedgerEntry::create([
                    'type' => LedgerEntry::TYPE_PAYOUT,
                    'vendor_id' => $payout->vendor_id,
                    'payout_id' => $payout->id,
                    'debit' => $payout->amount,
                    'currency' => $payout->currency,
                    'reference' => "PAYOUT-{$payout->id}",
                    'note' => "Payout processed via {$gatewayName}.",
                ]);
            }
        });

        return $allSuccess;
    }

    /**
     * Export payout records into bank transfer CSV format.
     */
    public function exportCsv(Payout $payout): string
    {
        $vendor = $payout->vendor;
        $bank = $vendor->payout_details ?? [];

        $headers = ['Payout ID', 'Vendor', 'Account Holder', 'Account Number', 'IFSC/SWIFT', 'Bank', 'Amount', 'Currency', 'Period'];
        $row = [
            $payout->id,
            $vendor->name,
            $bank['account_holder'] ?? $vendor->legal_name ?? $vendor->name,
            $bank['account_no'] ?? $bank['account_number'] ?? 'N/A',
            $bank['ifsc'] ?? $bank['swift'] ?? 'N/A',
            $bank['bank_name'] ?? 'N/A',
            number_format($payout->amount / 100, 2, '.', ''),
            $payout->currency,
            "{$payout->period_start} to {$payout->period_end}",
        ];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        fputcsv($output, $row);
        rewind($output);

        $csv = stream_get_contents($output);
        fclose($output);

        return $csv ?: '';
    }
}
