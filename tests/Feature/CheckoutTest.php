<?php

use App\Models\CartItem;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\OrderPlaced;
use App\Notifications\RfqInvitation;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);

    // JSON test requests drop cookies unless credentials are enabled, and the
    // guest cart is bound to the session cookie.
    $this->withCredentials();
});

function checkoutPayload(array $overrides = []): array
{
    return array_merge([
        'contact_name' => 'Amina Mohamed',
        'company' => 'SunGrid Africa',
        'line1' => '14 Riverside Drive',
        'city' => 'Nairobi',
        'country_code' => 'KE',
        'phone' => '+254 700 123456',
        'incoterm' => 'CIF',
    ], $overrides);
}

it('adds a product to the cart honouring MOQ', function () {
    $product = Product::factory()->create(['moq' => 25, 'base_price' => 850]);

    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 5])
        ->assertOk()
        ->assertJsonPath('count', 25);
});

it('refuses to add quote-only products to the cart', function () {
    $product = Product::factory()->quoteOnly()->create();

    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1])
        ->assertStatus(422);
});

it('updates and removes cart items', function () {
    $product = Product::factory()->create(['moq' => 1, 'base_price' => 1000]);

    // Signed in, so the cart follows the account rather than the session cookie
    // (the test client does not replay Set-Cookie between requests).
    $this->actingAs(User::factory()->create());

    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 2]);

    $item = CartItem::firstOrFail();

    $this->patchJson(route('cart.items.update', $item), ['qty' => 7])
        ->assertOk()
        ->assertJsonPath('count', 7);

    $this->deleteJson(route('cart.items.destroy', $item))
        ->assertOk()
        ->assertJsonPath('count', 0);
});

it('will not let one buyer touch another buyer’s cart', function () {
    $product = Product::factory()->create();

    $this->actingAs(User::factory()->create());
    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1]);

    $item = CartItem::firstOrFail();

    $this->actingAs(User::factory()->create())
        ->patchJson(route('cart.items.update', $item), ['qty' => 5])
        ->assertForbidden();

    expect($item->fresh()->qty)->toBe($item->qty);
});

it('keeps checkout behind authentication', function () {
    $this->get(route('checkout'))->assertRedirect(route('login'));
});

it('splits an order into one sub-order per vendor and freezes commission', function () {
    Notification::fake();

    $buyer = User::factory()->create();
    $vendorA = Vendor::factory()->create(['commission_percent' => 5]);
    $vendorB = Vendor::factory()->create(['commission_percent' => 10]);

    $productA = Product::factory()->create(['vendor_id' => $vendorA->id, 'base_price' => 10_000, 'moq' => 1]);
    $productB = Product::factory()->create(['vendor_id' => $vendorB->id, 'base_price' => 20_000, 'moq' => 1]);

    $this->actingAs($buyer);
    $this->postJson(route('cart.items.store'), ['product_id' => $productA->id, 'qty' => 2]);
    $this->postJson(route('cart.items.store'), ['product_id' => $productB->id, 'qty' => 1]);

    $response = $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::firstOrFail();
    $response->assertRedirect(route('payment.show', $order));

    expect($order->subOrders)->toHaveCount(2)
        ->and($order->subtotal)->toBe(40_000)
        ->and($order->buyer_id)->toBe($buyer->id)
        ->and($order->payment_status)->toBe(Order::PAYMENT_UNPAID);

    $subA = $order->subOrders->firstWhere('vendor_id', $vendorA->id);
    $subB = $order->subOrders->firstWhere('vendor_id', $vendorB->id);

    // 5% of 20,000 and 10% of 20,000 respectively.
    expect($subA->commission_amount)->toBe(1_000)
        ->and($subB->commission_amount)->toBe(2_000)
        ->and($subA->vendor_payout_amount)->toBe($subA->total - $subA->commission_amount);

    // The cart is emptied and the buyer is notified.
    expect(CartItem::count())->toBe(0);
    Notification::assertSentTo($buyer, OrderPlaced::class);

    // Ledger keeps a sale and a commission line per vendor.
    expect(LedgerEntry::where('order_id', $order->id)->count())->toBe(4);
});

it('stops another buyer from opening a confirmation page', function () {
    $buyer = User::factory()->create();
    $stranger = User::factory()->create();
    $product = Product::factory()->create(['moq' => 1]);

    $this->actingAs($buyer);
    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1]);
    $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::firstOrFail();

    $this->actingAs($stranger)->get(route('checkout.confirmation', $order))->assertForbidden();
});

it('validates the checkout form', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['moq' => 1]);

    $this->actingAs($buyer);
    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1]);

    $this->post(route('checkout.store'), checkoutPayload(['line1' => '', 'incoterm' => 'NOPE']))
        ->assertSessionHasErrors(['line1', 'incoterm']);

    expect(Order::count())->toBe(0);
});

it('submits an RFQ and invites matching vendors', function () {
    Notification::fake();

    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $product = Product::factory()->create(['vendor_id' => $vendor->id]);

    $this->actingAs($buyer)->post(route('rfq.store'), [
        'product_id' => $product->id,
        'title' => 'Bulk order of 5 tons',
        'description' => 'Need WHO-GMP certified material with COA.',
        'qty' => 5,
        'unit' => 'ton',
        'destination_country' => 'AE',
        'incoterm' => 'CIF',
    ])->assertRedirect();

    $rfq = Rfq::firstOrFail();

    expect($rfq->buyer_id)->toBe($buyer->id)
        ->and($rfq->status)->toBe(Rfq::STATUS_OPEN)
        ->and($rfq->vendors)->toHaveCount(1);

    Notification::assertSentTo($vendor->owner, RfqInvitation::class);
});

it('shows the buyer their own RFQ only', function () {
    $buyer = User::factory()->create();
    $stranger = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $product = Product::factory()->create(['vendor_id' => $vendor->id]);

    $this->actingAs($buyer)->post(route('rfq.store'), [
        'product_id' => $product->id,
        'title' => 'Private request',
        'description' => 'Confidential requirement.',
        'qty' => 10,
        'unit' => 'kg',
        'destination_country' => 'GB',
        'incoterm' => 'FOB',
    ]);

    $rfq = Rfq::firstOrFail();

    $this->actingAs($buyer)->get(route('account.rfqs.show', $rfq))->assertOk()->assertSee('Private request');
    $this->actingAs($stranger)->get(route('account.rfqs.show', $rfq))->assertForbidden();
});
