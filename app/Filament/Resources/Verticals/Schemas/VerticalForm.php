<?php

namespace App\Filament\Resources\Verticals\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VerticalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('icon'),
                TextInput::make('watermark_icon'),
                TextInput::make('gradient_class'),
                TextInput::make('chip_class'),
                TextInput::make('accent')
                    ->required()
                    ->default('gray'),
                Textarea::make('tagline')
                    ->columnSpanFull(),
                TextInput::make('products_count_cache')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
