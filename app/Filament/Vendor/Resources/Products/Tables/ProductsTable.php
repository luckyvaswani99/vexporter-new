<?php

namespace App\Filament\Vendor\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Add your first listing — it goes live once our team approves it.')
            ->columns([
                TextColumn::make('name')
                    ->description(fn (Product $record) => $record->sku)
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('price_label')
                    ->label('Price')
                    ->description(fn (Product $record) => 'MOQ '.$record->moq.' '.$record->unit),

                TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Product::APPROVAL_APPROVED => 'success',
                        Product::APPROVAL_PENDING => 'warning',
                        default => 'danger',
                    })
                    ->description(fn (Product $record) => $record->rejection_reason),

                IconColumn::make('is_active')
                    ->label('Live')
                    ->boolean(),

                TextColumn::make('rating_cache')
                    ->label('Rating')
                    ->numeric(decimalPlaces: 1)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('approval_status')
                    ->label('Status')
                    ->options([
                        Product::APPROVAL_PENDING => 'Pending',
                        Product::APPROVAL_APPROVED => 'Approved',
                        Product::APPROVAL_REJECTED => 'Rejected',
                    ]),

                TernaryFilter::make('is_active')->label('Available'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                // Rejected listings can be corrected and pushed back into the queue.
                Action::make('resubmit')
                    ->label('Submit for review')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('warning')
                    ->visible(fn (Product $record) => $record->approval_status === Product::APPROVAL_REJECTED)
                    ->requiresConfirmation()
                    ->action(function (Product $record): void {
                        $record->update([
                            'approval_status' => Product::APPROVAL_PENDING,
                            'rejection_reason' => null,
                        ]);

                        Notification::make()->success()->title('Sent for review')->send();
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
