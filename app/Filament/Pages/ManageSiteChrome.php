<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * The header and footer wrapping every storefront page — contact strip, top-bar
 * links, search placeholder, footer link columns, socials and legal line.
 */
class ManageSiteChrome extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWindow;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Header & Footer';

    protected static ?string $navigationLabel = 'Header & Footer';

    protected function settingGroups(): array
    {
        return ['brand' => 'brand', 'header' => 'header', 'footer' => 'footer'];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->persistTabInQueryString()->tabs([
                    $this->brandingTab(),
                    $this->headerTab(),
                    $this->footerTab(),
                    $this->footerLinksTab(),
                ]),
            ]);
    }

    private function brandingTab(): Tab
    {
        return Tab::make('Branding')
            ->icon(Heroicon::OutlinedPhoto)
            ->schema([
                FileUpload::make('brand.logo_dark')
                    ->label('Logo — for light backgrounds')
                    ->helperText('Used in the header. SVG or PNG, roughly 480×132. Leave empty to keep the built-in mark.')
                    ->image()
                    ->disk('public')
                    ->directory('brand')
                    ->visibility('public')
                    ->maxSize(1024)
                    ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp'])
                    ->imagePreviewHeight('60'),

                FileUpload::make('brand.logo_light')
                    ->label('Logo — for dark backgrounds')
                    ->helperText('Used in the footer. Falls back to the header logo if left empty.')
                    ->image()
                    ->disk('public')
                    ->directory('brand')
                    ->visibility('public')
                    ->maxSize(1024)
                    ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp'])
                    ->imagePreviewHeight('60'),

                FileUpload::make('brand.favicon')
                    ->label('Favicon')
                    ->helperText('Square, 32×32 or larger. ICO, PNG or SVG.')
                    ->disk('public')
                    ->directory('brand')
                    ->visibility('public')
                    ->maxSize(256)
                    ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/svg+xml']),

                Toggle::make('brand.show_tagline')
                    ->label('Show "Where The World Trades" under the wordmark')
                    ->helperText('Only applies to the built-in mark — an uploaded logo is shown as-is.'),
            ]);
    }

    private function headerTab(): Tab
    {
        return Tab::make('Header')
            ->icon(Heroicon::OutlinedBars3)
            ->schema([
                Toggle::make('header.show_topbar')
                    ->label('Show the dark contact strip above the header')
                    ->live(),
                TextInput::make('header.phone')
                    ->label('Phone')
                    ->tel()
                    ->visible(fn ($get): bool => (bool) $get('header.show_topbar'))
                    ->maxLength(40),
                TextInput::make('header.email')
                    ->label('Email')
                    ->email()
                    ->visible(fn ($get): bool => (bool) $get('header.show_topbar'))
                    ->maxLength(120),
                Repeater::make('header.links')
                    ->label('Top-bar links')
                    ->visible(fn ($get): bool => (bool) $get('header.show_topbar'))
                    ->schema($this->linkFields())
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->maxItems(5)
                    ->collapsed(),
                TextInput::make('header.search_placeholder')
                    ->label('Search box placeholder')
                    ->maxLength(80),
                Toggle::make('header.show_wishlist')->label('Show the wishlist shortcut'),
            ]);
    }

    private function footerTab(): Tab
    {
        return Tab::make('Footer')
            ->icon(Heroicon::OutlinedRectangleGroup)
            ->schema([
                Textarea::make('footer.about')
                    ->label('About paragraph')
                    ->rows(3)
                    ->maxLength(400),
                Repeater::make('footer.socials')
                    ->label('Social links')
                    ->schema([
                        TextInput::make('label')
                            ->label('Network')
                            ->helperText('Used as the accessible label.')
                            ->required()
                            ->maxLength(40),
                        TextInput::make('icon')
                            ->label('Icon')
                            ->helperText('Font Awesome brand class, e.g. fa-linkedin-in.')
                            ->required()
                            ->maxLength(40),
                        TextInput::make('url')->label('URL')->required()->maxLength(255),
                    ])
                    ->columns(3)
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->maxItems(8)
                    ->collapsed(),
                TextInput::make('footer.copyright')
                    ->label('Copyright line')
                    ->helperText('Use :year for the current year.')
                    ->maxLength(160),
                Repeater::make('footer.legal_links')
                    ->label('Legal links')
                    ->schema($this->linkFields())
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->maxItems(6)
                    ->collapsed(),
                Repeater::make('footer.payment_icons')
                    ->label('Payment method icons')
                    ->simple(
                        TextInput::make('icon')
                            ->label('Icon')
                            ->helperText('Font Awesome brand class, e.g. fa-cc-visa.')
                            ->maxLength(40),
                    )
                    ->maxItems(8),
            ]);
    }

    private function footerLinksTab(): Tab
    {
        return Tab::make('Footer columns')
            ->icon(Heroicon::OutlinedListBullet)
            ->schema([
                Repeater::make('footer.columns')
                    ->label('Link columns')
                    ->helperText('Drag to reorder. Each column becomes one list in the footer.')
                    ->schema([
                        TextInput::make('heading')->label('Heading')->required()->maxLength(40),
                        Repeater::make('links')
                            ->label('Links')
                            ->schema($this->linkFields())
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->maxItems(10)
                            ->collapsed(),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['heading'] ?? null)
                    ->maxItems(4)
                    ->collapsed(),
            ]);
    }

    /** @return array<int, TextInput> */
    private function linkFields(): array
    {
        return [
            TextInput::make('label')->label('Label')->required()->maxLength(60),
            TextInput::make('url')
                ->label('URL')
                ->helperText('A path such as /help, or a full https:// address.')
                ->required()
                ->maxLength(255),
        ];
    }
}
