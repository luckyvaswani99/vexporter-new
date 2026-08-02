<?php

use App\Models\Product;
use App\Services\JsonLdGenerator;
use App\Services\SeoService;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('generates valid OpenGraph and Twitter card metadata', function () {
    $seoService = app(SeoService::class);
    $seo = $seoService->generate('Test Title', 'Test Description');

    expect($seo['title'])->toContain('Test Title')
        ->and($seo['description'])->toBe('Test Description')
        ->and($seo['og']['site_name'])->toBe(config('app.name'))
        ->and($seo['twitter']['card'])->toBe('summary_large_image');
});

it('generates valid Schema.org JSON-LD organization and product schemas', function () {
    $jsonLd = app(JsonLdGenerator::class);
    $orgSchema = $jsonLd->organization();

    expect($orgSchema['@type'])->toBe('Organization')
        ->and($orgSchema['name'])->toBe(config('app.name'));

    $product = Product::first() ?? Product::factory()->create();
    $productSchema = $jsonLd->product($product);

    expect($productSchema['@type'])->toBe('Product')
        ->and($productSchema['name'])->toBe($product->name)
        ->and($productSchema['offers']['@type'])->toBe('Offer');
});

it('serves dynamic sitemap.xml and robots.txt endpoints', function () {
    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/xml; charset=UTF-8')
        ->assertSee('urlset')
        ->assertSee(route('home'));

    $this->get(route('robots'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('User-agent: *')
        ->assertSee(route('sitemap'));
});
