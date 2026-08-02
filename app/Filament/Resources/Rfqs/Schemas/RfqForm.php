<?php

namespace App\Filament\Resources\Rfqs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RfqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required(),
                Select::make('buyer_id')
                    ->relationship('buyer', 'name')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
                TextInput::make('target_type')
                    ->required()
                    ->default('product'),
                Select::make('product_id')
                    ->relationship('product', 'name'),
                TextInput::make('category_id')
                    ->numeric(),
                TextInput::make('vertical_id')
                    ->numeric(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('qty')
                    ->numeric(),
                TextInput::make('unit'),
                TextInput::make('target_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                TextInput::make('destination_country'),
                TextInput::make('incoterm'),
                DatePicker::make('delivery_by'),
                Textarea::make('attachments')
                    ->columnSpanFull(),
                DateTimePicker::make('expires_at'),
            ]);
    }
}
