<?php

use App\Actions\Checkout\PlaceOrder;
use App\Models\Product;
use App\Models\ProductTierPrice;

/**
 * Pricing rules that money depends on, exercised without the HTTP layer.
 */
it('picks the tier that matches the ordered quantity', function (int $qty, int $expected) {
    $product = Product::factory()->make(['base_price' => 10_00, 'moq' => 10]);
    $product->setRelation('tierPrices', collect([
        new ProductTierPrice(['min_qty' => 10, 'max_qty' => 49, 'price' => 10_00]),
        new ProductTierPrice(['min_qty' => 50, 'max_qty' => 199, 'price' => 9_00]),
        new ProductTierPrice(['min_qty' => 200, 'max_qty' => null, 'price' => 8_00]),
    ]));

    expect($product->priceForQty($qty))->toBe($expected);
})->with([
    'at MOQ' => [10, 1000],
    'inside first slab' => [49, 1000],
    'first quantity of second slab' => [50, 900],
    'inside second slab' => [199, 900],
    'open-ended top slab' => [200, 800],
    'far above the top slab' => [10_000, 800],
]);

it('falls back to the base price when no slab matches', function () {
    $product = Product::factory()->make(['base_price' => 12_50, 'moq' => 1]);
    $product->setRelation('tierPrices', collect([
        new ProductTierPrice(['min_qty' => 100, 'max_qty' => 200, 'price' => 10_00]),
    ]));

    expect($product->priceForQty(5))->toBe(1250);
});

it('treats EPC and licensed products as quote-only', function () {
    expect(Product::factory()->make()->requiresQuote())->toBeFalse()
        ->and(Product::factory()->make(['type' => Product::TYPE_QUOTE_ONLY])->requiresQuote())->toBeTrue()
        ->and(Product::factory()->make(['type' => Product::TYPE_SERVICE_EPC])->requiresQuote())->toBeTrue()
        ->and(Product::factory()->make(['requires_license' => true])->requiresQuote())->toBeTrue();
});

it('charges the freight minimum on small orders and a percentage on large ones', function (int $subtotal, int $expected) {
    $freight = max(PlaceOrder::SHIPPING_MINIMUM, (int) round($subtotal * PlaceOrder::SHIPPING_PERCENT / 100));

    expect($freight)->toBe($expected);
})->with([
    'tiny order hits the minimum' => [10_000, 2500],
    'break-even point' => [100_000, 3000],
    'large order scales' => [10_000_000, 300_000],
]);

it('formats unit labels for the storefront', function () {
    expect(Product::factory()->make(['unit' => 'kg'])->unit_label)->toBe('/ kg')
        ->and(Product::factory()->make(['unit' => 'turnkey'])->unit_label)->toBe('turnkey')
        ->and(Product::factory()->make(['unit' => 'drum'])->unit_label)->toBe('/ drum');
});
