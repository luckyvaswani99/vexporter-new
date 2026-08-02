<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('placed_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Order')
                    ->description(fn (Order $record) => $record->incoterm)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('buyer.name')
                    ->label('Buyer')
                    ->description(fn (Order $record) => $record->buyer?->email)
                    ->searchable(),

                TextColumn::make('sub_orders_count')
                    ->label('Vendors')
                    ->counts('subOrders')
                    ->badge(),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state, Order $record) => Money::format($state, $record->currency))
                    ->sortable(),

                TextColumn::make('commission_total')
                    ->label('Commission')
                    ->formatStateUsing(fn (int $state, Order $record) => Money::format($state, $record->currency))
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Order::STATUS_COMPLETED, Order::STATUS_DELIVERED => 'success',
                        Order::STATUS_CANCELLED => 'danger',
                        Order::STATUS_SHIPPED, Order::STATUS_PROCESSING => 'info',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Order::PAYMENT_RELEASED => 'success',
                        Order::PAYMENT_ESCROW_HELD => 'info',
                        Order::PAYMENT_REFUNDED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('placed_at')
                    ->label('Placed')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect([
                        Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING,
                        Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED,
                        Order::STATUS_CANCELLED,
                    ])->mapWithKeys(fn (string $status) => [$status => str($status)->headline()->toString()])->all()),

                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options([
                        Order::PAYMENT_UNPAID => 'Unpaid',
                        Order::PAYMENT_ESCROW_HELD => 'In escrow',
                        Order::PAYMENT_RELEASED => 'Released',
                        Order::PAYMENT_REFUNDED => 'Refunded',
                    ]),

                Filter::make('last_30_days')
                    ->label('Last 30 days')
                    ->query(fn (Builder $query) => $query->where('placed_at', '>=', now()->subDays(30))),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
