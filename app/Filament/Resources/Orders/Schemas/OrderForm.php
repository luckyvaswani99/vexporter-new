<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
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
                TextInput::make('source')
                    ->required()
                    ->default('cart'),
                Select::make('quote_id')
                    ->relationship('quote', 'id'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_status')
                    ->required()
                    ->default('unpaid'),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                TextInput::make('fx_rate')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('shipping_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('grand_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('commission_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('billing_address')
                    ->columnSpanFull(),
                Textarea::make('shipping_address')
                    ->columnSpanFull(),
                TextInput::make('incoterm'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DateTimePicker::make('placed_at'),
            ]);
    }
}
