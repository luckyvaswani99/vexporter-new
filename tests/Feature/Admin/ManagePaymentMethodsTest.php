<?php

use App\Filament\Pages\ManagePaymentMethods;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Vendor;
use App\Support\PaymentMethods;
use App\Support\SiteSettings;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
    $this->withCredentials();
});

function financeAdmin(): User
{
    return User::factory()->create(['type' => 'admin'])->syncRoles(RoleSeeder::ROLE_ADMIN);
}

/** Drives the real cart → checkout flow, since orders have no factory. */
function payableOrder(): Order
{
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['vendor_id' => Vendor::factory()->create()->id, 'moq' => 1]);

    test()->actingAs($buyer);
    test()->postJson(route('cart.items.store'), ['product_id' => $product->id, 'qty' => 1]);
    test()->post(route('checkout.store'), [
        'contact_name' => 'John Doe',
        'line1' => '123 Main St',
        'city' => 'Mumbai',
        'country_code' => 'IN',
        'phone' => '+91 9876543210',
        'incoterm' => 'CIF',
    ]);

    return Order::firstOrFail();
}

it('is reachable by an admin', function () {
    $this->actingAs(financeAdmin())
        ->get(ManagePaymentMethods::getUrl())
        ->assertSuccessful();
});

it('is hidden from a support user without the settings permission', function () {
    $this->actingAs(User::factory()->create(['type' => 'admin'])->syncRoles(RoleSeeder::ROLE_SUPPORT))
        ->get(ManagePaymentMethods::getUrl())
        ->assertForbidden();
});

it('offers all three methods out of the box', function () {
    expect(PaymentMethods::enabledGateways())->toBe(['razorpay', 'stripe', 'bank_transfer']);
});

it('shows every enabled method on the payment page', function () {
    $order = payableOrder();

    $this->actingAs($order->buyer)
        ->get(route('payment.show', $order))
        ->assertSuccessful()
        ->assertSee('Razorpay')
        ->assertSee('Stripe')
        ->assertSee('Bank Wire / T/T');
});

it('lists the methods on the checkout page too', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('checkout'))
        // Empty cart bounces to the cart page; the copy is asserted below.
        ->assertRedirect(route('cart.index'));

    expect(collect(PaymentMethods::enabled())->pluck('label'))
        ->toContain('Razorpay', 'Stripe');
});

it('drops a method that has been switched off', function () {
    app(SiteSettings::class)->put(['payments.methods' => [
        ['gateway' => 'razorpay', 'enabled' => false],
        ['gateway' => 'stripe', 'enabled' => true],
        ['gateway' => 'bank_transfer', 'enabled' => false],
    ]]);

    $order = payableOrder();

    $this->actingAs($order->buyer)
        ->get(route('payment.show', $order))
        ->assertSuccessful()
        ->assertSee('Stripe')
        ->assertDontSee('Bank Wire / T/T');
});

it('refuses to charge through a disabled gateway', function () {
    app(SiteSettings::class)->put(['payments.methods' => [
        ['gateway' => 'razorpay', 'enabled' => false],
        ['gateway' => 'stripe', 'enabled' => true],
    ]]);

    $order = payableOrder();

    $this->actingAs($order->buyer)
        ->postJson(route('payment.process', $order), ['gateway' => 'razorpay'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('gateway');
});

it('reorders the methods as the admin arranged them', function () {
    app(SiteSettings::class)->put(['payments.methods' => [
        ['gateway' => 'bank_transfer', 'enabled' => true],
        ['gateway' => 'stripe', 'enabled' => true],
        ['gateway' => 'razorpay', 'enabled' => true],
    ]]);

    expect(PaymentMethods::enabledGateways())->toBe(['bank_transfer', 'stripe', 'razorpay']);
});

it('pre-selects a currency-appropriate method that is still enabled', function () {
    expect(PaymentMethods::recommendedFor('INR'))->toBe('razorpay')
        ->and(PaymentMethods::recommendedFor('USD'))->toBe('stripe');

    app(SiteSettings::class)->put(['payments.methods' => [
        ['gateway' => 'razorpay', 'enabled' => false],
        ['gateway' => 'stripe', 'enabled' => false],
        ['gateway' => 'bank_transfer', 'enabled' => true],
    ]]);

    expect(PaymentMethods::recommendedFor('INR'))->toBe('bank_transfer');
});

it('never prints a placeholder bank account', function () {
    $order = payableOrder();

    $this->actingAs($order->buyer)
        ->get(route('payment.show', $order))
        ->assertDontSee('50200012345678')
        ->assertSee('will email the beneficiary account details', escape: false);
});

it('prints the wire details once the admin has entered them', function () {
    app(SiteSettings::class)->put(['payments.bank' => [
        'beneficiary' => 'VEXPORTER GLOBAL LTD',
        'bank_name' => 'HDFC Bank Ltd',
        'account_number' => '99887766554433',
        'swift' => 'HDFCINBBXXX',
    ]]);

    $order = payableOrder();

    $this->actingAs($order->buyer)
        ->get(route('payment.show', $order))
        ->assertSee('99887766554433')
        ->assertSee('HDFCINBBXXX');

    $this->actingAs($order->buyer)
        ->get(route('payment.proforma', $order))
        ->assertSee('99887766554433');
});

it('saves edits from the admin form', function () {
    Livewire::actingAs(financeAdmin())
        ->test(ManagePaymentMethods::class)
        ->fillForm(['bank.beneficiary' => 'Acme Exports Ltd'])
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteSetting::where('key', 'payments.bank')->value('value'))
        ->toMatchArray(['beneficiary' => 'Acme Exports Ltd'])
        ->and(SiteSetting::where('key', 'payments.methods')->value('value'))
        ->toHaveCount(3);
});

it('fails preflight when no method is enabled', function () {
    app(SiteSettings::class)->put(['payments.methods' => [
        ['gateway' => 'razorpay', 'enabled' => false],
        ['gateway' => 'stripe', 'enabled' => false],
        ['gateway' => 'bank_transfer', 'enabled' => false],
    ]]);

    $this->artisan('vexporter:preflight')->assertFailed();
});
