<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Vertical;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('lists products for a vertical', function () {
    $vendor = Vendor::factory()->create();
    $category = Category::where('slug', 'solar-panels')->firstOrFail();

    $listed = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'category_id' => $category->id,
        'vertical_id' => $category->vertical_id,
        'name' => 'Bifacial 600W Panel',
    ]);

    $pharma = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'category_id' => Category::where('slug', 'formulations')->value('id'),
        'vertical_id' => Vertical::where('slug', 'pharma')->value('id'),
        'name' => 'Ibuprofen Tablets',
    ]);

    $this->get(route('verticals.show', 'solar'))
        ->assertOk()
        ->assertSee($listed->name)
        ->assertDontSee($pharma->name);
});

it('hides products from pending vendors on listings', function () {
    $pending = Vendor::factory()->pending()->create();
    $hidden = Product::factory()->create(['vendor_id' => $pending->id, 'name' => 'Not Yet Approved Panel']);

    $this->get(route('search', ['q' => 'Panel']))
        ->assertOk()
        ->assertDontSee($hidden->name);
});

it('searches by product name and vendor', function () {
    $vendor = Vendor::factory()->create(['name' => 'Kolkata Chem Works']);
    Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Sodium Benzoate USP']);

    $this->get(route('search', ['q' => 'Benzoate']))->assertOk()->assertSee('Sodium Benzoate USP');
    $this->get(route('search', ['q' => 'Kolkata']))->assertOk()->assertSee('Sodium Benzoate USP');
});

it('filters by certification', function () {
    $vendor = Vendor::factory()->create();

    $certified = Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'FDA Grade API']);
    $certified->certificates()->create(['type' => 'FDA', 'is_primary' => true]);

    Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Uncertified Filler']);

    $this->get(route('search', ['certification' => ['FDA']]))
        ->assertOk()
        ->assertSee('FDA Grade API')
        ->assertDontSee('Uncertified Filler');
});

it('shows a product detail page with tier pricing', function () {
    $product = Product::factory()->create(['name' => 'Mono PERC 550W', 'base_price' => 18500, 'moq' => 30]);
    $product->tierPrices()->create(['min_qty' => 30, 'max_qty' => 149, 'price' => 18500, 'currency' => 'USD']);
    $product->tierPrices()->create(['min_qty' => 150, 'price' => 17000, 'currency' => 'USD']);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Mono PERC 550W')
        ->assertSee('$185')
        ->assertSee('$170')
        ->assertSee('Add to cart');
});

it('hides pricing on licensed pharma products and offers a quote instead', function () {
    $product = Product::factory()->create([
        'name' => 'Controlled Substance API',
        'requires_license' => true,
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Licence required')
        ->assertSee('Request a quote')
        ->assertDontSee('Add to cart');
});

it('404s for a product whose vendor is not approved', function () {
    $product = Product::factory()->create(['vendor_id' => Vendor::factory()->pending()->create()->id]);

    $this->get(route('products.show', $product))->assertNotFound();
});

it('lists vendors and opens a vendor store', function () {
    $vendor = Vendor::factory()->create(['name' => 'Deccan Solar Works']);
    Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Deccan Mounting Rail']);

    $this->get(route('vendors.index'))->assertOk()->assertSee('Deccan Solar Works');

    $this->get(route('vendors.show', $vendor))
        ->assertOk()
        ->assertSee('Deccan Solar Works')
        ->assertSee('Deccan Mounting Rail')
        ->assertSee('Verified vendor');
});

it('returns search suggestions as json', function () {
    $vendor = Vendor::factory()->create();
    Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Amoxicillin Trihydrate']);

    $this->getJson(route('search.suggest', ['q' => 'Amox']))
        ->assertOk()
        ->assertJsonPath('products.0.name', 'Amoxicillin Trihydrate');
});

it('requires sign-in before saving to the wishlist', function () {
    $product = Product::factory()->create();

    $this->postJson(route('wishlist.toggle'), ['product_id' => $product->id])->assertStatus(401);

    $this->actingAs(User::factory()->create())
        ->postJson(route('wishlist.toggle'), ['product_id' => $product->id])
        ->assertOk()
        ->assertJsonPath('saved', true);
});
