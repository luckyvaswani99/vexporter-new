<?php

namespace App\Filament\Support;

use App\Services\Ai\ProductAiGenerator;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Throwable;

/**
 * Adds the "Auto-fill with AI" button to a Create/Edit product page.
 *
 * The answer is written into the open form only; nothing is saved until the
 * seller reviews it and presses the normal save button.
 */
trait HasAiAutofill
{
    /** @return array<int, Action> */
    protected function aiHeaderActions(): array
    {
        return [
            Action::make('aiAutofill')
                ->label('Auto-fill with AI')
                ->icon('heroicon-m-sparkles')
                ->color('info')
                ->visible(fn (): bool => app(ProductAiGenerator::class)->available())
                ->modalHeading('Auto-fill this listing with AI')
                ->modalDescription('Type the product name. AI fills in the category, details, description and search text for you. You still add photos and the price, and check everything before saving.')
                ->modalSubmitActionLabel('Fill my listing')
                ->schema([
                    TextInput::make('name')
                        ->label('Product name')
                        ->placeholder('e.g. Paracetamol 650 mg tablets')
                        ->required()
                        ->default(fn (): ?string => $this->data['name'] ?? null),
                ])
                ->action(function (array $data): void {
                    try {
                        $state = app(ProductAiGenerator::class)->generate($data['name'], Filament::getTenant()?->name);
                    } catch (Throwable $e) {
                        report($e);

                        Notification::make()->danger()->title('AI could not fill the listing')->body($e->getMessage())->send();

                        return;
                    }

                    foreach ($state as $field => $value) {
                        $this->data[$field] = $value;
                    }

                    Notification::make()
                        ->success()
                        ->title('Listing filled in')
                        ->body('Please check the details — especially strength, manufacturer and category — then add photos and a price.')
                        ->send();
                }),
        ];
    }
}
