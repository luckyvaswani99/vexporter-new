<?php

namespace App\Filament\Vendor\Resources\Products\Pages;

use App\Filament\Support\HandlesProductImages;
use App\Filament\Vendor\Resources\Products\ProductResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HandlesProductImages;

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['vertical_id'] = Category::find($data['category_id'] ?? null)?->vertical_id ?? $this->record->vertical_id;

        return $this->takeImages($data);
    }

    protected function afterSave(): void
    {
        $this->saveImages($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
