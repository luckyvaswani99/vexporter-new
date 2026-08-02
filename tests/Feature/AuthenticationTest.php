<?php

use App\Models\BuyerProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('shows the login and register screens to guests', function () {
    $this->get(route('login'))->assertOk()->assertSee('Welcome back');
    $this->get(route('register'))->assertOk()->assertSee('Create your account');
});

it('registers a buyer with a profile and the buyer role', function () {
    Event::fake([Registered::class]);

    $this->post(route('register.store'), [
        'name' => 'Priya Sharma',
        'email' => 'priya@globaltrade.test',
        'phone' => '+971 50 123 4567',
        'company_name' => 'GlobalTrade LLC',
        'account_type' => 'buyer',
        'password' => 'export-secret-123',
        'password_confirmation' => 'export-secret-123',
        'terms' => '1',
    ])->assertRedirect(route('account.dashboard'));

    $user = User::where('email', 'priya@globaltrade.test')->firstOrFail();

    expect($user->type)->toBe(User::TYPE_BUYER)
        ->and($user->hasRole(RoleSeeder::ROLE_BUYER))->toBeTrue()
        ->and(BuyerProfile::where('user_id', $user->id)->exists())->toBeTrue();

    $this->assertAuthenticatedAs($user);
    Event::assertDispatched(Registered::class);
});

it('sends a new vendor straight to onboarding', function () {
    $this->post(route('register.store'), [
        'name' => 'Anil Mehta',
        'email' => 'anil@sunfab.test',
        'account_type' => 'vendor',
        'password' => 'export-secret-123',
        'password_confirmation' => 'export-secret-123',
        'terms' => '1',
    ])->assertRedirect(route('vendor.onboarding.create'));

    expect(User::where('email', 'anil@sunfab.test')->first()->hasRole(RoleSeeder::ROLE_VENDOR_OWNER))->toBeTrue();
});

it('requires the terms to be accepted', function () {
    $this->post(route('register.store'), [
        'name' => 'No Terms',
        'email' => 'noterms@example.test',
        'account_type' => 'buyer',
        'password' => 'export-secret-123',
        'password_confirmation' => 'export-secret-123',
    ])->assertSessionHasErrors('terms');

    $this->assertGuest();
});

it('logs a user in and records the login time', function () {
    $user = User::factory()->create(['password' => 'export-secret-123']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'export-secret-123',
    ])->assertRedirect(route('account.dashboard'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->last_login_at)->not->toBeNull();
});

it('rejects a wrong password', function () {
    $user = User::factory()->create(['password' => 'export-secret-123']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('blocks deactivated accounts', function () {
    $user = User::factory()->create(['password' => 'export-secret-123', 'is_active' => false]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'export-secret-123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs the user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

    $this->assertGuest();
});

it('keeps the account area behind auth', function () {
    $this->get(route('account.dashboard'))->assertRedirect(route('login'));
});

it('shows the buyer dashboard', function () {
    $user = User::factory()->create(['name' => 'Demo Buyer']);

    $this->actingAs($user)
        ->get(route('account.dashboard'))
        ->assertOk()
        ->assertSee('Hello, Demo Buyer')
        ->assertSee('Recent orders');
});
