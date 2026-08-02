<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('clears product cache on product saved and deleted events', function () {
    Cache::put('homepage_featured_products', ['mock_data'], 3600);
    expect(Cache::has('homepage_featured_products'))->toBeTrue();

    $product = Product::first() ?? Product::factory()->create();
    $product->update(['name' => 'Updated Product Name']);

    expect(Cache::has('homepage_featured_products'))->toBeFalse();
});

it('clears vendor cache on vendor saved and deleted events', function () {
    Cache::put('homepage_top_vendors', ['mock_vendors'], 3600);
    expect(Cache::has('homepage_top_vendors'))->toBeTrue();

    $vendor = Vendor::first() ?? Vendor::factory()->create();
    $vendor->update(['name' => 'Updated Vendor Name']);

    expect(Cache::has('homepage_top_vendors'))->toBeFalse();
});

it('clears category cache on category saved and deleted events', function () {
    Cache::put('navigation_categories_tree', ['mock_tree'], 3600);
    expect(Cache::has('navigation_categories_tree'))->toBeTrue();

    $category = Category::first() ?? Category::factory()->create();
    $category->update(['name' => 'Updated Category Name']);

    expect(Cache::has('navigation_categories_tree'))->toBeFalse();
});
