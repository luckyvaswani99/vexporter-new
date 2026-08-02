<?php

use App\Actions\Vendors\ApproveVendor;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('sends hardening headers on every response', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');

    expect($response->headers->get('Content-Security-Policy'))
        ->toContain("frame-ancestors 'self'")
        ->toContain("object-src 'none'")
        ->toContain("form-action 'self'");
});

it('throttles repeated failed logins', function () {
    RateLimiter::clear('');

    $user = User::factory()->create(['password' => 'export-secret-123']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
    }

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'export-secret-123'])
        ->assertSessionHasErrors('email');

    // Even the correct password is refused while the lockout is active.
    $this->assertGuest();
});

it('rate limits the public search suggestion endpoint', function () {
    $route = app('router')->getRoutes()->getByName('search.suggest');

    expect($route->gatherMiddleware())->toContain('throttle:60,1');
});

it('never exposes KYC paperwork to the public disk', function () {
    Storage::fake('local');

    $vendor = Vendor::factory()->create();
    $document = VendorDocument::create([
        'vendor_id' => $vendor->id,
        'type' => 'gst',
        'label' => 'GST',
        'status' => VendorDocument::STATUS_VERIFIED,
        'is_public' => false,
        'file_path' => 'vendor-documents/'.$vendor->id.'/gst.pdf',
    ]);

    Storage::disk('local')->put($document->file_path, 'sensitive');

    // Guests and unrelated buyers are refused; the owner and admins are not.
    $this->get(route('documents.vendor', $document))->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->get(route('documents.vendor', $document))
        ->assertForbidden();

    $this->actingAs($vendor->owner)
        ->get(route('documents.vendor', $document))
        ->assertOk();

    $admin = User::factory()->create(['type' => User::TYPE_ADMIN]);
    $this->actingAs($admin)->get(route('documents.vendor', $document))->assertOk();
});

it('gates product documents behind a buyer account', function () {
    Storage::fake('local');

    $product = Product::factory()->create();
    $document = ProductDocument::create([
        'product_id' => $product->id,
        'type' => 'coa',
        'label' => 'Certificate of analysis',
        'file_path' => 'product-documents/coa.pdf',
        'requires_login' => true,
    ]);

    Storage::disk('local')->put($document->file_path, 'coa');

    $this->get(route('documents.product', $document))->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->get(route('documents.product', $document))
        ->assertOk();
});

it('encrypts vendor payout account numbers at rest', function () {
    $vendor = Vendor::factory()->create();

    $account = $vendor->bankAccounts()->create([
        'account_holder' => 'SunFab Energy Pvt Ltd',
        'account_no' => '50100123456789',
        'bank_name' => 'HDFC Bank',
        'currency' => 'INR',
        'is_primary' => true,
    ]);

    $raw = DB::table('vendor_bank_accounts')->where('id', $account->id)->value('account_no');

    expect($raw)->not->toBe('50100123456789')
        ->and($account->fresh()->account_no)->toBe('50100123456789');
});

it('keeps the two-factor secret out of plain text', function () {
    $admin = User::factory()->create(['type' => User::TYPE_ADMIN]);

    $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

    $raw = DB::table('users')->where('id', $admin->id)->value('app_authentication_secret');

    expect($raw)->not->toBe('JBSWY3DPEHPK3PXP')
        ->and($admin->fresh()->getAppAuthenticationSecret())->toBe('JBSWY3DPEHPK3PXP');
});

it('writes an audit trail for vendor approval and product moderation', function () {
    $admin = User::factory()->create(['type' => User::TYPE_ADMIN]);
    $vendor = Vendor::factory()->pending()->create();

    app(ApproveVendor::class)->handle($vendor, $admin, 'Docs verified.');

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'vendor',
        'subject_type' => Vendor::class,
        'subject_id' => $vendor->id,
    ]);

    $product = Product::factory()->pendingApproval()->create(['vendor_id' => $vendor->id]);
    $product->update(['approval_status' => Product::APPROVAL_APPROVED]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'product',
        'subject_type' => Product::class,
        'subject_id' => $product->id,
    ]);
});

it('refuses mass assignment of protected user fields', function () {
    $user = User::factory()->create();

    $user->fill(['type' => User::TYPE_ADMIN, 'email_verified_at' => now()]);

    // `type` is fillable by design (registration sets it); verification is not.
    expect($user->isDirty('email_verified_at'))->toBeFalse();
});

it('does not leak stack traces when debug is off', function () {
    config(['app.debug' => false]);

    $this->get('/p/this-product-does-not-exist')
        ->assertNotFound()
        ->assertDontSee('vendor\\laravel\\framework');
});
