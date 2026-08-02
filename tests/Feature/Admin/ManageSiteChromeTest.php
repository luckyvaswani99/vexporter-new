<?php

use App\Filament\Pages\ManageSiteChrome;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\SiteSettings;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('is reachable by an admin', function () {
    $this->actingAs(User::factory()->create(['type' => 'admin'])->syncRoles(RoleSeeder::ROLE_ADMIN))
        ->get(ManageSiteChrome::getUrl())
        ->assertSuccessful();
});

it('is hidden from users without the content permission', function () {
    $this->actingAs(User::factory()->create(['type' => 'buyer']))
        ->get(ManageSiteChrome::getUrl())
        ->assertForbidden();
});

it('persists header edits across every page', function () {
    Livewire::actingAs(User::factory()->create(['type' => 'admin'])->syncRoles(RoleSeeder::ROLE_ADMIN))
        ->test(ManageSiteChrome::class)
        ->fillForm([
            'header.phone' => '+91 11111 22222',
            'header.search_placeholder' => 'Find suppliers…',
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteSetting::where('key', 'header')->value('value'))
        ->toMatchArray(['phone' => '+91 11111 22222']);

    // The header wraps every storefront page, not just the homepage.
    $this->get('/vendors')
        ->assertSee('+91 11111 22222')
        ->assertSee('Find suppliers…', escape: false);
});

it('hides the top bar when switched off', function () {
    // The click-to-call link is unique to the top bar — the contact email also
    // appears in the Organization JSON-LD, which this toggle does not touch.
    $this->get('/')->assertSee('href="tel:', escape: false);

    app(SiteSettings::class)->put(['header' => ['show_topbar' => false]]);

    $this->get('/')->assertDontSee('href="tel:', escape: false);
});

it('hides the wishlist shortcut in both the header and the mobile menu', function () {
    app(SiteSettings::class)->put(['header' => ['show_wishlist' => false]]);

    $this->get('/')->assertDontSee('Wishlist');
});

it('rebuilds the footer columns from settings', function () {
    app(SiteSettings::class)->put(['footer' => [
        'columns' => [
            ['heading' => 'Marketplace', 'links' => [['label' => 'All vendors', 'url' => '/vendors']]],
        ],
    ]]);

    $this->get('/')
        ->assertSee('Marketplace')
        ->assertSee('All vendors')
        ->assertDontSee('Help Center');
});

it('substitutes the year into the copyright line', function () {
    app(SiteSettings::class)->put(['footer' => ['copyright' => 'Copyright :year Acme']]);

    $this->get('/')->assertSee('Copyright '.date('Y').' Acme');
});

it('uses the uploaded logo instead of the built-in mark', function () {
    // The tagline <span> is unique to the fallback mark — an uploaded logo
    // still carries the same words in its alt text.
    $this->get('/')->assertSee('Where The World Trades</span>', escape: false);

    app(SiteSettings::class)->put(['brand' => ['logo_dark' => 'brand/logo.svg']]);

    $this->get('/')
        ->assertSee('/storage/brand/logo.svg', escape: false)
        ->assertDontSee('Where The World Trades</span>', escape: false);
});

it('falls back to the dark logo when no light variant is uploaded', function () {
    app(SiteSettings::class)->put(['brand' => ['logo_dark' => 'brand/logo.svg']]);

    // Header and footer both resolve to the one file.
    expect(substr_count($this->get('/')->getContent(), '/storage/brand/logo.svg'))->toBe(2);
});

it('serves the uploaded favicon', function () {
    $this->get('/')->assertSee('href="'.asset('favicon.ico').'"', escape: false);

    app(SiteSettings::class)->put(['brand' => ['favicon' => 'brand/icon.png']]);

    $this->get('/')->assertSee('/storage/brand/icon.png', escape: false);
});

it('can drop the tagline from the built-in mark', function () {
    app(SiteSettings::class)->put(['brand' => ['show_tagline' => false]]);

    $this->get('/')
        ->assertDontSee('Where The World Trades</span>', escape: false)
        ->assertSee('VEXPORTER');
});

it('keeps shipped values for fields the admin has not touched', function () {
    app(SiteSettings::class)->put(['footer' => ['about' => 'Short blurb.']]);

    $this->get('/')
        ->assertSee('Short blurb.')
        ->assertSee('All rights reserved');
});
