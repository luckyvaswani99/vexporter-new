<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
    $this->withCredentials();
});

it('renders payment page for order owner and blocks unauthorized users', function () {
    $buyer = User::factory()->create();
    $stranger = User::factory()->create();

    $vendor = Vendor::factory()->create();
    $product = Product::factory()->create(['vendor_id' => $vendor->id, 'moq' => 1]);

    $this->actingAs($buyer);
    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1]);
    $this->post(route('checkout.store'), [
        'contact_name' => 'John Doe',
        'line1' => '123 Main St',
        'city' => 'Mumbai',
        'country_code' => 'IN',
        'phone' => '+91 9876543210',
        'incoterm' => 'CIF',
    ]);

    $order = Order::firstOrFail();

    // Owner can access payment screen
    $this->actingAs($buyer)
        ->get(route('payment.show', $order))
        ->assertOk()
        ->assertSee('Complete Payment')
        ->assertSee($order->reference);

    // Stranger cannot access
    $this->actingAs($stranger)
        ->get(route('payment.show', $order))
        ->assertForbidden();
});

it('processes payment intent and completes order via callback', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $product = Product::factory()->create(['vendor_id' => $vendor->id, 'moq' => 1]);

    $this->actingAs($buyer);
    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1]);
    $this->post(route('checkout.store'), [
        'contact_name' => 'Jane Smith',
        'line1' => '456 Park Ave',
        'city' => 'New York',
        'country_code' => 'US',
        'phone' => '+1 555 123456',
        'incoterm' => 'FOB',
    ]);

    $order = Order::firstOrFail();

    // Process Razorpay intent
    $this->postJson(route('payment.process', $order), ['gateway' => 'razorpay'])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('gateway', 'razorpay');

    // Complete payment
    $this->postJson(route('payment.complete', $order), [
        'gateway' => 'razorpay',
        'razorpay_payment_id' => 'pay_test_callback_123',
    ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($order->fresh()->payment_status)->toBe(Order::PAYMENT_ESCROW_HELD);
});

it('supports proforma invoice generation for wire transfers', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $product = Product::factory()->create(['vendor_id' => $vendor->id, 'moq' => 1]);

    $this->actingAs($buyer);
    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1]);
    $this->post(route('checkout.store'), [
        'contact_name' => 'Global Logistics Ltd',
        'line1' => '789 Trade Tower',
        'city' => 'Dubai',
        'country_code' => 'AE',
        'phone' => '+971 4 1234567',
        'incoterm' => 'EXW',
    ]);

    $order = Order::firstOrFail();

    $this->actingAs($buyer)
        ->get(route('payment.proforma', $order))
        ->assertOk()
        ->assertSee('PROFORMA INVOICE')
        ->assertSee($order->reference);
});
