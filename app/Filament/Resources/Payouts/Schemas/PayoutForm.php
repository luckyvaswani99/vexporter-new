<?php

namespace App\Filament\Resources\Payouts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->required(),
                DatePicker::make('period_start'),
                DatePicker::make('period_end'),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('INR'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('gateway'),
                TextInput::make('gateway_transfer_id'),
                Textarea::make('sub_order_ids')
                    ->columnSpanFull(),
                DateTimePicker::make('processed_at'),
                Textarea::make('failure_reason')
                    ->columnSpanFull(),
            ]);
    }
}
