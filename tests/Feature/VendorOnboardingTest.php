<?php

use App\Actions\Vendors\ApproveVendor;
use App\Actions\Vendors\RejectVendor;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Models\Vertical;
use App\Notifications\VendorApplicationReceived;
use App\Notifications\VendorApproved;
use App\Notifications\VendorRejected;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);
});

function application(array $overrides = []): array
{
    return array_merge([
        'name' => 'SunFab Energy',
        'legal_name' => 'SunFab Energy Pvt Ltd',
        'about' => 'Tier-1 solar module manufacturer.',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'country_code' => 'IN',
        'gst_number' => '24AAACS1234A1Z5',
        'pan' => 'AAACS1234A',
        'iec_code' => '0912345678',
        'verticals' => [Vertical::where('slug', 'solar')->value('id')],
        'account_holder' => 'SunFab Energy Pvt Ltd',
        'account_no' => '50100123456789',
        'ifsc' => 'HDFC0001234',
        'bank_name' => 'HDFC Bank',
        'payout_currency' => 'INR',
        'declaration' => '1',
    ], $overrides);
}

it('shows the onboarding wizard to a signed-in user', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('vendor.onboarding.create'))
        ->assertOk()
        ->assertSee('Become a VEXPORTER vendor')
        ->assertSee('Statutory registration');
});

it('creates a pending vendor with documents, payout account and an audit log', function () {
    Notification::fake();
    Storage::fake('local');

    $admin = User::factory()->create(['type' => User::TYPE_ADMIN]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('vendor.onboarding.store'), application([
            'documents' => [
                ['label' => 'ALMM', 'number' => 'ALMM-2026-001', 'file' => UploadedFile::fake()->create('almm.pdf', 120, 'application/pdf')],
            ],
        ]))
        ->assertRedirect(route('vendor.onboarding.status'));

    $vendor = Vendor::where('slug', 'sunfab-energy')->firstOrFail();

    expect($vendor->status)->toBe(Vendor::STATUS_PENDING)
        ->and($vendor->user_id)->toBe($user->id)
        ->and($vendor->staff()->whereKey($user->id)->exists())->toBeTrue()
        ->and($vendor->bankAccounts()->count())->toBe(1)
        ->and($vendor->kycLogs()->where('action', 'application_submitted')->exists())->toBeTrue()
        ->and($user->fresh()->type)->toBe(User::TYPE_VENDOR);

    // GST + PAN + IEC captured as documents, plus the uploaded certificate.
    expect($vendor->documents()->count())->toBe(4);

    $certificate = $vendor->documents()->where('label', 'ALMM')->firstOrFail();
    expect($certificate->file_path)->not->toBeNull();
    Storage::disk('local')->assertExists($certificate->file_path);

    Notification::assertSentTo($admin, VendorApplicationReceived::class);
});

it('stores payout account numbers encrypted', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('vendor.onboarding.store'), application());

    $vendor = Vendor::where('slug', 'sunfab-energy')->firstOrFail();
    $stored = DB::table('vendor_bank_accounts')->where('vendor_id', $vendor->id)->value('account_no');

    expect($stored)->not->toBe('50100123456789')
        ->and($vendor->bankAccounts()->first()->account_no)->toBe('50100123456789');
});

it('validates the application', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('vendor.onboarding.store'), application([
            'gst_number' => '',
            'verticals' => [],
            'declaration' => null,
        ]))
        ->assertSessionHasErrors(['gst_number', 'verticals', 'declaration']);

    expect(Vendor::count())->toBe(0);
});

it('does not let a vendor apply twice', function () {
    $vendor = Vendor::factory()->create();

    $this->actingAs($vendor->owner)
        ->get(route('vendor.onboarding.create'))
        ->assertRedirect(route('vendor.onboarding.status'));

    $this->actingAs($vendor->owner)
        ->post(route('vendor.onboarding.store'), application())
        ->assertForbidden();
});

it('approves a vendor, verifies its documents and notifies the owner', function () {
    Notification::fake();

    $admin = User::factory()->create(['type' => User::TYPE_ADMIN]);
    $vendor = Vendor::factory()->pending()->create();
    $vendor->documents()->create(['type' => 'gst', 'label' => 'GST', 'status' => VendorDocument::STATUS_PENDING]);

    app(ApproveVendor::class)->handle($vendor, $admin, 'Docs verified.');

    expect($vendor->fresh()->status)->toBe(Vendor::STATUS_APPROVED)
        ->and($vendor->fresh()->approved_by)->toBe($admin->id)
        ->and($vendor->documents()->where('status', VendorDocument::STATUS_VERIFIED)->count())->toBe(1)
        ->and($vendor->kycLogs()->where('action', 'approved')->exists())->toBeTrue();

    Notification::assertSentTo($vendor->owner, VendorApproved::class);
});

it('rejects a vendor with a reason', function () {
    Notification::fake();

    $admin = User::factory()->create(['type' => User::TYPE_ADMIN]);
    $vendor = Vendor::factory()->pending()->create();

    app(RejectVendor::class)->handle($vendor, $admin, 'Drug licence has expired.');

    expect($vendor->fresh()->status)->toBe(Vendor::STATUS_REJECTED)
        ->and($vendor->fresh()->rejection_reason)->toBe('Drug licence has expired.');

    Notification::assertSentTo($vendor->owner, VendorRejected::class);
});

it('shows the application status page', function () {
    $vendor = Vendor::factory()->pending()->create();

    $this->actingAs($vendor->owner)
        ->get(route('vendor.onboarding.status'))
        ->assertOk()
        ->assertSee('Under review')
        ->assertSee($vendor->name);
});
