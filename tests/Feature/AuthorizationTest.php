<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\SubOrder;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('lets a vendor manage only its own products', function () {
    $mine = Vendor::factory()->create();
    $theirs = Vendor::factory()->create();

    $owner = $mine->owner;
    $owner->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);

    $myProduct = Product::factory()->create(['vendor_id' => $mine->id]);
    $theirProduct = Product::factory()->create(['vendor_id' => $theirs->id]);

    expect($owner->can('update', $myProduct))->toBeTrue()
        ->and($owner->can('update', $theirProduct))->toBeFalse()
        ->and($owner->can('delete', $theirProduct))->toBeFalse();
});

it('lets vendor staff manage the store they were invited to', function () {
    $vendor = Vendor::factory()->create();
    $staff = User::factory()->create(['type' => User::TYPE_VENDOR]);
    $staff->syncRoles(RoleSeeder::ROLE_VENDOR_STAFF);
    $vendor->staff()->attach($staff->id, ['role' => 'staff']);

    $product = Product::factory()->create(['vendor_id' => $vendor->id]);

    expect($staff->can('update', $product))->toBeTrue();
});

it('stops a vendor from approving its own products', function () {
    $vendor = Vendor::factory()->create();
    $owner = $vendor->owner;
    $owner->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);

    $product = Product::factory()->pendingApproval()->create(['vendor_id' => $vendor->id]);

    expect($owner->can('approve', $product))->toBeFalse();
});

it('gives admins full access', function () {
    $admin = User::factory()->create(['type' => User::TYPE_ADMIN]);
    $admin->syncRoles(RoleSeeder::ROLE_ADMIN);

    $product = Product::factory()->create();
    $vendor = Vendor::factory()->pending()->create();

    expect($admin->can('update', $product))->toBeTrue()
        ->and($admin->can('approve', $product))->toBeTrue()
        ->and($admin->can('approve', $vendor))->toBeTrue();
});

it('keeps orders private to the buyer and the fulfilling vendor', function () {
    $vendor = Vendor::factory()->create();
    $buyer = User::factory()->create();
    $stranger = User::factory()->create();

    $order = Order::create([
        'reference' => 'VX-TEST-000001',
        'buyer_id' => $buyer->id,
        'currency' => 'USD',
        'placed_at' => now(),
    ]);

    $subOrder = SubOrder::create([
        'order_id' => $order->id,
        'vendor_id' => $vendor->id,
        'reference' => 'VX-TEST-000001-A',
    ]);

    expect($buyer->can('view', $order))->toBeTrue()
        ->and($vendor->owner->can('view', $order))->toBeTrue()
        ->and($stranger->can('view', $order))->toBeFalse()
        ->and($stranger->can('view', $subOrder))->toBeFalse()
        ->and($buyer->can('view', $subOrder))->toBeTrue();
});

it('only lets approved vendors create products', function () {
    $approved = Vendor::factory()->create();
    $pending = Vendor::factory()->pending()->create();

    $approved->owner->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);
    $pending->owner->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);

    expect($approved->owner->can('create', Product::class))->toBeTrue()
        ->and($pending->owner->can('create', Product::class))->toBeFalse();
});
