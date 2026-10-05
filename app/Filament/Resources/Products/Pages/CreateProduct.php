<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\HandlesProductImages;
use App\Filament\Support\HasAiAutofill;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use HandlesProductImages;
    use HasAiAutofill;

    protected function getHeaderActions(): array
    {
        return $this->aiHeaderActions();
    }

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->takeImages($data);
    }

    protected function afterCreate(): void
    {
        $this->saveImages($this->record);
    }
}
