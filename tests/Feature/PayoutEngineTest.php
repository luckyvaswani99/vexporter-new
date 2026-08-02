<?php

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Services\EscrowService;
use App\Services\PayoutService;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('holds escrow and auto-releases funds after retention period', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();

    $order = Order::create([
        'reference' => 'VX-2026-ESCROW01',
        'buyer_id' => $buyer->id,
        'status' => Order::STATUS_PENDING,
        'payment_status' => Order::PAYMENT_UNPAID,
        'currency' => 'USD',
        'subtotal' => 50000,
        'shipping_total' => 2500,
        'grand_total' => 52500,
    ]);

    $subOrder = SubOrder::create([
        'order_id' => $order->id,
        'vendor_id' => $vendor->id,
        'reference' => 'VX-2026-ESCROW01-A',
        'status' => Order::STATUS_DELIVERED,
        'subtotal' => 50000,
        'shipping_total' => 2500,
        'total' => 52500,
        'commission_amount' => 2500,
        'vendor_payout_amount' => 50000,
        'updated_at' => now()->subDays(10),
    ]);

    $escrowService = app(EscrowService::class);

    // Hold funds
    $escrowService->hold($order);
    expect($order->fresh()->payment_status)->toBe(Order::PAYMENT_ESCROW_HELD);

    // Auto release eligible
    $count = $escrowService->autoReleaseEligibleSubOrders(7);

    expect($count)->toBe(1)
        ->and($subOrder->fresh()->escrow_released_at)->not->toBeNull()
        ->and($subOrder->fresh()->payout_status)->toBe(SubOrder::PAYOUT_ELIGIBLE)
        ->and($order->fresh()->payment_status)->toBe(Order::PAYMENT_RELEASED);
});

it('generates payout batches, processes payouts and creates ledger entries', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create(['payout_method' => 'bank_transfer']);
    $order = Order::create([
        'reference' => 'VX-2026-PAYOUT01',
        'buyer_id' => $buyer->id,
        'status' => Order::STATUS_DELIVERED,
        'payment_status' => Order::PAYMENT_RELEASED,
        'currency' => 'USD',
        'subtotal' => 45000,
        'shipping_total' => 0,
        'grand_total' => 45000,
    ]);

    $subOrder = SubOrder::create([
        'order_id' => $order->id,
        'vendor_id' => $vendor->id,
        'reference' => 'VX-2026-PAYOUT01-A',
        'status' => Order::STATUS_DELIVERED,
        'payout_status' => SubOrder::PAYOUT_ELIGIBLE,
        'escrow_released_at' => now()->subDays(2),
        'subtotal' => 45000,
        'total' => 45000,
        'commission_amount' => 0,
        'vendor_payout_amount' => 45000,
    ]);

    $payoutService = app(PayoutService::class);

    // Generate batch
    $payouts = $payoutService->generateBatch($vendor->id);

    expect($payouts)->toHaveCount(1);
    $payout = $payouts->first();

    expect($payout->vendor_id)->toBe($vendor->id)
        ->and($payout->amount)->toBe(45000)
        ->and($payout->status)->toBe('pending');

    // Process Payout
    $success = $payoutService->processPayout($payout, 'bank_transfer');

    expect($success)->toBeTrue()
        ->and($payout->fresh()->status)->toBe('paid')
        ->and($subOrder->fresh()->payout_status)->toBe(SubOrder::PAYOUT_PAID);

    // Check Ledger
    $ledgerEntry = LedgerEntry::where('payout_id', $payout->id)->first();
    expect($ledgerEntry)->not->toBeNull()
        ->and($ledgerEntry->type)->toBe(LedgerEntry::TYPE_PAYOUT)
        ->and($ledgerEntry->debit)->toBe(45000);

    // Test CSV Export
    $csv = $payoutService->exportCsv($payout);
    expect($csv)->toContain('Payout ID')
        ->toContain($vendor->name)
        ->toContain('450.00');
});
