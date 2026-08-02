<?php

namespace App\Filament\Resources\Vendors\Schemas;

use App\Models\Product;
use App\Models\Vendor;
use App\Support\Countries;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Store')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Store name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('legal_name')
                            ->label('Registered legal name')
                            ->maxLength(255),

                        Select::make('user_id')
                            ->label('Owner')
                            ->relationship('owner', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('about')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Location')
                    ->columns(3)
                    ->schema([
                        TextInput::make('city'),
                        TextInput::make('state'),
                        Select::make('country_code')
                            ->label('Country')
                            ->options(Countries::NAMES)
                            ->searchable()
                            ->default('IN'),
                    ]),

                Section::make('Statutory registration')
                    ->columns(4)
                    ->schema([
                        TextInput::make('gst_number')->label('GST'),
                        TextInput::make('pan')->label('PAN'),
                        TextInput::make('iec_code')->label('IEC'),
                        TextInput::make('cin')->label('CIN'),
                    ]),

                Section::make('Commercial terms')
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->options([
                                Vendor::STATUS_PENDING => 'Pending review',
                                Vendor::STATUS_APPROVED => 'Approved',
                                Vendor::STATUS_SUSPENDED => 'Suspended',
                                Vendor::STATUS_REJECTED => 'Rejected',
                            ])
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Use the Approve / Reject actions to change this.'),

                        TextInput::make('commission_percent')
                            ->label('Commission override (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->placeholder((string) config('vexporter.commission_percent'))
                            ->helperText('Leave empty to use the platform default.'),

                        TextInput::make('response_time_hours')
                            ->label('Avg. response (hours)')
                            ->numeric(),

                        Textarea::make('rejection_reason')
                            ->rows(2)
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?Vendor $record) => filled($record?->rejection_reason))
                            ->columnSpanFull(),
                    ]),

                Section::make('Storefront presentation')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('avatar_gradient')
                            ->helperText('Tailwind gradient classes used on the storefront card.'),

                        Select::make('tag_tone')
                            ->options(collect(array_keys(Product::TONE_CLASSES))
                                ->mapWithKeys(fn (string $tone) => [$tone => ucfirst($tone)])
                                ->all()),
                    ]),
            ]);
    }
}
