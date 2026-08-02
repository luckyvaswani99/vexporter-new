<?php

namespace App\Filament\Vendor\Resources\SubOrders\Tables;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\SubOrder;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class SubOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No orders yet')
            ->columns([
                TextColumn::make('reference')
                    ->label('Order')
                    ->description(fn (SubOrder $record) => $record->order?->reference)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.buyer.name')
                    ->label('Buyer')
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge(),

                TextColumn::make('total')
                    ->formatStateUsing(fn (int $state, SubOrder $record) => Money::format($state, $record->order?->currency ?? 'USD'))
                    ->sortable(),

                TextColumn::make('vendor_payout_amount')
                    ->label('Your payout')
                    ->formatStateUsing(fn (int $state, SubOrder $record) => Money::format($state, $record->order?->currency ?? 'USD'))
                    ->description(fn (SubOrder $record) => 'after '.Money::format($record->commission_amount, $record->order?->currency ?? 'USD').' commission')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Order::STATUS_COMPLETED, Order::STATUS_DELIVERED => 'success',
                        Order::STATUS_CANCELLED => 'danger',
                        Order::STATUS_SHIPPED => 'info',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('payout_status')
                    ->label('Payout')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => $state === SubOrder::PAYOUT_PAID ? 'success' : 'gray')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect([
                        Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING,
                        Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED,
                    ])->mapWithKeys(fn (string $status) => [$status => str($status)->headline()->toString()])->all()),

                SelectFilter::make('payout_status')
                    ->label('Payout')
                    ->options([
                        SubOrder::PAYOUT_PENDING => 'Pending',
                        SubOrder::PAYOUT_ELIGIBLE => 'Eligible',
                        SubOrder::PAYOUT_PAID => 'Paid',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('accept')
                        ->label('Accept order')
                        ->icon('heroicon-m-check')
                        ->color('success')
                        ->visible(fn (SubOrder $record) => $record->status === Order::STATUS_PENDING)
                        ->requiresConfirmation()
                        ->action(fn (SubOrder $record) => self::transition($record, Order::STATUS_CONFIRMED, 'Order accepted')),

                    Action::make('process')
                        ->label('Start processing')
                        ->icon('heroicon-m-cog-6-tooth')
                        ->visible(fn (SubOrder $record) => $record->status === Order::STATUS_CONFIRMED)
                        ->action(fn (SubOrder $record) => self::transition($record, Order::STATUS_PROCESSING, 'Production started')),

                    Action::make('ship')
                        ->label('Mark shipped')
                        ->icon('heroicon-m-truck')
                        ->color('info')
                        ->visible(fn (SubOrder $record) => in_array($record->status, [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING], true))
                        ->schema([
                            Select::make('carrier')
                                ->options([
                                    'DHL' => 'DHL',
                                    'FedEx' => 'FedEx',
                                    'Maersk' => 'Maersk',
                                    'Blue Dart' => 'Blue Dart',
                                    'Other' => 'Other',
                                ])
                                ->required(),
                            TextInput::make('tracking_no')->label('Tracking / AWB / BL number')->required(),
                            TextInput::make('port_of_loading')->label('Port of loading'),
                            Textarea::make('note')->rows(2),
                        ])
                        ->action(function (SubOrder $record, array $data): void {
                            DB::transaction(function () use ($record, $data): void {
                                Shipment::create([
                                    'sub_order_id' => $record->id,
                                    'carrier' => $data['carrier'],
                                    'tracking_no' => $data['tracking_no'],
                                    'port_of_loading' => $data['port_of_loading'] ?? null,
                                    'incoterm' => $record->order?->incoterm,
                                    'status' => 'in_transit',
                                    'shipped_at' => now(),
                                ]);

                                self::transition($record, Order::STATUS_SHIPPED, $data['note'] ?? 'Shipment dispatched');
                            });
                        }),

                    Action::make('deliver')
                        ->label('Mark delivered')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->visible(fn (SubOrder $record) => $record->status === Order::STATUS_SHIPPED)
                        ->requiresConfirmation()
                        ->action(function (SubOrder $record): void {
                            self::transition($record, Order::STATUS_DELIVERED, 'Delivered to buyer');

                            // Delivery starts the escrow release clock.
                            $record->update(['payout_status' => SubOrder::PAYOUT_ELIGIBLE]);
                        }),
                ]),
            ]);
    }

    /** Moves the sub-order forward and keeps an auditable history entry. */
    private static function transition(SubOrder $subOrder, string $status, ?string $note = null): void
    {
        $from = $subOrder->status;

        $subOrder->update(['status' => $status]);

        $subOrder->statusHistory()->create([
            'from_status' => $from,
            'to_status' => $status,
            'actor_id' => auth()->id(),
            'note' => $note,
        ]);

        Notification::make()
            ->success()
            ->title('Order updated to '.str($status)->headline())
            ->send();
    }
}
