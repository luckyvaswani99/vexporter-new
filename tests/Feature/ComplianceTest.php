<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SubOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Vertical;
use App\Services\ComplianceService;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('blocks pharma order fulfillment if vendor lacks drug license', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create(); // No drug license document attached

    $pharmaVertical = Vertical::where('slug', 'pharma')->firstOrFail();
    $category = Category::where('vertical_id', $pharmaVertical->id)->firstOrFail();

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'vertical_id' => $pharmaVertical->id,
        'category_id' => $category->id,
        'name' => 'Paracetamol API Grade BP',
    ]);

    $order = Order::create([
        'reference' => 'VX-2026-CMP001',
        'buyer_id' => $buyer->id,
        'status' => Order::STATUS_CONFIRMED,
        'payment_status' => Order::PAYMENT_ESCROW_HELD,
        'currency' => 'USD',
        'subtotal' => 10000,
        'shipping_total' => 0,
        'grand_total' => 10000,
    ]);

    $subOrder = SubOrder::create([
        'order_id' => $order->id,
        'vendor_id' => $vendor->id,
        'reference' => 'VX-2026-CMP001-A',
        'status' => Order::STATUS_CONFIRMED,
        'subtotal' => 10000,
        'total' => 10000,
        'vendor_payout_amount' => 9500,
    ]);

    OrderItem::create([
        'sub_order_id' => $subOrder->id,
        'product_id' => $product->id,
        'name_snapshot' => $product->name,
        'sku' => $product->sku,
        'qty' => 100,
        'unit' => 'kg',
        'unit_price' => 100,
        'total' => 10000,
    ]);

    $complianceService = app(ComplianceService::class);

    expect(fn () => $complianceService->validateFulfillment($subOrder))
        ->toThrow(ValidationException::class);
});

it('passes pharma order fulfillment when vendor drug license is valid', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();

    // Attach valid Drug License document
    $vendor->documents()->create([
        'type' => 'drug_license',
        'number' => 'DL-MH-123456',
        'status' => 'verified',
    ]);

    $pharmaVertical = Vertical::where('slug', 'pharma')->firstOrFail();
    $category = Category::where('vertical_id', $pharmaVertical->id)->firstOrFail();

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'vertical_id' => $pharmaVertical->id,
        'category_id' => $category->id,
    ]);

    $order = Order::create([
        'reference' => 'VX-2026-CMP002',
        'buyer_id' => $buyer->id,
        'status' => Order::STATUS_CONFIRMED,
        'payment_status' => Order::PAYMENT_ESCROW_HELD,
        'currency' => 'USD',
        'subtotal' => 10000,
        'shipping_total' => 0,
        'grand_total' => 10000,
    ]);

    $subOrder = SubOrder::create([
        'order_id' => $order->id,
        'vendor_id' => $vendor->id,
        'reference' => 'VX-2026-CMP002-A',
        'status' => Order::STATUS_CONFIRMED,
        'subtotal' => 10000,
        'total' => 10000,
        'vendor_payout_amount' => 9500,
    ]);

    OrderItem::create([
        'sub_order_id' => $subOrder->id,
        'product_id' => $product->id,
        'name_snapshot' => $product->name,
        'sku' => $product->sku,
        'qty' => 100,
        'unit' => 'kg',
        'unit_price' => 100,
        'total' => 10000,
        'batch_no' => 'BATCH-2026-01',
    ]);

    $complianceService = app(ComplianceService::class);

    // Should complete without throwing
    $complianceService->validateFulfillment($subOrder);

    expect(true)->toBeTrue();
});
