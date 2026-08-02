<?php

namespace App\Filament\Pages;

use App\Support\Homepage;
use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Single screen for everything the storefront homepage renders: copy, calls to
 * action, the badge/feature lists, how many products each showcase pulls, and
 * the order and visibility of the sections themselves.
 */
class ManageHomepage extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Homepage';

    protected static ?string $navigationLabel = 'Homepage';

    /** Groups are stored as "home.hero"; the form is keyed on "hero". */
    protected function settingGroups(): array
    {
        return collect(Homepage::defaults())
            ->keys()
            ->mapWithKeys(fn (string $key): array => [$key => "home.{$key}"])
            ->all();
    }

    protected function staleCacheKeys(): array
    {
        // Headline counts and the analytics panel are cached separately.
        return ['home:stats', 'home:analytics'];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->persistTabInQueryString()->tabs([
                    $this->layoutTab(),
                    $this->heroTab(),
                    $this->trustTab(),
                    $this->showcaseTab(),
                    $this->vendorsTab(),
                    $this->whyTab(),
                    $this->conversionTab(),
                    $this->seoTab(),
                ]),
            ]);
    }

    private function layoutTab(): Tab
    {
        return Tab::make('Sections')
            ->icon(Heroicon::OutlinedBars3BottomLeft)
            ->schema([
                Repeater::make('sections')
                    ->label('Order & visibility')
                    ->helperText('Drag to reorder. Turn a section off to hide it without losing its content.')
                    ->schema([
                        Hidden::make('key'),
                        Toggle::make('enabled')->label('Visible on the homepage')->inline(false),
                    ])
                    ->itemLabel(fn (array $state): string => Homepage::SECTIONS[$state['key'] ?? ''] ?? 'Unknown section')
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable()
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    private function heroTab(): Tab
    {
        return Tab::make('Hero')
            ->icon(Heroicon::OutlinedSparkles)
            ->schema([
                TextInput::make('hero.badge')->label('Pill badge')->maxLength(120),
                TextInput::make('hero.title_line_1')->label('Headline — first line')->required()->maxLength(60),
                TextInput::make('hero.title_line_2')
                    ->label('Headline — highlighted line')
                    ->helperText('Rendered in the red-to-orange gradient.')
                    ->maxLength(60),
                Textarea::make('hero.subtitle')->label('Supporting paragraph')->rows(3)->maxLength(400),
                TextInput::make('hero.primary_label')->label('Primary button label')->maxLength(40),
                TextInput::make('hero.primary_url')->label('Primary button link')->maxLength(255),
                TextInput::make('hero.secondary_label')->label('Secondary button label')->maxLength(40),
                TextInput::make('hero.secondary_url')->label('Secondary button link')->maxLength(255),
                Toggle::make('hero.show_stats')
                    ->label('Show the live counters')
                    ->helperText('Product, vendor and country totals, calculated hourly from real data.'),
                Toggle::make('hero.show_tiles')->label('Show the floating product tiles')->live(),
                Repeater::make('hero.tiles')
                    ->label('Floating tiles')
                    ->visible(fn ($get): bool => (bool) $get('hero.show_tiles'))
                    ->schema([
                        TextInput::make('title')->label('Title')->required()->maxLength(40),
                        TextInput::make('price')->label('Price line')->maxLength(30),
                        TextInput::make('unit')->label('Unit suffix')->placeholder('/ton')->maxLength(20),
                        $this->iconField(),
                        $this->toneField(Homepage::tileToneOptions()),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->maxItems(4)
                    ->collapsed(),
            ]);
    }

    private function trustTab(): Tab
    {
        return Tab::make('Trust badges')
            ->icon(Heroicon::OutlinedShieldCheck)
            ->schema([
                Repeater::make('trust.items')
                    ->label('Badges')
                    ->helperText('Shown as a four-across strip under the hero.')
                    ->schema([
                        TextInput::make('title')->label('Title')->required()->maxLength(40),
                        TextInput::make('subtitle')->label('Subtitle')->maxLength(40),
                        $this->iconField(),
                        $this->toneField(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->maxItems(4)
                    ->collapsed(),
            ]);
    }

    private function showcaseTab(): Tab
    {
        return Tab::make('Category & verticals')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->schema([
                TextInput::make('categories.eyebrow')->label('Category strip — eyebrow')->maxLength(40),
                TextInput::make('categories.title')->label('Category strip — heading')->required()->maxLength(80),
                Textarea::make('categories.subtitle')->label('Category strip — subheading')->rows(2)->maxLength(300),

                ...$this->verticalFields('pharma', 'Pharma showcase'),
                ...$this->verticalFields('solar', 'Solar showcase'),
            ]);
    }

    /** @return array<int, Component> */
    private function verticalFields(string $key, string $label): array
    {
        return [
            TextInput::make("{$key}.eyebrow")->label("{$label} — eyebrow")->maxLength(40),
            TextInput::make("{$key}.eyebrow_icon")
                ->label("{$label} — eyebrow icon")
                ->helperText('Font Awesome class, e.g. fa-sun.')
                ->maxLength(40),
            TextInput::make("{$key}.title")->label("{$label} — heading")->required()->maxLength(80),
            Textarea::make("{$key}.subtitle")->label("{$label} — subheading")->rows(2)->maxLength(300),
            TextInput::make("{$key}.cta_label")->label("{$label} — link label")->maxLength(60),
            TextInput::make("{$key}.limit")
                ->label("{$label} — products to show")
                ->numeric()
                ->minValue(2)
                ->maxValue(8)
                ->helperText('Pulled automatically from featured, approved products in this vertical.'),
        ];
    }

    private function vendorsTab(): Tab
    {
        return Tab::make('Vendors')
            ->icon(Heroicon::OutlinedBuildingStorefront)
            ->schema([
                TextInput::make('vendors.eyebrow')->label('Eyebrow')->maxLength(40),
                TextInput::make('vendors.title')->label('Heading')->required()->maxLength(80),
                Textarea::make('vendors.subtitle')->label('Subheading')->rows(2)->maxLength(300),
                TextInput::make('vendors.cta_label')
                    ->label('Button label')
                    ->helperText('Use :count where the live vendor total should appear.')
                    ->maxLength(60),
                TextInput::make('vendors.limit')->label('Vendors to show')->numeric()->minValue(2)->maxValue(8),
            ]);
    }

    private function whyTab(): Tab
    {
        return Tab::make('Why us')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->schema([
                TextInput::make('why.eyebrow')->label('Eyebrow')->maxLength(40),
                TextInput::make('why.title')->label('Heading')->required()->maxLength(80),
                Textarea::make('why.body')->label('Intro paragraph')->rows(3)->maxLength(400),
                Toggle::make('why.show_panel')->label('Show the live analytics panel')->live(),
                TextInput::make('why.panel_title')
                    ->label('Analytics panel heading')
                    ->visible(fn ($get): bool => (bool) $get('why.show_panel'))
                    ->maxLength(40),
                Repeater::make('why.reasons')
                    ->label('Selling points')
                    ->schema([
                        TextInput::make('title')->label('Title')->required()->maxLength(60),
                        Textarea::make('body')->label('Description')->rows(2)->maxLength(300),
                        $this->iconField(),
                        $this->toneField(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->maxItems(6)
                    ->collapsed(),
            ]);
    }

    private function conversionTab(): Tab
    {
        return Tab::make('Testimonials & CTA')
            ->icon(Heroicon::OutlinedMegaphone)
            ->schema([
                TextInput::make('testimonials.eyebrow')->label('Testimonials — eyebrow')->maxLength(40),
                TextInput::make('testimonials.title')->label('Testimonials — heading')->required()->maxLength(80),
                TextInput::make('testimonials.limit')
                    ->label('Testimonials to show')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(6)
                    ->helperText('Edit the quotes themselves under Content → Testimonials.'),

                TextInput::make('vendor_cta.title')->label('Vendor CTA — heading')->required()->maxLength(90),
                Textarea::make('vendor_cta.subtitle')->label('Vendor CTA — paragraph')->rows(3)->maxLength(400),
                TextInput::make('vendor_cta.primary_label')->label('Vendor CTA — primary button')->maxLength(40),
                TextInput::make('vendor_cta.primary_url')->label('Vendor CTA — primary link')->maxLength(255),
                TextInput::make('vendor_cta.secondary_label')->label('Vendor CTA — secondary button')->maxLength(40),
                TextInput::make('vendor_cta.secondary_url')->label('Vendor CTA — secondary link')->maxLength(255),
                Repeater::make('vendor_cta.bullets')
                    ->label('Reassurance bullets')
                    ->simple(
                        TextInput::make('text')
                            ->label('Bullet')
                            ->helperText('Use :commission for the platform commission percentage.')
                            ->maxLength(60),
                    )
                    ->maxItems(4),

                TextInput::make('newsletter.title')->label('Newsletter — heading')->required()->maxLength(80),
                Textarea::make('newsletter.subtitle')->label('Newsletter — paragraph')->rows(2)->maxLength(300),
                TextInput::make('newsletter.placeholder')->label('Newsletter — input placeholder')->maxLength(60),
                TextInput::make('newsletter.button_label')->label('Newsletter — button label')->maxLength(30),
            ]);
    }

    private function seoTab(): Tab
    {
        return Tab::make('SEO')
            ->icon(Heroicon::OutlinedMagnifyingGlass)
            ->schema([
                TextInput::make('seo.meta_title')
                    ->label('Meta title')
                    ->helperText('Around 60 characters shows in full on Google.')
                    ->maxLength(120),
                Textarea::make('seo.meta_description')
                    ->label('Meta description')
                    ->rows(3)
                    ->helperText('Around 155 characters.')
                    ->maxLength(300),
            ]);
    }

    private function iconField(): TextInput
    {
        return TextInput::make('icon')
            ->label('Icon')
            ->helperText('Font Awesome class, e.g. fa-shield-halved.')
            ->required()
            ->maxLength(40);
    }

    /** @param  array<string, string>|null  $options */
    private function toneField(?array $options = null): Select
    {
        return Select::make('tone')
            ->label('Colour')
            ->options($options ?? Homepage::toneOptions())
            ->default('red')
            ->required()
            ->native(false);
    }
}
