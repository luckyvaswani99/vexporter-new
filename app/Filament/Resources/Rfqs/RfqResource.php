<?php

namespace App\Filament\Resources\Rfqs;

use App\Filament\Resources\Rfqs\Pages\CreateRfq;
use App\Filament\Resources\Rfqs\Pages\EditRfq;
use App\Filament\Resources\Rfqs\Pages\ListRfqs;
use App\Filament\Resources\Rfqs\Schemas\RfqForm;
use App\Filament\Resources\Rfqs\Tables\RfqsTable;
use App\Models\Rfq;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RfqResource extends Resource
{
    protected static ?string $model = Rfq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RfqForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RfqsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRfqs::route('/'),
            'create' => CreateRfq::route('/create'),
            'edit' => EditRfq::route('/{record}/edit'),
        ];
    }
}
