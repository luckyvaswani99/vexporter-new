<?php

use Database\Seeders\CatalogSeeder;
use Database\Seeders\ContentSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\VendorSeeder;

it('renders the homepage with every design section', function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class, VendorSeeder::class, ProductSeeder::class, ContentSeeder::class]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Global Trade', escape: false)
        ->assertSee('Browse by Category')
        ->assertSee('Pharmaceutical Products')
        ->assertSee('Solar &amp; Renewable Energy', escape: false)
        ->assertSee('Top Verified Vendors')
        ->assertSee('What Our Clients Say')
        ->assertSee('Stay Updated with Export Trends')
        ->assertSee('Where The World Trades');
});

it('shows seeded products, vendors and the flash deal', function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class, VendorSeeder::class, ProductSeeder::class, ContentSeeder::class]);

    $this->get('/')
        ->assertSee('Paracetamol API BP/USP Grade')
        ->assertSee('Mono PERC 540W Solar Panel')
        ->assertSee('MediChem Labs')
        ->assertSee('SunPower India')
        ->assertSee('Mega Export Deal: Up to 40% Off')
        ->assertSee('$8.50')
        ->assertSee('$185');
});

it('renders the homepage even with an empty catalogue', function () {
    $this->get('/')->assertOk();
});

it('renders a placeholder for routes that are not built yet', function () {
    $this->get('/help')
        ->assertOk()
        ->assertSee('Help center');
});

it('accepts newsletter subscriptions', function () {
    $this->postJson(route('newsletter.store'), ['email' => 'buyer@example.com'])
        ->assertOk()
        ->assertJson(['message' => 'Subscribed successfully.']);

    $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'buyer@example.com']);
});

it('rejects an invalid newsletter email', function () {
    $this->postJson(route('newsletter.store'), ['email' => 'not-an-email'])
        ->assertStatus(422);
});
