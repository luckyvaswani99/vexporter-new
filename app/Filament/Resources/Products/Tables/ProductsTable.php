<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->description(fn (Product $record) => $record->sku)
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('price_label')
                    ->label('Price')
                    ->description(fn (Product $record) => 'MOQ '.$record->moq.' '.$record->unit),

                TextColumn::make('approval_status')
                    ->label('Approval')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Product::APPROVAL_APPROVED => 'success',
                        Product::APPROVAL_PENDING => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                IconColumn::make('requires_license')
                    ->label('Licensed')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-exclamation')
                    ->falseIcon('heroicon-o-minus')
                    ->toggleable(),

                TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('approval_status')
                    ->label('Approval')
                    ->options([
                        Product::APPROVAL_PENDING => 'Pending',
                        Product::APPROVAL_APPROVED => 'Approved',
                        Product::APPROVAL_REJECTED => 'Rejected',
                    ]),

                SelectFilter::make('vertical')
                    ->relationship('vertical', 'name')
                    ->label('Vertical'),

                SelectFilter::make('vendor')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('requires_license')->label('Requires licence'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (Product $record) => $record->approval_status !== Product::APPROVAL_APPROVED)
                    ->requiresConfirmation()
                    ->action(function (Product $record): void {
                        $record->update([
                            'approval_status' => Product::APPROVAL_APPROVED,
                            'rejection_reason' => null,
                            'published_at' => $record->published_at ?? now(),
                        ]);

                        Notification::make()->success()->title("{$record->name} is live")->send();
                    }),

                Action::make('reject')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (Product $record) => $record->approval_status !== Product::APPROVAL_REJECTED)
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $record->update([
                            'approval_status' => Product::APPROVAL_REJECTED,
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()->warning()->title("{$record->name} rejected")->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Approve selected')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (Product $product) => $product->update([
                                'approval_status' => Product::APPROVAL_APPROVED,
                                'published_at' => $product->published_at ?? now(),
                            ]));

                            Notification::make()->success()->title($records->count().' products approved')->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
