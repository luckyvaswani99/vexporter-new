<?php

namespace App\Filament\Vendor\Widgets;

use App\Filament\Vendor\Pages\StoreProfile;
use App\Filament\Vendor\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\Vendor;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * Plain-language checklist for new sellers; disappears once everything is done.
 */
class GetStarted extends Widget
{
    protected string $view = 'filament.vendor.widgets.get-started';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) self::steps();
    }

    /** @return array<int, array{label: string, hint: string, done: bool, url: string, cta: string}> */
    public static function steps(): array
    {
        /** @var Vendor|null $vendor */
        $vendor = Filament::getTenant();

        if (! $vendor) {
            return [];
        }

        $products = Product::where('vendor_id', $vendor->id);
        $hasProduct = (clone $products)->exists();
        $hasPhotos = (clone $products)->whereHas('images')->exists();

        $steps = [
            [
                'label' => 'Add your store logo',
                'hint' => 'Buyers trust stores that show a real logo.',
                'done' => filled($vendor->logo),
                'url' => StoreProfile::getUrl(),
                'cta' => 'Upload logo',
            ],
            [
                'label' => 'List your first product',
                'hint' => 'Name, price and a few photos is all it takes.',
                'done' => $hasProduct,
                'url' => ProductResource::getUrl('create'),
                'cta' => 'Add product',
            ],
            [
                'label' => 'Add photos to your products',
                'hint' => 'Listings with pictures get far more enquiries.',
                'done' => $hasProduct && $hasPhotos,
                'url' => ProductResource::getUrl('index'),
                'cta' => 'Open products',
            ],
        ];

        return collect($steps)->every(fn (array $step) => $step['done']) ? [] : $steps;
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return ['steps' => self::steps()];
    }
}
