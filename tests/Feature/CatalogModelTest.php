<?php

use App\Models\Product;
use App\Models\ProductTierPrice;
use App\Models\Vendor;
use App\Support\Money;

it('formats money the way the catalogue quotes it', function () {
    expect(Money::format(850, 'USD'))->toBe('$8.50')
        ->and(Money::format(4500, 'USD'))->toBe('$45.00')
        ->and(Money::format(32000, 'USD'))->toBe('$320')
        ->and(Money::format(125000, 'USD'))->toBe('$1,250')
        ->and(Money::format(14500000, 'USD'))->toBe('$145,000')
        ->and(Money::format(null))->toBeNull();
});

it('hides products from unapproved vendors', function () {
    $approved = Vendor::factory()->create();
    $pending = Vendor::factory()->pending()->create();

    Product::factory()->create(['vendor_id' => $approved->id]);
    Product::factory()->create(['vendor_id' => $pending->id]);

    expect(Product::visible()->count())->toBe(1);
});

it('hides products that are inactive or awaiting approval', function () {
    $vendor = Vendor::factory()->create();

    Product::factory()->create(['vendor_id' => $vendor->id]);
    Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => false]);
    Product::factory()->pendingApproval()->create(['vendor_id' => $vendor->id]);

    expect(Product::visible()->count())->toBe(1);
});

it('applies tier pricing based on quantity', function () {
    $product = Product::factory()->create(['base_price' => 10_00, 'moq' => 10]);

    ProductTierPrice::insert([
        ['product_id' => $product->id, 'min_qty' => 10, 'max_qty' => 49, 'price' => 10_00, 'currency' => 'USD'],
        ['product_id' => $product->id, 'min_qty' => 50, 'max_qty' => 199, 'price' => 9_00, 'currency' => 'USD'],
        ['product_id' => $product->id, 'min_qty' => 200, 'max_qty' => null, 'price' => 8_00, 'currency' => 'USD'],
    ]);

    $product->load('tierPrices');

    expect($product->priceForQty(10))->toBe(1000)
        ->and($product->priceForQty(75))->toBe(900)
        ->and($product->priceForQty(5000))->toBe(800);
});

it('treats EPC and licensed products as quote only', function () {
    $simple = Product::factory()->create();
    $epc = Product::factory()->create(['type' => Product::TYPE_SERVICE_EPC]);
    $licensed = Product::factory()->create(['requires_license' => true]);

    expect($simple->is_quote_only)->toBeFalse()
        ->and($epc->is_quote_only)->toBeTrue()
        ->and($licensed->is_quote_only)->toBeTrue();
});

it('exposes the vendor certifications on the card', function () {
    $vendor = Vendor::factory()->create();

    $vendor->documents()->createMany([
        ['type' => 'fda', 'label' => 'FDA', 'status' => 'verified', 'is_public' => true],
        ['type' => 'who_gmp', 'label' => 'WHO-GMP', 'status' => 'verified', 'is_public' => true],
        ['type' => 'pending_one', 'label' => 'EU-GMP', 'status' => 'pending', 'is_public' => true],
    ]);

    expect($vendor->fresh()->certifications)->toBe(['FDA', 'WHO-GMP']);
});
