<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\Html;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductObserver
{
    /** Default HS codes per vertical, used when the seller leaves HSN blank. */
    private const HS_CODES = ['pharma' => '3004.90', 'solar' => '8541.43'];

    /**
     * Everything a seller should not have to type: a web address, a SKU, the
     * HS code and the search-result title/snippet are derived when left blank.
     */
    public function saving(Product $product): void
    {
        if (blank($product->slug) && filled($product->name)) {
            $product->slug = $this->uniqueSlug($product);
        }

        if (blank($product->sku) && filled($product->name)) {
            $product->sku = 'VX-'.$product->vendor_id.'-'.strtoupper(Str::random(6));
        }

        if (blank($product->hsn_code) && $product->vertical_id) {
            $product->hsn_code = self::HS_CODES[$product->vertical?->slug] ?? null;
        }

        if (blank($product->short_description) && filled($product->description)) {
            $product->short_description = Html::toText($product->description, 300);
        }

        if (blank($product->seo_title) && filled($product->name)) {
            $product->seo_title = Str::limit($product->name.' | '.config('app.name'), 70, '');
        }

        if (blank($product->seo_description)) {
            $text = Html::toText($product->short_description ?: $product->description, 160);
            $product->seo_description = filled($text) ? $text : null;
        }
    }

    private function uniqueSlug(Product $product): string
    {
        $base = Str::slug($product->name) ?: 'product';
        $slug = $base;

        for ($i = 2; Product::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    public function saved(Product $product): void
    {
        $this->clearCache();
    }

    public function deleted(Product $product): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget('homepage_featured_products');
        Cache::forget('catalog_facets_global');
    }
}
