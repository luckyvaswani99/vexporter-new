<?php

namespace App\Filament\Vendor\Resources\Quotes\Tables;

use App\Models\Quote;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No quotes sent yet')
            ->emptyStateDescription('Answer a quote request and it will show up here.')
            ->columns([
                TextColumn::make('reference')
                    ->label('Quote')
                    ->description(fn (Quote $record) => $record->rfq?->title)
                    ->searchable(),

                TextColumn::make('rfq.buyer.name')
                    ->label('Buyer')
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Lines')
                    ->counts('items')
                    ->badge(),

                TextColumn::make('total')
                    ->formatStateUsing(fn (int $state, Quote $record) => Money::format($state, $record->currency))
                    ->description(fn (Quote $record) => $record->incoterm)
                    ->sortable(),

                TextColumn::make('lead_time_days')
                    ->label('Lead time')
                    ->formatStateUsing(fn (?int $state) => $state ? $state.' days' : '—')
                    ->toggleable(),

                TextColumn::make('validity_until')
                    ->label('Valid until')
                    ->date('d M Y')
                    ->color(fn (Quote $record) => $record->isExpired() ? 'danger' : null)
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Quote::STATUS_ACCEPTED => 'success',
                        Quote::STATUS_REJECTED, Quote::STATUS_EXPIRED => 'danger',
                        Quote::STATUS_REVISED => 'info',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Sent')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Quote::STATUS_SENT => 'Sent',
                        Quote::STATUS_REVISED => 'Revised',
                        Quote::STATUS_ACCEPTED => 'Accepted',
                        Quote::STATUS_REJECTED => 'Rejected',
                        Quote::STATUS_EXPIRED => 'Expired',
                    ]),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lines')
                    ->icon('heroicon-m-list-bullet')
                    ->modalHeading(fn (Quote $record) => "Quote {$record->reference}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (Quote $record) => view('filament.vendor.quote-details', ['quote' => $record])),

                // Buyers negotiate — a revision replaces the price they see.
                Action::make('revise')
                    ->label('Revise')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->visible(fn (Quote $record) => in_array($record->status, [Quote::STATUS_SENT, Quote::STATUS_REVISED], true))
                    ->fillForm(fn (Quote $record) => [
                        'shipping' => $record->shipping,
                        'lead_time_days' => $record->lead_time_days,
                        'validity_until' => $record->validity_until,
                        'payment_terms' => $record->payment_terms,
                    ])
                    ->schema([
                        TextInput::make('shipping')->label('Freight (cents)')->numeric()->required(),
                        TextInput::make('lead_time_days')->label('Lead time (days)')->numeric(),
                        DatePicker::make('validity_until')->label('Valid until'),
                        TextInput::make('payment_terms'),
                    ])
                    ->action(function (Quote $record, array $data): void {
                        $record->update([
                            'shipping' => (int) $data['shipping'],
                            'total' => $record->subtotal + (int) $data['shipping'] + $record->tax,
                            'lead_time_days' => $data['lead_time_days'] ?? null,
                            'validity_until' => $data['validity_until'] ?? null,
                            'payment_terms' => $data['payment_terms'] ?? null,
                            'status' => Quote::STATUS_REVISED,
                        ]);

                        Notification::make()->success()->title('Quote revised')->send();
                    }),

                Action::make('withdraw')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Quote $record) => in_array($record->status, [Quote::STATUS_SENT, Quote::STATUS_REVISED], true))
                    ->action(function (Quote $record): void {
                        $record->update(['status' => Quote::STATUS_REJECTED]);

                        Notification::make()->warning()->title('Quote withdrawn')->send();
                    }),
            ]);
    }
}
