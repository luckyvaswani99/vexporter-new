<?php

namespace App\Actions\Rfq;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Quote;
use App\Models\Rfq;
use App\Models\SubOrder;
use App\Notifications\QuoteAccepted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcceptQuote
{
    /**
     * Converts an accepted quote into a real order. Everything the buyer agreed
     * to — prices, incoterm, freight — is copied across so later edits to the
     * quote cannot change the order.
     */
    public function handle(Quote $quote, array $shippingAddress = []): Order
    {
        if ($quote->status === Quote::STATUS_ACCEPTED) {
            throw ValidationException::withMessages(['quote' => 'This quote has already been accepted.']);
        }

        if ($quote->isExpired()) {
            throw ValidationException::withMessages(['quote' => 'This quote has expired — ask the vendor to revise it.']);
        }

        $order = DB::transaction(function () use ($quote, $shippingAddress): Order {
            $rfq = $quote->rfq;
            $vendor = $quote->vendor;

            $order = Order::create([
                'reference' => $this->reference(),
                'buyer_id' => $rfq->buyer_id,
                'source' => 'quote',
                'quote_id' => $quote->id,
                'status' => Order::STATUS_CONFIRMED,
                'payment_status' => Order::PAYMENT_UNPAID,
                'currency' => $quote->currency,
                'subtotal' => $quote->subtotal,
                'shipping_total' => $quote->shipping,
                'tax_total' => $quote->tax,
                'grand_total' => $quote->total,
                'shipping_address' => $shippingAddress,
                'billing_address' => $shippingAddress,
                'incoterm' => $quote->incoterm,
                'notes' => $quote->notes,
                'placed_at' => now(),
            ]);

            $commission = (int) round($quote->subtotal * $vendor->commissionPercent() / 100);

            $subOrder = SubOrder::create([
                'order_id' => $order->id,
                'vendor_id' => $vendor->id,
                'reference' => $order->reference.'-'.Str::upper(Str::random(4)),
                'status' => Order::STATUS_CONFIRMED,
                'subtotal' => $quote->subtotal,
                'shipping_total' => $quote->shipping,
                'tax_total' => $quote->tax,
                'total' => $quote->total,
                'commission_amount' => $commission,
                'vendor_payout_amount' => $quote->total - $commission,
            ]);

            foreach ($quote->items as $item) {
                OrderItem::create([
                    'sub_order_id' => $subOrder->id,
                    'product_id' => $item->product_id,
                    'name_snapshot' => $item->description,
                    'qty' => $item->qty,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ]);
            }

            $subOrder->statusHistory()->create([
                'to_status' => Order::STATUS_CONFIRMED,
                'actor_id' => $rfq->buyer_id,
                'note' => "Created from accepted quote {$quote->reference}.",
            ]);

            LedgerEntry::create([
                'type' => LedgerEntry::TYPE_SALE,
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'sub_order_id' => $subOrder->id,
                'credit' => $quote->total,
                'currency' => $order->currency,
                'reference' => $subOrder->reference,
            ]);

            LedgerEntry::create([
                'type' => LedgerEntry::TYPE_COMMISSION,
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'sub_order_id' => $subOrder->id,
                'debit' => $commission,
                'currency' => $order->currency,
                'reference' => $subOrder->reference,
            ]);

            $quote->update(['status' => Quote::STATUS_ACCEPTED]);

            // Competing quotes on the same RFQ are closed out.
            Quote::where('rfq_id', $rfq->id)
                ->whereKeyNot($quote->id)
                ->whereIn('status', [Quote::STATUS_SENT, Quote::STATUS_REVISED])
                ->update(['status' => Quote::STATUS_REJECTED]);

            $rfq->update(['status' => Rfq::STATUS_CONVERTED]);

            return $order->refresh();
        });

        $quote->vendor->owner->notify(new QuoteAccepted($quote, $order));

        return $order;
    }

    private function reference(): string
    {
        $year = now()->format('Y');
        $sequence = Order::whereYear('created_at', $year)->count() + 1;

        return 'VX-'.$year.'-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
