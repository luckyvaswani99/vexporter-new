<?php

use App\Filament\Vendor\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Vertical;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->owner = User::factory()->create(['type' => User::TYPE_VENDOR]);
    $this->vendor = Vendor::factory()->create(['user_id' => $this->owner->id]);
    $this->vendor->staff()->attach($this->owner->id, ['role' => 'owner']);
    $this->owner->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);

    $this->actingAs($this->owner);
    Filament::setCurrentPanel('vendor');
    Filament::bootCurrentPanel();
    Filament::setTenant($this->vendor);

    $this->pharma = Vertical::factory()->create(['slug' => 'pharma', 'name' => 'Pharma']);
    $this->category = Category::factory()->create(['vertical_id' => $this->pharma->id, 'name' => 'Analgesics']);
});

it('fills in slug, SKU, HS code, SEO title and snippet when the seller leaves them blank', function () {
    $product = Product::factory()->create([
        'vendor_id' => $this->vendor->id,
        'vertical_id' => $this->pharma->id,
        'category_id' => $this->category->id,
        'name' => 'Paracetamol 650 mg Tablets',
        'slug' => '',
        'sku' => null,
        'hsn_code' => null,
        'seo_title' => null,
        'seo_description' => null,
        'short_description' => null,
        'description' => '<p>Fever and pain relief tablets for export.</p>',
    ]);

    expect($product->slug)->toBe('paracetamol-650-mg-tablets')
        ->and($product->sku)->toStartWith('VX-'.$this->vendor->id.'-')
        ->and($product->hsn_code)->toBe('3004.90')
        ->and($product->seo_title)->toBe('Paracetamol 650 mg Tablets | '.config('app.name'))
        ->and($product->short_description)->toContain('Fever and pain relief')
        ->and($product->seo_description)->toContain('Fever and pain relief');
});

it('keeps slugs unique when two sellers list the same name', function () {
    $make = fn () => Product::factory()->create([
        'name' => 'Same Name', 'slug' => '', 'category_id' => $this->category->id, 'vertical_id' => $this->pharma->id,
    ]);

    expect($make()->slug)->toBe('same-name')->and($make()->slug)->toBe('same-name-2');
});

it('saves pharma details from the vendor form', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Paracetamol 650 mg',
            'category_id' => $this->category->id,
            'base_price' => '1.20',
            'currency' => 'USD',
            'unit' => 'unit',
            'moq' => 100,
            'stock_qty' => 5000,
            'generic_name' => 'Paracetamol',
            'strength' => '650 mg',
            'dosage_form' => 'Tablet',
            'ingredients' => ['Paracetamol 650 mg'],
            'country_of_origin' => 'IN',
            'is_cold_chain' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('name', 'Paracetamol 650 mg')->firstOrFail();

    expect($product->generic_name)->toBe('Paracetamol')
        ->and($product->ingredients)->toBe(['Paracetamol 650 mg'])
        ->and($product->country_of_origin)->toBe('IN')
        ->and($product->hsn_code)->toBe('3004.90');
});

it('finds medicines by molecule, brand and manufacturer', function () {
    Product::factory()->create([
        'name' => 'Crocin Advance', 'generic_name' => 'Paracetamol', 'manufacturer' => 'GSK Pharma',
        'category_id' => $this->category->id, 'vertical_id' => $this->pharma->id,
    ]);

    $this->get('/search?q=paracetamol')->assertSee('Crocin Advance');
    $this->get('/search?q=GSK')->assertSee('Crocin Advance');
});

it('lets AI auto-fill the open form but never invents a price', function () {
    config([
        'services.groq.key' => 'test-key',
        'services.groq.model' => 'openai/gpt-oss-120b',
    ]);

    Http::fake(['api.groq.com/*' => Http::sequence()
        ->push(['choices' => [['message' => ['content' => json_encode([
            'category' => 'Analgesics', 'unit' => 'pack', 'hsn_code' => '3004.90',
            'short_description' => 'Paracetamol tablets.',
            'generic_name' => 'Paracetamol', 'brand_name' => null, 'strength' => '650 mg', 'dosage_form' => 'Tablet',
            'pack_size' => '10 x 10', 'ingredients' => ['Paracetamol 650 mg'], 'manufacturer' => null,
            'country_of_origin' => 'IN', 'cas_number' => null, 'pharmacopoeia_standard' => 'IP',
            'schedule_class' => 'OTC', 'storage_conditions' => 'Store below 30°C.', 'shelf_life_months' => 36,
            'is_cold_chain' => false, 'humidity_sensitive' => true, 'light_sensitive' => false,
            'seo_title' => 'Paracetamol 650 mg', 'seo_description' => 'Wholesale paracetamol.',
            'seo_keywords' => 'paracetamol, tablets', 'focus_keyword' => 'paracetamol 650',
        ])]]]])
        ->push(['choices' => [['message' => ['content' => "```html\n<h2>Overview</h2><p>Tablets.</p>\n```"]]]]),
    ]);

    Livewire::test(CreateProduct::class)
        ->callAction('aiAutofill', ['name' => 'Paracetamol 650 mg'])
        ->assertHasNoActionErrors()
        ->assertSet('data.generic_name', 'Paracetamol')
        ->assertSet('data.category_id', $this->category->id)
        ->assertSet('data.unit', 'pack')
        ->assertSet('data.country_of_origin', 'IN')
        ->assertSet('data.humidity_sensitive', true)
        ->assertSet('data.manufacturer', null);

    expect(Product::count())->toBe(0);
});

it('hides the AI button until a Groq key is configured', function () {
    config(['services.groq.key' => null]);

    Livewire::test(CreateProduct::class)->assertActionHidden('aiAutofill');
});
