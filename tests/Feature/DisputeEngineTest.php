<?php

use App\Models\Dispute;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('allows buyer to open dispute and freezes escrow payout release', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();

    $order = Order::create([
        'reference' => 'VX-2026-DSP001',
        'buyer_id' => $buyer->id,
        'status' => Order::STATUS_DELIVERED,
        'payment_status' => Order::PAYMENT_ESCROW_HELD,
        'currency' => 'USD',
        'subtotal' => 50000,
        'shipping_total' => 2500,
        'grand_total' => 52500,
    ]);

    $subOrder = SubOrder::create([
        'order_id' => $order->id,
        'vendor_id' => $vendor->id,
        'reference' => 'VX-2026-DSP001-A',
        'status' => Order::STATUS_DELIVERED,
        'payout_status' => 'pending',
        'subtotal' => 50000,
        'total' => 52500,
        'vendor_payout_amount' => 47500,
    ]);

    $this->actingAs($buyer);

    $response = $this->post(route('account.disputes.store', ['order' => $order, 'subOrder' => $subOrder]), [
        'reason' => 'Damaged Cargo / COA mismatch',
        'description' => '5 drums arrived with broken seal and water contamination.',
    ]);

    $response->assertRedirect(route('account.orders.show', $order));

    $dispute = Dispute::firstOrFail();

    expect($dispute->buyer_id)->toBe($buyer->id)
        ->and($dispute->reason)->toBe('Damaged Cargo / COA mismatch')
        ->and($dispute->status)->toBe(Dispute::STATUS_OPEN)
        ->and($subOrder->fresh()->payout_status)->toBe('disputed');
});

it('allows message thread communication on open dispute', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();

    $order = Order::create([
        'reference' => 'VX-2026-DSP002',
        'buyer_id' => $buyer->id,
        'status' => Order::STATUS_DELIVERED,
        'payment_status' => Order::PAYMENT_ESCROW_HELD,
        'currency' => 'USD',
        'subtotal' => 10000,
        'shipping_total' => 0,
        'grand_total' => 10000,
    ]);

    $subOrder = SubOrder::create([
        'order_id' => $order->id,
        'vendor_id' => $vendor->id,
        'reference' => 'VX-2026-DSP002-A',
        'status' => Order::STATUS_DELIVERED,
        'subtotal' => 10000,
        'total' => 10000,
        'vendor_payout_amount' => 9500,
    ]);

    $dispute = Dispute::create([
        'reference' => 'DSP-2026-TEST99',
        'order_id' => $order->id,
        'sub_order_id' => $subOrder->id,
        'buyer_id' => $buyer->id,
        'vendor_id' => $vendor->id,
        'reason' => 'Weight discrepancy',
        'description' => 'Container weight short by 200kg.',
        'status' => Dispute::STATUS_OPEN,
    ]);

    $this->actingAs($buyer);

    // View thread
    $this->get(route('account.disputes.show', $dispute))
        ->assertOk()
        ->assertSee('DSP-2026-TEST99')
        ->assertSee('Weight discrepancy');

    // Post reply
    $this->post(route('account.disputes.reply', $dispute), [
        'message' => 'Attached weighbridge receipt PDF.',
    ])->assertRedirect();

    expect($dispute->messages)->toHaveCount(1)
        ->and($dispute->messages->first()->message)->toBe('Attached weighbridge receipt PDF.');
});
