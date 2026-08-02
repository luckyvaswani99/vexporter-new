<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Vendors\VendorResource;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingApprovals extends TableWidget
{
    protected static ?string $heading = 'Vendors awaiting review';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vendor::query()
                    ->where('status', Vendor::STATUS_PENDING)
                    ->withCount('documents')
                    ->latest(),
            )
            ->emptyStateHeading('No vendors waiting')
            ->emptyStateDescription('New applications will appear here for KYC review.')
            ->columns([
                TextColumn::make('name')
                    ->label('Store')
                    ->description(fn (Vendor $vendor) => $vendor->legal_name)
                    ->searchable(),

                TextColumn::make('location')
                    ->label('Location'),

                TextColumn::make('iec_code')
                    ->label('IEC'),

                TextColumn::make('documents_count')
                    ->label('Documents')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->url(fn (Vendor $vendor) => VendorResource::getUrl('edit', ['record' => $vendor])),
            ]);
    }
}
