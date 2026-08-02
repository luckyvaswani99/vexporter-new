<?php

namespace App\Services;

use App\Models\Product;

class JsonLdGenerator
{
    /**
     * Generate Organization Schema.org JSON-LD array.
     */
    public function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name', 'VEXPORTER'),
            'url' => route('home'),
            'logo' => asset('images/logo.svg'),
            'description' => 'Where The World Trades — Multivendor B2B E-Commerce Platform.',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => config('vexporter.contact.phone', '+91 98765 43210'),
                'contactType' => 'customer service',
                'email' => config('vexporter.contact.email', 'support@vexporter.com'),
            ],
        ];
    }

    /**
     * Generate Product & Offer Schema.org JSON-LD array.
     */
    public function product(Product $product): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => $product->primary_image ? [asset('storage/'.$product->primary_image)] : [],
            'description' => $product->short_description ?? $product->name,
            'sku' => $product->sku,
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->brand?->name ?? $product->vendor?->name ?? 'VEXPORTER',
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('products.show', $product),
                'priceCurrency' => $product->currency ?? 'USD',
                'price' => number_format($product->base_price / 100, 2, '.', ''),
                'priceValidUntil' => now()->addYear()->toDateString(),
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => $product->stock_qty > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $product->vendor?->name ?? 'VEXPORTER Vendor',
                ],
            ],
        ];

        if ($product->rating_cache > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) number_format((float) $product->rating_cache, 1),
                'reviewCount' => (string) max(1, $product->reviews_count),
            ];
        }

        return $schema;
    }

    /**
     * Generate BreadcrumbList Schema.org JSON-LD array.
     */
    public function breadcrumbs(array $crumbs): array
    {
        $itemList = [];
        $position = 1;

        $itemList[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => route('home'),
        ];

        foreach ($crumbs as $crumb) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $crumb['label'],
                'item' => $crumb['url'] ?? url()->current(),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemList,
        ];
    }
}
