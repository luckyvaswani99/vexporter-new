<?php

namespace App\Filament\Resources\Disputes\Tables;

use App\Models\Dispute;
use App\Models\Order;
use App\Payments\PaymentManager;
use App\Services\EscrowService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DisputesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Dispute Ref')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.reference')
                    ->label('Order Ref')
                    ->searchable(),

                TextColumn::make('buyer.name')
                    ->label('Buyer')
                    ->searchable(),

                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable(),

                TextColumn::make('reason')
                    ->limit(30),

                TextColumn::make('refund_amount')
                    ->label('Claim Amount')
                    ->formatStateUsing(fn (int $state, Dispute $record) => Money::format($state, $record->order->currency ?? 'USD')),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Dispute::STATUS_RESOLVED_REFUND => 'success',
                        Dispute::STATUS_RESOLVED_RELEASED => 'info',
                        Dispute::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Dispute::STATUS_OPEN => 'Open',
                        Dispute::STATUS_UNDER_REVIEW => 'Under Review',
                        Dispute::STATUS_RESOLVED_REFUND => 'Resolved (Refund)',
                        Dispute::STATUS_RESOLVED_RELEASED => 'Resolved (Released)',
                        Dispute::STATUS_REJECTED => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('resolveRefund')
                    ->label('Resolve (Refund Buyer)')
                    ->icon('heroicon-m-arrow-path')
                    ->color('danger')
                    ->visible(fn (Dispute $record) => in_array($record->status, [Dispute::STATUS_OPEN, Dispute::STATUS_UNDER_REVIEW]))
                    ->schema([
                        Textarea::make('resolution_note')->label('Arbitration Note')->required(),
                    ])
                    ->action(function (Dispute $record, array $data, PaymentManager $paymentManager): void {
                        $payment = $record->order->payments()->where('status', 'captured')->first();

                        if ($payment) {
                            $gateway = $paymentManager->driver($payment->gateway);
                            $gateway->refund($payment, $record->refund_amount, $data['resolution_note']);
                        }

                        $record->update([
                            'status' => Dispute::STATUS_RESOLVED_REFUND,
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                            'resolution_note' => $data['resolution_note'],
                        ]);

                        $record->subOrder->update(['payout_status' => 'refunded']);
                        $record->order->update(['payment_status' => Order::PAYMENT_REFUNDED]);

                        Notification::make()->success()->title('Dispute resolved with buyer refund.')->send();
                    }),

                Action::make('resolveRelease')
                    ->label('Resolve (Release Escrow)')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (Dispute $record) => in_array($record->status, [Dispute::STATUS_OPEN, Dispute::STATUS_UNDER_REVIEW]))
                    ->schema([
                        Textarea::make('resolution_note')->label('Arbitration Note')->required(),
                    ])
                    ->action(function (Dispute $record, array $data, EscrowService $escrowService): void {
                        $escrowService->release($record->subOrder);

                        $record->update([
                            'status' => Dispute::STATUS_RESOLVED_RELEASED,
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                            'resolution_note' => $data['resolution_note'],
                        ]);

                        Notification::make()->success()->title('Dispute resolved & escrow released to vendor.')->send();
                    }),
            ]);
    }
}
