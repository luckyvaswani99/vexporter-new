<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function approvedVendorFor(User $user): Vendor
{
    $vendor = Vendor::factory()->create(['user_id' => $user->id]);
    $vendor->staff()->attach($user->id, ['role' => 'owner']);
    $user->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);

    return $vendor;
}

it('lets an admin into the admin panel', function () {
    $admin = User::factory()->create(['type' => User::TYPE_ADMIN]);
    $admin->syncRoles(RoleSeeder::ROLE_ADMIN);

    $this->actingAs($admin)->get('/admin')->assertSuccessful();
});

it('keeps buyers and vendors out of the admin panel', function () {
    $buyer = User::factory()->create();
    $vendorOwner = User::factory()->create(['type' => User::TYPE_VENDOR]);
    approvedVendorFor($vendorOwner);

    $this->actingAs($buyer)->get('/admin')->assertForbidden();
    $this->actingAs($vendorOwner)->get('/admin')->assertForbidden();
});

it('sends guests to the panel login screen', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('lets an approved vendor into its own store panel', function () {
    $owner = User::factory()->create(['type' => User::TYPE_VENDOR]);
    $vendor = approvedVendorFor($owner);

    $this->actingAs($owner)->get("/vendor/store/{$vendor->slug}")->assertSuccessful();
});

it('blocks a vendor from opening another store', function () {
    $owner = User::factory()->create(['type' => User::TYPE_VENDOR]);
    approvedVendorFor($owner);

    $otherOwner = User::factory()->create(['type' => User::TYPE_VENDOR]);
    $otherVendor = approvedVendorFor($otherOwner);

    // Filament hides stores the user is not a member of rather than admitting
    // they exist, so this is a 404 not a 403.
    $this->actingAs($owner)->get("/vendor/store/{$otherVendor->slug}")->assertNotFound();
});

it('keeps a pending vendor out of the store panel', function () {
    $owner = User::factory()->create(['type' => User::TYPE_VENDOR]);
    $vendor = Vendor::factory()->pending()->create(['user_id' => $owner->id]);
    $vendor->staff()->attach($owner->id, ['role' => 'owner']);

    $this->actingAs($owner)->get("/vendor/store/{$vendor->slug}")->assertForbidden();
});

it('scopes the vendor product list to the signed-in store', function () {
    $owner = User::factory()->create(['type' => User::TYPE_VENDOR]);
    $vendor = approvedVendorFor($owner);

    $mine = Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'My Solar Panel']);
    $theirs = Product::factory()->create(['name' => 'Someone Elses Panel']);

    $this->actingAs($owner)
        ->get("/vendor/store/{$vendor->slug}/products")
        ->assertSuccessful()
        ->assertSee($mine->name)
        ->assertDontSee($theirs->name);
});

it('shows the admin vendor queue with pending applications', function () {
    $admin = User::factory()->create(['type' => User::TYPE_ADMIN]);
    $admin->syncRoles(RoleSeeder::ROLE_ADMIN);

    $pending = Vendor::factory()->pending()->create(['name' => 'Awaiting Review Labs']);

    $this->actingAs($admin)
        ->get('/admin/vendors')
        ->assertSuccessful()
        ->assertSee($pending->name);
});
