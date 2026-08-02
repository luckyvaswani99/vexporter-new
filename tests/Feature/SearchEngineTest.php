<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Vertical;
use App\Services\SearchService;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('expands search terms using industry synonyms', function () {
    $searchService = app(SearchService::class);

    $expandedApi = $searchService->expandQuery('api');
    expect($expandedApi)->toContain('api')->toContain('active pharmaceutical ingredient');

    $expandedPv = $searchService->expandQuery('pv');
    expect($expandedPv)->toContain('pv')->toContain('solar panel');
});

it('provides autocomplete search suggestions endpoint', function () {
    $vendor = Vendor::factory()->create(['name' => 'SunTech Solar Global']);
    $solarVertical = Vertical::where('slug', 'solar')->firstOrFail();
    $category = Category::where('vertical_id', $solarVertical->id)->firstOrFail();

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'vertical_id' => $solarVertical->id,
        'category_id' => $category->id,
        'name' => 'PV Solar Panel Mono PERC 550W',
    ]);

    // Search with synonym term 'PV'
    $this->getJson(route('search.suggest', ['q' => 'PV']))
        ->assertOk()
        ->assertJsonPath('products.0.id', $product->id);
});
