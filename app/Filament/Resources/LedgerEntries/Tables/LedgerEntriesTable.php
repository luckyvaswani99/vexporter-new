<?php

namespace App\Filament\Resources\LedgerEntries\Tables;

use App\Models\LedgerEntry;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LedgerEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        LedgerEntry::TYPE_SALE => 'success',
                        LedgerEntry::TYPE_COMMISSION => 'info',
                        LedgerEntry::TYPE_PAYOUT => 'warning',
                        LedgerEntry::TYPE_REFUND => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->placeholder('Platform')
                    ->searchable(),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable(),

                TextColumn::make('credit')
                    ->label('Credit (+)')
                    ->formatStateUsing(fn (int $state, LedgerEntry $record) => $state > 0 ? Money::format($state, $record->currency) : '—')
                    ->color('success'),

                TextColumn::make('debit')
                    ->label('Debit (-)')
                    ->formatStateUsing(fn (int $state, LedgerEntry $record) => $state > 0 ? Money::format($state, $record->currency) : '—')
                    ->color('danger'),

                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        LedgerEntry::TYPE_SALE => 'Sale',
                        LedgerEntry::TYPE_COMMISSION => 'Commission',
                        LedgerEntry::TYPE_PAYOUT => 'Payout',
                        LedgerEntry::TYPE_REFUND => 'Refund',
                        LedgerEntry::TYPE_ADJUSTMENT => 'Adjustment',
                    ]),

                SelectFilter::make('vendor')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
