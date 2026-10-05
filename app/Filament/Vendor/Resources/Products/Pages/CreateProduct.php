<?php

namespace App\Filament\Vendor\Resources\Products\Pages;

use App\Filament\Support\HandlesProductImages;
use App\Filament\Vendor\Resources\Products\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateProduct extends CreateRecord
{
    use HandlesProductImages;

    protected static string $resource = ProductResource::class;

    /** New listings always enter the moderation queue. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['approval_status'] = Product::APPROVAL_PENDING;
        $data['published_at'] = null;
        $data['vertical_id'] = Category::find($data['category_id'] ?? null)?->vertical_id;
        $data['slug'] = $this->uniqueSlug((string) $data['name']);

        return $this->takeImages($data);
    }

    protected function afterCreate(): void
    {
        $this->saveImages($this->record);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Product submitted')
            ->body('Our team reviews new listings before they appear on the storefront.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;

        for ($i = 2; Product::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
