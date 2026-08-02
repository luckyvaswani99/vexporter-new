<?php

namespace App\Filament\Resources\Vendors\RelationManagers;

use App\Models\VendorDocument;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'KYC documents';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->required(),
            TextInput::make('number'),
            TextInput::make('issuing_authority'),
            DatePicker::make('expires_at'),
            Textarea::make('review_note')->rows(2)->columnSpanFull(),
            Toggle::make('is_public')
                ->label('Show on storefront')
                ->helperText('Certificates like WHO-GMP are public; GST/PAN are not.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->description(fn (VendorDocument $record) => $record->number)
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        VendorDocument::STATUS_VERIFIED => 'success',
                        VendorDocument::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('expires_at')
                    ->date()
                    ->color(fn (?VendorDocument $record) => $record?->isExpired() ? 'danger' : null)
                    ->placeholder('—'),

                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                IconColumn::make('file_path')
                    ->label('File')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus'),
            ])
            ->recordActions([
                // Signed, short-lived URL — KYC files live on the private disk.
                Action::make('download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->visible(fn (VendorDocument $record) => filled($record->file_path))
                    ->url(fn (VendorDocument $record) => Storage::disk('local')->temporaryUrl($record->file_path, now()->addMinutes(5)))
                    ->openUrlInNewTab(),

                Action::make('verify')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (VendorDocument $record) => $record->status !== VendorDocument::STATUS_VERIFIED)
                    ->requiresConfirmation()
                    ->action(function (VendorDocument $record): void {
                        $record->update([
                            'status' => VendorDocument::STATUS_VERIFIED,
                            'reviewed_by' => auth()->id(),
                        ]);

                        Notification::make()->success()->title('Document verified')->send();
                    }),

                Action::make('rejectDocument')
                    ->label('Reject')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->visible(fn (VendorDocument $record) => $record->status !== VendorDocument::STATUS_REJECTED)
                    ->schema([
                        Textarea::make('review_note')->label('Reason')->required()->rows(2),
                    ])
                    ->action(function (VendorDocument $record, array $data): void {
                        $record->update([
                            'status' => VendorDocument::STATUS_REJECTED,
                            'reviewed_by' => auth()->id(),
                            'review_note' => $data['review_note'],
                        ]);

                        Notification::make()->warning()->title('Document rejected')->send();
                    }),

                EditAction::make(),
            ]);
    }
}
