<?php

namespace App\Filament\Vendor\Resources\Rfqs;

use App\Filament\Vendor\Resources\Rfqs\Pages\ListRfqs;
use App\Filament\Vendor\Resources\Rfqs\Tables\RfqsTable;
use App\Models\Rfq;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RfqResource extends Resource
{
    protected static ?string $model = Rfq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'quote request';

    protected static ?string $pluralModelLabel = 'quote requests';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * RFQs belong to buyers, not vendors, so Filament's tenant column scoping
     * does not apply — we scope through the invitation pivot instead.
     */
    protected static bool $isScopedToTenant = false;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('vendors', fn (Builder $query) => $query->whereKey(Filament::getTenant()?->getKey()));
    }

    public static function table(Table $table): Table
    {
        return RfqsTable::configure($table);
    }

    /** Invitations still waiting on a quote from this store. */
    public static function getNavigationBadge(): ?string
    {
        $open = static::getEloquentQuery()
            ->where('status', Rfq::STATUS_OPEN)
            ->whereDoesntHave('quotes', fn (Builder $query) => $query->where('vendor_id', Filament::getTenant()?->getKey()))
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
            'index' => ListRfqs::route('/'),
        ];
    }
}
