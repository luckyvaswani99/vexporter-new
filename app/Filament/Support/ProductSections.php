<?php

namespace App\Filament\Support;

use App\Models\Category;
use App\Support\Countries;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Form sections shared by the admin and vendor product forms.
 */
class ProductSections
{
    /** Common values offered as suggestions; sellers can still type their own. */
    private const DOSAGE_FORMS = ['Tablet', 'Capsule', 'Syrup', 'Suspension', 'Injection', 'Infusion', 'Ointment', 'Cream', 'Gel', 'Drops', 'Inhaler', 'Powder', 'Sachet', 'API (bulk powder)', 'Softgel', 'Suppository'];

    private const SCHEDULES = ['OTC', 'Schedule G', 'Schedule H', 'Schedule H1', 'Schedule X'];

    private const PHARMACOPOEIAS = ['IP', 'BP', 'USP', 'Ph. Eur.', 'JP'];

    /** True while the chosen category belongs to the pharma vertical. */
    public static function isPharma(Get $get): bool
    {
        $categoryId = $get('category_id');

        return $categoryId && Category::find($categoryId)?->vertical?->slug === 'pharma';
    }

    public static function pharma(): Section
    {
        return Section::make('Pharma details')
            ->description('Buyers search medicines by molecule, strength and form — the more you fill in, the easier you are to find.')
            ->columns(3)
            ->visible(fn (Get $get): bool => self::isPharma($get) || filled($get('generic_name')))
            ->schema([
                TextInput::make('generic_name')
                    ->label('Generic name (molecule)')
                    ->placeholder('Paracetamol')
                    ->helperText('Molecule only — no strength.'),

                TextInput::make('strength')
                    ->placeholder('650 mg'),

                TextInput::make('dosage_form')
                    ->label('Dosage form')
                    ->datalist(self::DOSAGE_FORMS),

                TextInput::make('brand_name')
                    ->label('Brand name'),

                TextInput::make('pack_size')
                    ->label('Pack size')
                    ->placeholder('10 x 10 blister'),

                TextInput::make('manufacturer'),

                Select::make('country_of_origin')
                    ->label('Country of origin')
                    ->options(Countries::NAMES)
                    ->searchable(),

                TextInput::make('schedule_class')
                    ->label('Drug schedule')
                    ->datalist(self::SCHEDULES),

                TextInput::make('pharmacopoeia_standard')
                    ->label('Pharmacopoeia')
                    ->datalist(self::PHARMACOPOEIAS),

                TagsInput::make('ingredients')
                    ->label('Active ingredients')
                    ->placeholder('Add ingredient and press Enter')
                    ->columnSpan(2),

                TextInput::make('cas_number')
                    ->label('CAS number')
                    ->helperText('For APIs / bulk chemicals.'),

                TextInput::make('storage_conditions')
                    ->label('Storage')
                    ->placeholder('Store below 30°C. Protect from light and moisture.')
                    ->columnSpan(2),

                TextInput::make('shelf_life_months')
                    ->label('Shelf life (months)')
                    ->numeric()
                    ->minValue(1),

                Toggle::make('is_cold_chain')->label('Needs cold chain (2–8°C)'),
                Toggle::make('humidity_sensitive')->label('Humidity sensitive'),
                Toggle::make('light_sensitive')->label('Light sensitive'),
            ]);
    }

    public static function seo(): Section
    {
        return Section::make('Search engine listing (optional)')
            ->description('Filled in automatically from the name and description — only change it if you want to.')
            ->collapsed()
            ->columns(2)
            ->schema([
                TextInput::make('seo_title')
                    ->label('Page title')
                    ->maxLength(70)
                    ->helperText('Up to 60 characters shows fully on Google.')
                    ->columnSpanFull(),

                Textarea::make('seo_description')
                    ->label('Search description')
                    ->rows(2)
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('focus_keyword')->label('Main keyword'),
                TextInput::make('seo_keywords')->label('Other keywords')->helperText('Comma separated.'),
            ]);
    }
}
