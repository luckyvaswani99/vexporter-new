<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);

    // JSON test requests drop cookies unless credentials are enabled, and the
    // guest cart is bound to the session cookie.
    $this->withCredentials();
});

function variableProduct(): Product
{
    $vendor = Vendor::factory()->create(['status' => Vendor::STATUS_APPROVED]);

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'type' => Product::TYPE_VARIABLE,
        'base_price' => 10_000,
        'moq' => 1,
        'order_increment' => 1,
        'is_active' => true,
        'approval_status' => Product::APPROVAL_APPROVED,
    ]);

    ProductVariant::create(['product_id' => $product->id, 'name' => '540W', 'price' => 18_500, 'stock_qty' => 20, 'is_default' => true]);
    ProductVariant::create(['product_id' => $product->id, 'name' => '450W', 'price' => null, 'stock_qty' => 5]);

    return $product->fresh();
}

it('shows an option picker on a variable product', function () {
    $product = variableProduct();

    $this->get(route('products.show', $product))
        ->assertSuccessful()
        ->assertSee('Options')
        ->assertSee('540W')
        ->assertSee('450W');
});

it('does not show a picker on a simple product', function () {
    $product = variableProduct();
    $product->update(['type' => Product::TYPE_SIMPLE]);

    $this->get(route('products.show', $product->fresh()))
        ->assertSuccessful()
        ->assertDontSee('x-for="option in variants"', escape: false);
});

it('renders the related and same-vendor rails', function () {
    $product = variableProduct();

    // These reuse the homepage showcase component with literal copy rather than
    // a settings group — a regression here 500s every populated product page.
    Product::factory()->count(2)->create([
        'vendor_id' => $product->vendor_id,
        'category_id' => $product->category_id,
        'is_active' => true,
        'approval_status' => Product::APPROVAL_APPROVED,
    ]);

    $this->get(route('products.show', $product))
        ->assertSuccessful()
        ->assertSee('Similar products')
        ->assertSee('More from '.$product->vendor->name);
});

it('prices a cart line from the chosen option', function () {
    $product = variableProduct();
    $variant = $product->variants->firstWhere('name', '540W');

    $this->actingAs(User::factory()->create())
        ->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 2, 'variant_id' => $variant->id])
        ->assertOk();

    $item = CartItem::sole();

    expect($item->unit_price)->toBe(18_500)
        ->and($item->product_variant_id)->toBe($variant->id)
        ->and($item->snapshot['variant'])->toBe('540W');
});

it('falls back to the base price for an option priced at null', function () {
    $product = variableProduct();
    $variant = $product->variants->firstWhere('name', '450W');

    $this->actingAs(User::factory()->create())
        ->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1, 'variant_id' => $variant->id])
        ->assertOk();

    expect(CartItem::sole()->unit_price)->toBe(10_000);
});

it('refuses to add a variable product without an option', function () {
    $product = variableProduct();

    $this->actingAs(User::factory()->create())
        ->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1])
        ->assertStatus(422)
        ->assertJsonValidationErrors('variant_id');

    expect(CartItem::count())->toBe(0);
});

it('refuses an option belonging to a different product', function () {
    $product = variableProduct();
    $foreign = variableProduct()->variants->first();

    $this->actingAs(User::factory()->create())
        ->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1, 'variant_id' => $foreign->id])
        ->assertStatus(422);

    expect(CartItem::count())->toBe(0);
});

it('ignores a variant id sent for a simple product', function () {
    $product = variableProduct();
    $variant = $product->variants->first();
    $product->update(['type' => Product::TYPE_SIMPLE]);

    $this->actingAs(User::factory()->create())
        ->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1, 'variant_id' => $variant->id])
        ->assertOk();

    $item = CartItem::sole();

    expect($item->product_variant_id)->toBeNull()
        ->and($item->unit_price)->toBe(10_000);
});

it('carries the option through to the order line', function () {
    $product = variableProduct();
    $variant = $product->variants->firstWhere('name', '540W');

    $this->actingAs(User::factory()->create())
        ->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 3, 'variant_id' => $variant->id])
        ->assertOk();

    $this->post(route('checkout.store'), [
        'contact_name' => 'Amina Mohamed',
        'company' => 'SunGrid Africa',
        'line1' => '14 Riverside Drive',
        'city' => 'Nairobi',
        'country_code' => 'KE',
        'phone' => '+254 700 123456',
        'incoterm' => 'CIF',
    ]);

    $line = Order::sole()->subOrders->first()->items->first();

    expect($line->product_variant_id)->toBe($variant->id)
        ->and($line->unit_price)->toBe(18_500)
        ->and($line->total)->toBe(55_500);
});
