<?php

namespace App\Filament\Vendor\Resources\Products\Schemas;

use App\Filament\Support\MoneyInput;
use App\Filament\Support\ProductImages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    /**
     * Vendor-facing form, ordered the way a seller thinks: photos, what it is,
     * what it costs — then optional detail. The slug and vertical are derived
     * on save, and approval status is admin-only.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('1. Photos & basics')
                    ->description('Start with clear photos — listings with pictures get far more enquiries.')
                    ->columns(2)
                    ->schema([
                        ProductImages::field(),

                        TextInput::make('name')
                            ->label('Product name')
                            ->placeholder('e.g. Monocrystalline Solar Panel 540W')
                            ->required()
                            ->maxLength(255),

                        Select::make('category_id')
                            ->label('Category')
                            ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Textarea::make('short_description')
                            ->label('Short description')
                            ->helperText('One or two lines shown under the product name.')
                            ->rows(2)
                            ->maxLength(300)
                            ->columnSpanFull(),
                    ]),

                Section::make('2. Price & stock')
                    ->columns(3)
                    ->schema([
                        MoneyInput::make('base_price')
                            ->label('Price per unit')
                            ->required()
                            ->helperText('Type it normally — 8.50 means 8.50 in the currency chosen.'),

                        Select::make('currency')
                            ->options(['USD' => 'USD', 'INR' => 'INR', 'EUR' => 'EUR'])
                            ->default('USD')
                            ->required(),

                        Select::make('unit')
                            ->label('Sold by')
                            ->options(collect(array_keys(Product::UNIT_LABELS))
                                ->mapWithKeys(fn (string $unit) => [$unit => $unit])
                                ->all())
                            ->default('unit')
                            ->required(),

                        TextInput::make('moq')
                            ->label('Minimum order quantity')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        TextInput::make('stock_qty')
                            ->label('Stock available')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Available for sale')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('More details (optional)')
                    ->description('Fill these in when you are ready — you can always come back.')
                    ->collapsed()
                    ->schema([
                        RichEditor::make('description')
                            ->label('Full description')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline',
                                'h2', 'h3', 'bulletList', 'orderedList',
                                'link', 'undo', 'redo',
                            ]),

                        Select::make('type')
                            ->label('Product type')
                            ->helperText('Choose "Variable" if it comes in different sizes, strengths or models.')
                            ->options(Product::TYPE_LABELS)
                            ->default(Product::TYPE_SIMPLE)
                            ->live()
                            ->required(),

                        Repeater::make('variants')
                            ->relationship()
                            ->label('Options')
                            ->visible(fn (Get $get): bool => $get('type') === Product::TYPE_VARIABLE)
                            ->helperText('Leave an option price blank to use the product price.')
                            ->columns(4)
                            ->schema([
                                TextInput::make('name')->label('Option')->required()->placeholder('540W'),
                                TextInput::make('sku')->label('SKU'),
                                MoneyInput::make('price')->label('Price')->placeholder('Same as product'),
                                TextInput::make('stock_qty')->label('Stock')->numeric()->default(0),
                                Toggle::make('is_default')->label('Pre-selected'),
                            ])
                            ->defaultItems(1)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->reorderable(),

                        Repeater::make('tierPrices')
                            ->relationship()
                            ->label('Cheaper for bigger orders')
                            ->helperText('Add a row per quantity band, e.g. 100–499 units at 7.90.')
                            ->addActionLabel('Add a quantity band')
                            ->columns(3)
                            ->schema([
                                TextInput::make('min_qty')->label('From qty')->numeric()->required(),
                                TextInput::make('max_qty')->label('Up to qty')->numeric()->placeholder('No limit'),
                                MoneyInput::make('price')->label('Price per unit')->required(),
                            ])
                            ->defaultItems(0),

                        Section::make('Shipping & codes')
                            ->columns(4)
                            ->schema([
                                TextInput::make('sku')->label('Your SKU'),
                                TextInput::make('lead_time_days')->label('Lead time (days)')->numeric(),
                                TextInput::make('weight_kg')->label('Weight (kg)')->numeric(),
                                TextInput::make('hsn_code')->label('HSN code'),
                                TextInput::make('order_increment')->label('Order in multiples of')->numeric()->default(1),
                                MoneyInput::make('compare_at_price')->label('Old price (struck through)'),
                            ]),

                        Section::make('Licences & certificates')
                            ->schema([
                                Toggle::make('requires_license')
                                    ->label('Buyer needs a licence to purchase')
                                    ->helperText('Prescription / controlled goods — buyers must request a quote.'),

                                Repeater::make('certificates')
                                    ->relationship()
                                    ->columns(3)
                                    ->addActionLabel('Add a certificate')
                                    ->schema([
                                        TextInput::make('type')->label('Certificate')->required()->placeholder('WHO-GMP'),
                                        TextInput::make('number'),
                                        Toggle::make('is_primary')->label('Show on product card'),
                                    ])
                                    ->defaultItems(0),
                            ]),
                    ]),
            ]);
    }
}
