<?php

namespace App\Filament\Vendor\Resources\Rfqs\Tables;

use App\Actions\Rfq\SubmitQuote;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Support\Countries;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RfqsTable
{
    /** The panel tenant, narrowed to the model we actually work with. */
    private static function tenant(): ?Vendor
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Vendor ? $tenant : null;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No quote requests yet')
            ->emptyStateDescription('Buyers sourcing in your categories will appear here.')
            ->columns([
                TextColumn::make('title')
                    ->description(fn (Rfq $record) => $record->reference)
                    ->searchable()
                    ->limit(45),

                TextColumn::make('qty')
                    ->label('Quantity')
                    ->formatStateUsing(fn (?int $state, Rfq $record) => number_format((int) $state).' '.$record->unit),

                TextColumn::make('destination_country')
                    ->label('Destination')
                    ->formatStateUsing(fn (?string $state) => Countries::name($state))
                    ->badge(),

                TextColumn::make('incoterm')->badge()->color('gray'),

                TextColumn::make('target_price')
                    ->label('Target')
                    ->formatStateUsing(fn (?int $state, Rfq $record) => $state ? Money::format($state, $record->currency) : '—'),

                TextColumn::make('quotes_count')
                    ->label('Quotes')
                    ->counts('quotes')
                    ->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        Rfq::STATUS_CONVERTED, Rfq::STATUS_ACCEPTED => 'success',
                        Rfq::STATUS_QUOTED => 'info',
                        Rfq::STATUS_EXPIRED => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Rfq::STATUS_OPEN => 'Open',
                        Rfq::STATUS_QUOTED => 'Quoted',
                        Rfq::STATUS_CONVERTED => 'Converted',
                        Rfq::STATUS_EXPIRED => 'Expired',
                    ]),

                Filter::make('not_quoted')
                    ->label('Awaiting my quote')
                    ->query(fn (Builder $query) => $query->whereDoesntHave(
                        'quotes',
                        fn (Builder $inner) => $inner->where('vendor_id', self::tenant()?->getKey()),
                    )),
            ])
            ->recordActions([
                Action::make('viewRequest')
                    ->label('Details')
                    ->icon('heroicon-m-eye')
                    ->modalHeading(fn (Rfq $record) => $record->title)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (Rfq $record) => view('filament.vendor.rfq-details', ['rfq' => $record])),

                Action::make('quote')
                    ->label('Send quote')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('primary')
                    ->modalHeading(fn (Rfq $record) => "Quote for: {$record->title}")
                    ->modalSubmitActionLabel('Send quote to buyer')
                    ->modalWidth('4xl')
                    ->visible(fn (Rfq $record) => ! $record->quotes()
                        ->where('vendor_id', self::tenant()?->getKey())
                        ->whereIn('status', ['sent', 'revised', 'accepted'])
                        ->exists())
                    ->fillForm(fn (Rfq $record) => [
                        'currency' => $record->currency,
                        'incoterm' => $record->incoterm,
                        'items' => [[
                            'product_id' => $record->product_id,
                            'description' => $record->product?->name ?? $record->title,
                            'qty' => $record->qty,
                            'unit' => $record->unit,
                            'unit_price' => $record->target_price,
                        ]],
                    ])
                    ->schema([
                        Section::make('Line items')
                            ->description('Prices are in cents — 850 = $8.50.')
                            ->schema([
                                Repeater::make('items')
                                    ->hiddenLabel()
                                    ->columns(5)
                                    ->minItems(1)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Product')
                                            ->options(fn () => self::tenant()?->products()->pluck('name', 'id')->all() ?? [])
                                            ->searchable()
                                            ->columnSpan(2),

                                        TextInput::make('description')
                                            ->required()
                                            ->columnSpan(3),

                                        TextInput::make('qty')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('unit')
                                            ->label('Unit')
                                            ->required(),

                                        TextInput::make('unit_price')
                                            ->label('Unit price (cents)')
                                            ->numeric()
                                            ->required()
                                            ->columnSpan(3),
                                    ]),
                            ]),

                        Section::make('Terms')
                            ->columns(3)
                            ->schema([
                                TextInput::make('shipping')
                                    ->label('Freight (cents)')
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('tax')
                                    ->label('Duties / tax (cents)')
                                    ->numeric()
                                    ->default(0),

                                Select::make('currency')
                                    ->options(['USD' => 'USD', 'INR' => 'INR', 'EUR' => 'EUR'])
                                    ->required(),

                                Select::make('incoterm')
                                    ->options(collect(['EXW', 'FOB', 'CIF', 'CFR', 'DAP', 'DDP'])
                                        ->mapWithKeys(fn (string $term) => [$term => $term])
                                        ->all())
                                    ->required(),

                                TextInput::make('lead_time_days')
                                    ->label('Lead time (days)')
                                    ->numeric(),

                                DatePicker::make('validity_until')
                                    ->label('Quote valid until')
                                    ->default(now()->addDays(14)),

                                TextInput::make('payment_terms')
                                    ->placeholder('30% advance, 70% against BL')
                                    ->columnSpan(2),

                                Textarea::make('notes')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->action(function (Rfq $record, array $data): void {
                        $vendor = self::tenant();

                        abort_if($vendor === null, 403);

                        $quote = app(SubmitQuote::class)->handle($record, $vendor, $data);

                        Notification::make()
                            ->success()
                            ->title("Quote {$quote->reference} sent")
                            ->body('The buyer has been notified and can compare it against other quotes.')
                            ->send();
                    }),
            ]);
    }
}
