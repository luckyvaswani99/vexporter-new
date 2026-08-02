<?php

use App\Filament\Pages\ManageHomepage;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Homepage;
use App\Support\SiteSettings;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function admin(): User
{
    return User::factory()->create(['type' => 'admin'])->syncRoles(RoleSeeder::ROLE_ADMIN);
}

it('is reachable by an admin', function () {
    $this->actingAs(admin())
        ->get(ManageHomepage::getUrl())
        ->assertSuccessful();
});

it('is hidden from users without the content permission', function () {
    $this->actingAs(User::factory()->create(['type' => 'buyer']))
        ->get(ManageHomepage::getUrl())
        ->assertForbidden();
});

it('opens pre-filled with the shipped defaults', function () {
    Livewire::actingAs(admin())
        ->test(ManageHomepage::class)
        ->assertSchemaStateSet([
            'hero.title_line_1' => 'Global Trade',
            'newsletter.button_label' => 'Subscribe',
        ]);
});

it('persists edits and shows them on the storefront', function () {
    Livewire::actingAs(admin())
        ->test(ManageHomepage::class)
        ->fillForm([
            'hero.title_line_1' => 'Trade Without Borders',
            'hero.badge' => 'Now in 42 markets',
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteSetting::where('key', 'home.hero')->value('value'))
        ->toMatchArray(['title_line_1' => 'Trade Without Borders']);

    $this->get('/')
        ->assertSee('Trade Without Borders')
        ->assertSee('Now in 42 markets')
        ->assertDontSee('Global Trade');
});

it('keeps the section keys when the form round-trips', function () {
    Livewire::actingAs(admin())
        ->test(ManageHomepage::class)
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteSetting::where('key', 'home.sections')->value('value'))
        ->toHaveCount(count(Homepage::SECTIONS))
        ->and(array_column(SiteSetting::where('key', 'home.sections')->value('value'), 'key'))
        ->toBe(array_keys(Homepage::SECTIONS));
});

it('hides a section that has been switched off', function () {
    app(SiteSettings::class)->put([
        'home.sections' => collect(Homepage::SECTIONS)
            ->map(fn (string $label, string $key): array => [
                'key' => $key,
                'enabled' => $key !== 'newsletter',
            ])
            ->values()
            ->all(),
    ]);

    $this->get('/')->assertDontSee('Stay Updated with Export Trends');
});

it('renders sections in the configured order', function () {
    app(SiteSettings::class)->put([
        'home.sections' => [
            ['key' => 'newsletter', 'enabled' => true],
            ['key' => 'hero', 'enabled' => true],
        ],
    ]);

    $body = $this->get('/')->getContent();

    // Sections the admin never ordered still render, after the ones they did.
    expect(strpos($body, 'Stay Updated with Export Trends'))
        ->toBeLessThan(strpos($body, 'Global Trade'))
        ->and($body)->toContain('What Our Clients Say');
});

it('falls back to shipped copy for fields the admin has not touched', function () {
    app(SiteSettings::class)->put(['home.hero' => ['badge' => 'Custom badge']]);

    $this->get('/')
        ->assertSee('Custom badge')
        ->assertSee('Global Trade');
});

it('clamps an out-of-range product limit', function () {
    app(SiteSettings::class)->put(['home.pharma' => ['limit' => 999]]);

    $this->get('/')->assertSuccessful();
});
