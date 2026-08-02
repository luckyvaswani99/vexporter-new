<?php

namespace App\Filament\Vendor\Resources\Products\Pages;

use App\Filament\Vendor\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /** New listings always enter the moderation queue. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['approval_status'] = Product::APPROVAL_PENDING;
        $data['published_at'] = null;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Product submitted')
            ->body('Our team reviews new listings before they appear on the storefront.');
    }
}
