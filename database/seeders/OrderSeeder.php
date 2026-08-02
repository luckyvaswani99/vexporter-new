<?php

namespace Database\Seeders;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Builds a realistic order history so dashboards, analytics, payouts and
     * the vendor panel have data to work with from day one.
     */
    public function run(): void
    {
        $buyers = $this->buyers();
        $products = Product::visible()->with('vendor')->get();

        if ($products->isEmpty()) {
            return;
        }

        foreach (range(1, 40) as $index) {
            $buyer = $buyers->random();
            $placedAt = now()->subDays(fake()->numberBetween(0, 60))->subHours(fake()->numberBetween(0, 23));
            $lines = $products->random(fake()->numberBetween(1, 4));

            $order = Order::create([
                'reference' => 'VX-'.$placedAt->format('Y').'-'.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
                'buyer_id' => $buyer->id,
                'source' => 'cart',
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'currency' => 'USD',
                'incoterm' => fake()->randomElement(['FOB', 'CIF', 'EXW', 'DDP']),
                'placed_at' => $placedAt,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);

            $orderTotals = ['subtotal' => 0, 'shipping' => 0, 'commission' => 0];

            foreach ($lines->groupBy('vendor_id') as $vendorId => $vendorLines) {
                $subOrder = $this->createSubOrder($order, (int) $vendorId, $vendorLines, $placedAt);

                $orderTotals['subtotal'] += $subOrder->subtotal;
                $orderTotals['shipping'] += $subOrder->shipping_total;
                $orderTotals['commission'] += $subOrder->commission_amount;
            }

            $grandTotal = $orderTotals['subtotal'] + $orderTotals['shipping'];
            $status = fake()->randomElement([
                Order::STATUS_COMPLETED, Order::STATUS_COMPLETED, Order::STATUS_DELIVERED,
                Order::STATUS_SHIPPED, Order::STATUS_PROCESSING, Order::STATUS_CONFIRMED,
            ]);

            $order->update([
                'status' => $status,
                'payment_status' => in_array($status, [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED], true)
                    ? Order::PAYMENT_RELEASED
                    : Order::PAYMENT_ESCROW_HELD,
                'subtotal' => $orderTotals['subtotal'],
                'shipping_total' => $orderTotals['shipping'],
                'grand_total' => $grandTotal,
                'commission_total' => $orderTotals['commission'],
            ]);

            $order->subOrders()->update(['status' => $status]);

            Payment::create([
                'order_id' => $order->id,
                'gateway' => fake()->randomElement(['razorpay', 'stripe']),
                'gateway_payment_id' => 'pay_'.Str::random(14),
                'amount' => $grandTotal,
                'currency' => 'USD',
                'status' => Payment::STATUS_CAPTURED,
                'method' => fake()->randomElement(['card', 'netbanking', 'bank_transfer']),
                'paid_at' => $placedAt,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);
        }
    }

    private function createSubOrder(Order $order, int $vendorId, $lines, $placedAt): SubOrder
    {
        $subOrder = SubOrder::create([
            'order_id' => $order->id,
            'vendor_id' => $vendorId,
            'reference' => $order->reference.'-'.Str::upper(Str::random(4)),
            'status' => Order::STATUS_PENDING,
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        $subtotal = 0;

        foreach ($lines as $product) {
            $qty = max($product->moq, $product->moq * fake()->numberBetween(1, 6));
            $unitPrice = $product->priceForQty($qty);
            $total = $unitPrice * $qty;
            $subtotal += $total;

            OrderItem::create([
                'sub_order_id' => $subOrder->id,
                'product_id' => $product->id,
                'name_snapshot' => $product->name,
                'sku' => $product->sku,
                'qty' => $qty,
                'unit' => $product->unit,
                'unit_price' => $unitPrice,
                'total' => $total,
                'batch_no' => $product->vertical->slug === 'pharma' ? strtoupper(fake()->bothify('B##??##')) : null,
                'expiry_date' => $product->vertical->slug === 'pharma' ? now()->addYears(2) : null,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);
        }

        $shipping = (int) round($subtotal * 0.03);
        $commission = (int) round($subtotal * ($subOrder->vendor->commissionPercent() / 100));

        $subOrder->update([
            'subtotal' => $subtotal,
            'shipping_total' => $shipping,
            'total' => $subtotal + $shipping,
            'commission_amount' => $commission,
            'vendor_payout_amount' => $subtotal + $shipping - $commission,
        ]);

        LedgerEntry::create([
            'type' => LedgerEntry::TYPE_SALE,
            'vendor_id' => $vendorId,
            'order_id' => $order->id,
            'sub_order_id' => $subOrder->id,
            'credit' => $subtotal + $shipping,
            'currency' => 'USD',
            'reference' => $subOrder->reference,
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        LedgerEntry::create([
            'type' => LedgerEntry::TYPE_COMMISSION,
            'vendor_id' => $vendorId,
            'order_id' => $order->id,
            'sub_order_id' => $subOrder->id,
            'debit' => $commission,
            'currency' => 'USD',
            'reference' => $subOrder->reference,
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        if (fake()->boolean(70)) {
            Shipment::create([
                'sub_order_id' => $subOrder->id,
                'carrier' => fake()->randomElement(['DHL', 'FedEx', 'Maersk', 'Blue Dart']),
                'tracking_no' => strtoupper(fake()->bothify('??########')),
                'status' => fake()->randomElement(['in_transit', 'customs', 'delivered']),
                'incoterm' => $order->incoterm,
                'port_of_loading' => fake()->randomElement(['Nhava Sheva', 'Mundra', 'Chennai']),
                'shipped_at' => $placedAt->copy()->addDays(3),
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);
        }

        return $subOrder->fresh();
    }

    /** A pool of buyers spread across the corridors VEXPORTER serves. */
    private function buyers()
    {
        $existing = User::where('type', User::TYPE_BUYER)->get();

        if ($existing->count() >= 8) {
            return $existing;
        }

        foreach (range(1, 8) as $index) {
            $existing->push(User::updateOrCreate(
                ['email' => "buyer{$index}@vexporter.test"],
                [
                    'name' => fake()->name(),
                    'password' => Hash::make('password'),
                    'type' => User::TYPE_BUYER,
                    'email_verified_at' => now(),
                ],
            ));
        }

        return $existing;
    }
}
