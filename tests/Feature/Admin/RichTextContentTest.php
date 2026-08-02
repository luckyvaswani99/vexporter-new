<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('sanitises a product description on the way into the database', function () {
    $product = Product::factory()->create([
        'description' => '<p>Grade A</p><script>alert(1)</script><p onclick="steal()">Bulk</p>',
    ]);

    expect($product->fresh()->description)
        ->toBe('<p>Grade A</p><p>Bulk</p>');
});

it('sanitises the short description too', function () {
    $product = Product::factory()->create([
        'short_description' => '<em>WHO-GMP</em><img src=x onerror=alert(1)>',
    ]);

    expect($product->fresh()->short_description)->toBe('<em>WHO-GMP</em>');
});

it('sanitises a category description', function () {
    $category = Category::first();
    $category->update(['description' => '<p>Bulk APIs</p><iframe src="//evil"></iframe>']);

    expect($category->fresh()->description)->toBe('<p>Bulk APIs</p>');
});

it('renders product rich text as markup, not escaped tags', function () {
    $vendor = Vendor::factory()->create(['status' => Vendor::STATUS_APPROVED]);

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'is_active' => true,
        'approval_status' => Product::APPROVAL_APPROVED,
        'short_description' => '<strong>WHO-GMP</strong> certified',
        'description' => '<h2>Specification</h2><ul><li>Purity 99.8%</li></ul>',
    ]);

    $this->get(route('products.show', $product))
        ->assertSuccessful()
        ->assertSee('<strong>WHO-GMP</strong>', escape: false)
        ->assertSee('<li>Purity 99.8%</li>', escape: false);
});

it('strips the markup out of the product meta description', function () {
    $vendor = Vendor::factory()->create(['status' => Vendor::STATUS_APPROVED]);

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'is_active' => true,
        'approval_status' => Product::APPROVAL_APPROVED,
        'short_description' => '<strong>WHO-GMP</strong> certified',
    ]);

    $this->get(route('products.show', $product))
        ->assertSee('<meta name="description" content="WHO-GMP certified">', escape: false);
});

it('renders a category description above the product grid', function () {
    $category = Category::first();
    $category->update(['description' => '<p>Sourced from <strong>audited</strong> plants.</p>']);

    $this->get(route('categories.show', $category))
        ->assertSuccessful()
        ->assertSee('<strong>audited</strong>', escape: false)
        // …but the meta tag gets the flattened text.
        ->assertSee('content="Sourced from audited plants."', escape: false);
});
