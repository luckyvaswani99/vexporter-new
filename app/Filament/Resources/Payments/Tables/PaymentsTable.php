<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use App\Payments\PaymentManager;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order.reference')
                    ->label('Order Ref')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gateway')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->sortable(),

                TextColumn::make('gateway_payment_id')
                    ->label('Transaction ID')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state, Payment $record) => Money::format($state, $record->currency))
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        'captured', 'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('gateway')
                    ->options([
                        'razorpay' => 'Razorpay',
                        'stripe' => 'Stripe',
                        'bank_transfer' => 'Bank Transfer',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'created' => 'Created',
                        'captured' => 'Captured',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->recordActions([
                Action::make('refund')
                    ->label('Process Refund')
                    ->icon('heroicon-m-arrow-path')
                    ->color('danger')
                    ->visible(fn (Payment $record) => $record->status === 'captured')
                    ->schema([
                        TextInput::make('reason')->label('Reason for Refund')->required(),
                    ])
                    ->action(function (Payment $record, array $data, PaymentManager $paymentManager): void {
                        $gateway = $paymentManager->driver($record->gateway);
                        $result = $gateway->refund($record, $record->amount, $data['reason']);

                        if ($result->isSuccess) {
                            $record->update(['status' => 'refunded']);
                            $record->order->update(['payment_status' => 'refunded']);
                            Notification::make()->success()->title('Refund processed successfully.')->send();
                        } else {
                            Notification::make()->danger()->title('Refund failed: '.($result->errorMessage ?? 'Unknown error'))->send();
                        }
                    }),
            ]);
    }
}
