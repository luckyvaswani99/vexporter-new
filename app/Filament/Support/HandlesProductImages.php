<?php

namespace App\Filament\Support;

use App\Models\Product;

/**
 * Wires {@see ProductImages} into a Create/Edit product page.
 */
trait HandlesProductImages
{
    /** @var array<int, string> */
    protected array $uploadedImagePaths = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function takeImages(array $data): array
    {
        [$data, $this->uploadedImagePaths] = ProductImages::extract($data);

        return $data;
    }

    protected function saveImages(Product $product): void
    {
        ProductImages::sync($product, $this->uploadedImagePaths);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->getRecord() instanceof Product) {
            $data[ProductImages::FIELD] = ProductImages::pathsFor($this->getRecord());
        }

        return $data;
    }
}
