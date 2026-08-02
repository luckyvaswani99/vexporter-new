<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vertical_id')
                    ->relationship('vertical', 'name')
                    ->required(),
                Select::make('parent_id')
                    ->relationship('parent', 'name'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('icon'),
                TextInput::make('icon_color'),
                FileUpload::make('image_gradient')
                    ->image(),
                RichEditor::make('description')
                    ->helperText('Shown above the product grid on the category page.')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'h2', 'h3',
                        'bulletList', 'orderedList', 'link', 'undo', 'redo',
                    ])
                    ->columnSpanFull(),
                TextInput::make('seo_title'),
                TextInput::make('seo_description'),
                Toggle::make('is_featured')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('products_count_cache')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
