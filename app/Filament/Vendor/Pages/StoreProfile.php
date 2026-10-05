<?php

namespace App\Filament\Vendor\Pages;

use App\Filament\Support\MoneyInput;
use App\Support\Countries;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Store profile';
    }

    /**
     * Vendors control how their storefront reads. Statutory fields and status
     * stay with the compliance team in the admin panel.
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Storefront')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Store name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('legal_name')
                        ->label('Registered legal name')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Contact support to change your legal entity.'),

                    FileUpload::make('logo')
                        ->label('Store logo')
                        ->helperText('Square image works best. Shown on your store page, product listings and cart.')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('vendors/logos')
                        ->visibility('public')
                        ->maxSize(2048),

                    FileUpload::make('banner')
                        ->label('Store banner')
                        ->helperText('Wide image shown behind your store name.')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('vendors/banners')
                        ->visibility('public')
                        ->maxSize(4096),

                    Textarea::make('about')
                        ->label('About your company')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Section::make('Contact & location')
                ->columns(3)
                ->schema([
                    TextInput::make('city'),
                    TextInput::make('state'),
                    Select::make('country_code')
                        ->label('Country')
                        ->options(Countries::NAMES)
                        ->searchable(),

                    TextInput::make('response_time_hours')
                        ->label('Typical response time (hours)')
                        ->numeric()
                        ->helperText('Shown to buyers on your store page.'),

                    MoneyInput::make('min_order_value')
                        ->label('Minimum order value (USD)'),
                ]),
        ]);
    }
}
