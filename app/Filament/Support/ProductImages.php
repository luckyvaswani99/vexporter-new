<?php

namespace App\Filament\Support;

use App\Models\Product;
use App\Models\ProductImage;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;

/**
 * Photo upload shared by the admin and vendor product forms.
 *
 * The field is not a column: the pages below move its paths in and out of the
 * `product_images` table, so the first photo is always the one shown on cards.
 */
class ProductImages
{
    public const FIELD = 'image_files';

    public static function field(): FileUpload
    {
        return FileUpload::make(self::FIELD)
            ->label('Product photos')
            ->helperText('Drag photos here or click to browse. The first photo is the cover — drag to reorder. JPG, PNG or WebP, up to 5 MB each.')
            ->image()
            ->multiple()
            ->reorderable()
            ->appendFiles()
            ->maxFiles(8)
            ->maxSize(5120)
            ->imageEditor()
            ->panelLayout('grid')
            ->disk('public')
            ->directory('products')
            ->visibility('public')
            ->columnSpanFull();
    }

    /** @return array<int, string> */
    public static function pathsFor(Product $product): array
    {
        return $product->images()->pluck('path')->all();
    }

    /**
     * Pull the upload field out of the form data so it never reaches the
     * products table.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    public static function extract(array $data): array
    {
        $paths = array_values(array_filter((array) ($data[self::FIELD] ?? [])));
        unset($data[self::FIELD]);

        return [$data, $paths];
    }

    /** @param  array<int, string>  $paths */
    public static function sync(Product $product, array $paths): void
    {
        $existing = $product->images()->get()->keyBy('path');

        foreach ($existing as $path => $image) {
            if (! in_array($path, $paths, true)) {
                $image->delete();
            }
        }

        foreach ($paths as $order => $path) {
            $product->images()->updateOrCreate(
                ['path' => $path],
                ['sort_order' => $order, 'is_primary' => $order === 0, 'alt' => $product->name],
            );
        }

        // Files are only removed from disk once nothing references them.
        foreach ($existing as $path => $image) {
            if (! in_array($path, $paths, true) && ! ProductImage::where('path', $path)->exists()) {
                Storage::disk('public')->delete($path);
            }
        }

        $product->unsetRelation('images');
    }
}
