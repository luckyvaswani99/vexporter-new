<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Listing')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->relationship('vendor', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Radio::make('type')
                            ->label('Product type')
                            ->options(Product::TYPE_LABELS)
                            ->descriptions(Product::TYPE_DESCRIPTIONS)
                            ->default(Product::TYPE_SIMPLE)
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        Select::make('vertical_id')
                            ->label('Vertical')
                            ->relationship('vertical', 'name')
                            ->required(),

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        RichEditor::make('short_description')
                            ->label('Short description')
                            ->helperText('One or two lines, shown under the product title. Also used for the meta description, where the formatting is stripped.')
                            ->toolbarButtons(['bold', 'italic', 'link', 'bulletList'])
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('Full description')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'h2', 'h3', 'bulletList', 'orderedList',
                                'blockquote', 'link', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Options')
                    ->description('Each option can carry its own SKU, price and stock. Leave a price blank to use the base price.')
                    ->visible(fn (Get $get): bool => $get('type') === Product::TYPE_VARIABLE)
                    ->schema([
                        Repeater::make('variants')
                            ->relationship()
                            ->label('Options')
                            ->columns(4)
                            ->schema([
                                TextInput::make('name')->label('Option')->required()->placeholder('540W'),
                                TextInput::make('sku')->label('SKU'),
                                TextInput::make('price')->label('Price (cents)')->numeric()->placeholder('Base price'),
                                TextInput::make('stock_qty')->label('Stock')->numeric()->default(0),
                                Toggle::make('is_default')->label('Pre-selected on the product page'),
                            ])
                            ->defaultItems(1)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->reorderable(),
                    ]),

                Section::make('Pricing & MOQ')
                    ->columns(3)
                    ->schema([
                        TextInput::make('base_price')
                            ->label('Base price')
                            ->numeric()
                            ->required()
                            ->helperText(fn (Get $get): string => $get('type') === Product::TYPE_VARIABLE
                                ? 'Cents. Used for any option without its own price.'
                                : 'Stored in cents — 850 = $8.50.'),

                        TextInput::make('compare_at_price')
                            ->label('Compare at')
                            ->numeric(),

                        Select::make('currency')
                            ->options(['USD' => 'USD', 'INR' => 'INR', 'EUR' => 'EUR', 'GBP' => 'GBP', 'AED' => 'AED'])
                            ->default('USD')
                            ->required(),

                        Select::make('unit')
                            ->options(collect(array_keys(Product::UNIT_LABELS))
                                ->mapWithKeys(fn (string $unit) => [$unit => $unit])
                                ->all())
                            ->default('unit')
                            ->required(),

                        TextInput::make('moq')
                            ->label('Minimum order qty')
                            ->numeric()
                            ->default(1),

                        TextInput::make('order_increment')
                            ->numeric()
                            ->default(1),

                        Repeater::make('tierPrices')
                            ->relationship()
                            ->label('Volume slabs')
                            ->columns(3)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('min_qty')->numeric()->required(),
                                TextInput::make('max_qty')->numeric()->placeholder('No limit'),
                                TextInput::make('price')->numeric()->required()->helperText('In cents'),
                            ])
                            ->defaultItems(0)
                            ->collapsed(),
                    ]),

                Section::make('Inventory & logistics')
                    ->columns(4)
                    ->schema([
                        TextInput::make('sku'),
                        TextInput::make('hsn_code')->label('HSN'),
                        TextInput::make('stock_qty')->numeric()->default(0),
                        TextInput::make('lead_time_days')->label('Lead time (days)')->numeric(),
                        TextInput::make('weight_kg')->numeric(),
                    ]),

                Section::make('Compliance & visibility')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')->label('Active')->default(true),
                        Toggle::make('is_featured')->label('Featured'),
                        Toggle::make('is_bestseller')->label('Bestseller'),

                        Toggle::make('requires_license')
                            ->label('Requires buyer licence')
                            ->helperText('Prescription / controlled items — price is hidden and buyers must request a quote.'),

                        Select::make('approval_status')
                            ->options([
                                Product::APPROVAL_PENDING => 'Pending',
                                Product::APPROVAL_APPROVED => 'Approved',
                                Product::APPROVAL_REJECTED => 'Rejected',
                            ])
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Use the Approve / Reject actions.'),

                        Repeater::make('certificates')
                            ->relationship()
                            ->columns(3)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('type')->label('Certificate')->required(),
                                TextInput::make('number'),
                                Toggle::make('is_primary')->label('Show on card'),
                            ])
                            ->defaultItems(0)
                            ->collapsed(),
                    ]),
            ]);
    }
}
