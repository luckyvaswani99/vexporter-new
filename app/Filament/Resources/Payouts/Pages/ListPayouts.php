<?php

namespace App\Filament\Resources\Payouts\Pages;

use App\Filament\Resources\Payouts\PayoutResource;
use App\Services\PayoutService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayouts extends ListRecords
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateBatch')
                ->label('Generate Eligible Payouts Batch')
                ->icon('heroicon-m-banknotes')
                ->color('success')
                ->action(function (PayoutService $payoutService): void {
                    $payouts = $payoutService->generateBatch();
                    $count = $payouts->count();

                    if ($count > 0) {
                        Notification::make()
                            ->success()
                            ->title("Generated {$count} payout batch(es).")
                            ->send();
                    } else {
                        Notification::make()
                            ->info()
                            ->title('No eligible sub-orders pending payout.')
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
