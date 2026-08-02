<?php

use App\Models\Order;
use App\Models\SubOrder;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('generates commercial invoice, packing list, and certificate of origin documents', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();

    $order = Order::create([
        'reference' => 'VX-2026-DOC001',
        'buyer_id' => $buyer->id,
        'status' => Order::STATUS_CONFIRMED,
        'payment_status' => Order::PAYMENT_ESCROW_HELD,
        'currency' => 'USD',
        'subtotal' => 200000,
        'shipping_total' => 5000,
        'grand_total' => 205000,
        'shipping_address' => [
            'contact_name' => 'Global Pharma LLC',
            'company' => 'Global Pharma',
            'line1' => 'Industry Street 10',
            'city' => 'Berlin',
            'country_code' => 'DE',
            'phone' => '+49 30 123456',
        ],
    ]);

    $subOrder = SubOrder::create([
        'order_id' => $order->id,
        'vendor_id' => $vendor->id,
        'reference' => 'VX-2026-DOC001-A',
        'status' => Order::STATUS_CONFIRMED,
        'subtotal' => 200000,
        'total' => 205000,
        'vendor_payout_amount' => 195000,
    ]);

    $this->actingAs($buyer);

    // 1. Commercial Invoice
    $this->get(route('account.orders.documents', ['order' => $order, 'type' => 'commercial-invoice']))
        ->assertOk()
        ->assertSee('COMMERCIAL INVOICE')
        ->assertSee('EXP-INV-VX-2026-DOC001')
        ->assertSee('SUPPLY MEANT FOR EXPORT UNDER LUT');

    // 2. Packing List
    $this->get(route('account.orders.documents', ['order' => $order, 'type' => 'packing-list', 'sub_order_id' => $subOrder->id]))
        ->assertOk()
        ->assertSee('EXPORT PACKING LIST')
        ->assertSee('EXP-PKL-VX-2026-DOC001-A');

    // 3. Certificate of Origin
    $this->get(route('account.orders.documents', ['order' => $order, 'type' => 'certificate-of-origin', 'sub_order_id' => $subOrder->id]))
        ->assertOk()
        ->assertSee('CERTIFICATE OF ORIGIN')
        ->assertSee('INDIA');
});
