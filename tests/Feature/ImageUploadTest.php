<?php

use App\Filament\Vendor\Resources\Products\Pages\CreateProduct;
use App\Filament\Vendor\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Storage::fake('public');

    $this->owner = User::factory()->create(['type' => User::TYPE_VENDOR]);
    $this->vendor = Vendor::factory()->create(['user_id' => $this->owner->id]);
    $this->vendor->staff()->attach($this->owner->id, ['role' => 'owner']);
    $this->owner->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);

    $this->actingAs($this->owner);
    Filament::setCurrentPanel('vendor');
    Filament::bootCurrentPanel();
    Filament::setTenant($this->vendor);
});

it('lets a vendor upload product photos and enter prices in normal units', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Solar Panel 540W',
            'category_id' => $category->id,
            'base_price' => '12.50',
            'currency' => 'USD',
            'unit' => 'unit',
            'moq' => 1,
            'stock_qty' => 10,
            'image_files' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('back.jpg'),
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('name', 'Solar Panel 540W')->firstOrFail();

    expect($product->base_price)->toBe(1250)
        ->and($product->slug)->toBe('solar-panel-540w')
        ->and($product->vendor_id)->toBe($this->vendor->id)
        ->and($product->vertical_id)->toBe($category->vertical_id)
        ->and($product->images)->toHaveCount(2)
        ->and($product->images->first()->is_primary)->toBeTrue()
        ->and($product->primary_image)->toBe($product->images->first()->path);

    Storage::disk('public')->assertExists($product->images->first()->path);
});

it('shows saved photos when editing and drops the ones removed', function () {
    $product = Product::factory()->create(['vendor_id' => $this->vendor->id, 'base_price' => 850]);
    Storage::disk('public')->put('products/a.jpg', 'x');
    Storage::disk('public')->put('products/b.jpg', 'x');
    $product->images()->createMany([
        ['path' => 'products/a.jpg', 'sort_order' => 0, 'is_primary' => true],
        ['path' => 'products/b.jpg', 'sort_order' => 1, 'is_primary' => false],
    ]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertFormSet(['base_price' => 8.5])
        ->fillForm(['image_files' => ['products/b.jpg']])
        ->call('save')
        ->assertHasNoFormErrors();

    $product->refresh();

    expect($product->base_price)->toBe(850)
        ->and($product->images->pluck('path')->all())->toBe(['products/b.jpg'])
        ->and($product->images->first()->is_primary)->toBeTrue();

    Storage::disk('public')->assertMissing('products/a.jpg');
});

it('shows an uploaded store logo on the vendor page', function () {
    $this->vendor->update(['logo' => 'vendors/logos/acme.png']);

    expect($this->vendor->fresh()->logo_url)->toContain('vendors/logos/acme.png');

    $this->get(route('vendors.show', $this->vendor))->assertSee('vendors/logos/acme.png', escape: false);
});

it('lets a buyer attach photos to a quote request', function () {
    $buyer = User::factory()->create();

    $this->actingAs($buyer)
        ->post(route('rfq.store'), [
            'title' => 'Solar inverters',
            'description' => 'Need 10 units',
            'qty' => 10,
            'unit' => 'unit',
            'destination_country' => 'US',
            'incoterm' => 'FOB',
            'attachments' => [UploadedFile::fake()->image('spec.png'), UploadedFile::fake()->create('spec.pdf', 100, 'application/pdf')],
        ])
        ->assertRedirect();

    $rfq = Rfq::firstOrFail();

    expect($rfq->attachments)->toHaveCount(2);
    Storage::disk('public')->assertExists($rfq->attachments[0]['path']);
});

it('rejects non-image attachments on a quote request', function () {
    $buyer = User::factory()->create();

    $this->actingAs($buyer)
        ->post(route('rfq.store'), [
            'title' => 'x', 'description' => 'x', 'qty' => 1, 'unit' => 'unit',
            'destination_country' => 'US', 'incoterm' => 'FOB',
            'attachments' => [UploadedFile::fake()->create('virus.exe', 10)],
        ])
        ->assertSessionHasErrors('attachments.0');
});
