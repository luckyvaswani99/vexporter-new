<?php

namespace App\Filament\Vendor\Resources\SubOrders;

use App\Filament\Vendor\Resources\SubOrders\Pages\ListSubOrders;
use App\Filament\Vendor\Resources\SubOrders\Tables\SubOrdersTable;
use App\Models\Order;
use App\Models\SubOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SubOrderResource extends Resource
{
    protected static ?string $model = SubOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'order';

    protected static ?string $pluralModelLabel = 'orders';

    protected static ?string $recordTitleAttribute = 'reference';

    /** Orders arrive from buyers — vendors fulfil them, never create them. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return SubOrdersTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $open = static::getEloquentQuery()
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING])
            ->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubOrders::route('/'),
        ];
    }
}
