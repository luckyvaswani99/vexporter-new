<?php

namespace App\Actions\Rfq;

use App\Models\Quote;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Notifications\QuoteReceived;
use Illuminate\Support\Facades\DB;

class SubmitQuote
{
    /**
     * A vendor's answer to an RFQ. Line items are priced individually so the
     * buyer can compare quotes side by side.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Rfq $rfq, Vendor $vendor, array $data): Quote
    {
        $quote = DB::transaction(function () use ($rfq, $vendor, $data): Quote {
            $items = collect($data['items'] ?? [])
                ->filter(fn (array $item) => filled($item['description'] ?? null) && (int) ($item['qty'] ?? 0) > 0)
                ->map(fn (array $item) => [
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'qty' => (int) $item['qty'],
                    'unit' => $item['unit'] ?? $rfq->unit,
                    'unit_price' => (int) $item['unit_price'],
                    'total' => (int) $item['unit_price'] * (int) $item['qty'],
                ]);

            $subtotal = (int) $items->sum('total');
            $shipping = (int) ($data['shipping'] ?? 0);
            $tax = (int) ($data['tax'] ?? 0);

            $quote = Quote::create([
                'reference' => $this->reference(),
                'rfq_id' => $rfq->id,
                'vendor_id' => $vendor->id,
                'status' => Quote::STATUS_SENT,
                'currency' => $data['currency'] ?? $rfq->currency,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => $tax,
                'total' => $subtotal + $shipping + $tax,
                'incoterm' => $data['incoterm'] ?? $rfq->incoterm,
                'lead_time_days' => $data['lead_time_days'] ?? null,
                'validity_until' => $data['validity_until'] ?? now()->addDays(14),
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $quote->items()->createMany($items->all());

            $rfq->vendors()->updateExistingPivot($vendor->id, [
                'status' => 'quoted',
                'viewed_at' => now(),
            ]);

            if ($rfq->status === Rfq::STATUS_OPEN) {
                $rfq->update(['status' => Rfq::STATUS_QUOTED]);
            }

            return $quote;
        });

        $rfq->buyer->notify(new QuoteReceived($quote));

        return $quote;
    }

    private function reference(): string
    {
        $year = now()->format('Y');
        $sequence = Quote::whereYear('created_at', $year)->count() + 1;

        return 'QT-'.$year.'-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
