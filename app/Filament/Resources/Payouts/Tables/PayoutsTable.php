<?php

namespace App\Filament\Resources\Payouts\Tables;

use App\Models\Payout;
use App\Services\PayoutService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

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
                    })
                    ->sortable(),

                TextColumn::make('gateway')
                    ->badge()
                    ->placeholder('Manual')
                    ->toggleable(),

                TextColumn::make('gateway_transfer_id')
                    ->label('Reference')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('processed_at')
                    ->dateTime('d M Y')
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

                SelectFilter::make('vendor')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('processTransfer')
                    ->label('Process Transfer')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->color('primary')
                    ->visible(fn (Payout $record) => $record->status !== Payout::STATUS_PAID)
                    ->action(function (Payout $record, PayoutService $payoutService): void {
                        $success = $payoutService->processPayout($record);

                        if ($success) {
                            Notification::make()->success()->title('Payout transfer completed successfully.')->send();
                        } else {
                            Notification::make()->danger()->title('Payout transfer failed. Check failure reason.')->send();
                        }
                    }),

                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->action(function (Payout $record, PayoutService $payoutService): StreamedResponse {
                        $csvContent = $payoutService->exportCsv($record);
                        $filename = "payout-{$record->id}-vendor-{$record->vendor_id}.csv";

                        return response()->streamDownload(function () use ($csvContent) {
                            echo $csvContent;
                        }, $filename, ['Content-Type' => 'text/csv']);
                    }),

                Action::make('markPaid')
                    ->label('Mark paid')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->visible(fn (Payout $record) => $record->status !== Payout::STATUS_PAID)
                    ->schema([
                        TextInput::make('gateway_transfer_id')
                            ->label('Bank / gateway reference')
                            ->required(),
                    ])
                    ->action(function (Payout $record, array $data, PayoutService $payoutService): void {
                        $payoutService->processPayout($record, 'bank_transfer');

                        $record->update([
                            'gateway_transfer_id' => $data['gateway_transfer_id'],
                        ]);

                        Notification::make()->success()->title('Payout marked as paid')->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
