<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\HandlesProductImages;
use App\Filament\Support\HasAiAutofill;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HandlesProductImages;
    use HasAiAutofill;

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->takeImages($data);
    }

    protected function afterSave(): void
    {
        $this->saveImages($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            ...$this->aiHeaderActions(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
