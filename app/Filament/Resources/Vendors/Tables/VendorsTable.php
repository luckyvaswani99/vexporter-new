<?php

namespace App\Filament\Resources\Vendors\Tables;

use App\Actions\Vendors\ApproveVendor;
use App\Actions\Vendors\RejectVendor;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Store')
                    ->description(fn (Vendor $record) => $record->legal_name)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Location')
                    ->searchable(['city', 'state']),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Vendor::STATUS_APPROVED => 'success',
                        Vendor::STATUS_PENDING => 'warning',
                        Vendor::STATUS_REJECTED, Vendor::STATUS_SUSPENDED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('iec_code')
                    ->label('IEC')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('products_count_cache')
                    ->label('Products')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('rating_cache')
                    ->label('Rating')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),

                TextColumn::make('commission_percent')
                    ->label('Commission')
                    ->formatStateUsing(fn (?string $state) => ($state ?? config('vexporter.commission_percent')).'%')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Vendor::STATUS_PENDING => 'Pending review',
                        Vendor::STATUS_APPROVED => 'Approved',
                        Vendor::STATUS_SUSPENDED => 'Suspended',
                        Vendor::STATUS_REJECTED => 'Rejected',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (Vendor $record) => $record->status !== Vendor::STATUS_APPROVED)
                    ->requiresConfirmation()
                    ->modalHeading(fn (Vendor $record) => "Approve {$record->name}?")
                    ->modalDescription('Submitted documents will be marked verified and the store goes live.')
                    ->schema([
                        Textarea::make('note')
                            ->label('Internal note')
                            ->rows(2),
                    ])
                    ->action(function (Vendor $record, array $data): void {
                        app(ApproveVendor::class)->handle($record, auth()->user(), $data['note'] ?? null);

                        Notification::make()
                            ->success()
                            ->title("{$record->name} approved")
                            ->body('The owner has been notified and can start listing products.')
                            ->send();
                    }),

                Action::make('reject')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (Vendor $record) => $record->status === Vendor::STATUS_PENDING)
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->rows(3)
                            ->helperText('Shared with the vendor so they can correct and resubmit.'),
                    ])
                    ->action(function (Vendor $record, array $data): void {
                        app(RejectVendor::class)->handle($record, auth()->user(), $data['reason']);

                        Notification::make()
                            ->warning()
                            ->title("{$record->name} rejected")
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
