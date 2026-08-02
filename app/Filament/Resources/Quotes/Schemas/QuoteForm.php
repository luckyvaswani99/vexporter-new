<?php

namespace App\Filament\Resources\Quotes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required(),
                Select::make('rfq_id')
                    ->relationship('rfq', 'title')
                    ->required(),
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('sent'),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('shipping')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('incoterm'),
                TextInput::make('lead_time_days')
                    ->numeric(),
                DatePicker::make('validity_until'),
                TextInput::make('payment_terms'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
