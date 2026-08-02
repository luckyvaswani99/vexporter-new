<?php

namespace App\Actions\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SubOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\OrderPlaced;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    /** Flat freight estimate until carrier rates land in Phase 7. */
    public const SHIPPING_PERCENT = 3;

    public const SHIPPING_MINIMUM = 2500;

    /**
     * Turns a cart into one order plus a sub-order per vendor, freezing prices
     * and commission at the moment of purchase.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(User $buyer, Cart $cart, array $data): Order
    {
        $items = $cart->items()->with('product.vendor')->get();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        return DB::transaction(function () use ($buyer, $cart, $items, $data): Order {
            $order = Order::create([
                'reference' => $this->reference(),
                'buyer_id' => $buyer->id,
                'source' => 'cart',
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'currency' => $cart->currency ?? config('vexporter.default_currency'),
                'billing_address' => $data['billing_address'] ?? $data['shipping_address'],
                'shipping_address' => $data['shipping_address'],
                'incoterm' => $data['incoterm'] ?? null,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            $totals = ['subtotal' => 0, 'shipping' => 0, 'commission' => 0];

            foreach ($items->groupBy('vendor_id') as $vendorId => $vendorItems) {
                $subOrder = $this->createSubOrder($order, (int) $vendorId, $vendorItems);

                $totals['subtotal'] += $subOrder->subtotal;
                $totals['shipping'] += $subOrder->shipping_total;
                $totals['commission'] += $subOrder->commission_amount;
            }

            $order->update([
                'subtotal' => $totals['subtotal'],
                'shipping_total' => $totals['shipping'],
                'grand_total' => $totals['subtotal'] + $totals['shipping'],
                'commission_total' => $totals['commission'],
            ]);

            $cart->items()->delete();

            $buyer->notify(new OrderPlaced($order));

            return $order->refresh();
        });
    }

    /** @param  Collection<int, CartItem>  $items */
    private function createSubOrder(Order $order, int $vendorId, $items): SubOrder
    {
        $vendor = Vendor::findOrFail($vendorId);

        $subOrder = SubOrder::create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'reference' => $order->reference.'-'.Str::upper(Str::random(4)),
            'status' => Order::STATUS_PENDING,
        ]);

        $subtotal = 0;

        foreach ($items as $item) {
            $total = $item->unit_price * $item->qty;
            $subtotal += $total;

            OrderItem::create([
                'sub_order_id' => $subOrder->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'name_snapshot' => $item->snapshot['name'] ?? $item->product?->name,
                'sku' => $item->snapshot['sku'] ?? $item->product?->sku,
                'qty' => $item->qty,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'total' => $total,
            ]);
        }

        $shipping = max(self::SHIPPING_MINIMUM, (int) round($subtotal * self::SHIPPING_PERCENT / 100));
        $commission = (int) round($subtotal * $vendor->commissionPercent() / 100);

        $subOrder->update([
            'subtotal' => $subtotal,
            'shipping_total' => $shipping,
            'total' => $subtotal + $shipping,
            'commission_amount' => $commission,
            'vendor_payout_amount' => $subtotal + $shipping - $commission,
        ]);

        $subOrder->statusHistory()->create([
            'to_status' => Order::STATUS_PENDING,
            'actor_id' => $order->buyer_id,
            'note' => 'Order placed by buyer.',
        ]);

        LedgerEntry::create([
            'type' => LedgerEntry::TYPE_SALE,
            'vendor_id' => $vendor->id,
            'order_id' => $order->id,
            'sub_order_id' => $subOrder->id,
            'credit' => $subtotal + $shipping,
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

        return $subOrder->refresh();
    }

    private function reference(): string
    {
        $year = now()->format('Y');
        $sequence = Order::whereYear('created_at', $year)->count() + 1;

        return 'VX-'.$year.'-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
