<?php

namespace App\Filament\Vendor\Resources\Payouts\Tables;

use App\Models\Payout;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No payouts yet')
            ->emptyStateDescription('Settlements appear here once delivered orders clear the escrow window.')
            ->columns([
                TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state, Payout $record) => Money::format($state, $record->currency))
                    ->sortable(),

                TextColumn::make('period_start')
                    ->label('Period')
                    ->formatStateUsing(fn ($state, Payout $record) => $record->period_start && $record->period_end
                        ? $record->period_start->format('d M').' – '.$record->period_end->format('d M Y')
                        : '—'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Payout::STATUS_PAID => 'success',
                        Payout::STATUS_FAILED => 'danger',
                        Payout::STATUS_PROCESSING => 'info',
                        default => 'warning',
                    }),

                TextColumn::make('gateway_transfer_id')
                    ->label('Reference')
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('processed_at')
                    ->label('Paid on')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Payout::STATUS_PENDING => 'Pending',
                        Payout::STATUS_PROCESSING => 'Processing',
                        Payout::STATUS_PAID => 'Paid',
                        Payout::STATUS_FAILED => 'Failed',
                    ]),
            ]);
    }
}
