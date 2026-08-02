<?php

namespace App\Services;

class SeoService
{
    /**
     * Generate metadata array for OpenGraph and Twitter cards.
     */
    public function generate(?string $title = null, ?string $description = null, ?string $url = null, ?string $image = null, string $type = 'website'): array
    {
        $siteName = config('app.name', 'VEXPORTER');
        $defaultTitle = "{$siteName} — Multivendor B2B E-Commerce Platform | Where The World Trades";
        $defaultDescription = 'Global B2B marketplace for Pharma APIs, Solar Panels, Electronics, Machinery, and General Goods from verified manufacturers.';

        $metaTitle = $title ? "{$title} | {$siteName}" : $defaultTitle;
        $metaDescription = $description ? (string) str($description)->stripTags()->limit(160) : $defaultDescription;
        $metaUrl = $url ?? url()->current();
        $metaImage = $image ?? asset('images/logo.svg');

        return [
            'title' => $metaTitle,
            'description' => $metaDescription,
            'canonical' => $metaUrl,
            'og' => [
                'site_name' => $siteName,
                'title' => $metaTitle,
                'description' => $metaDescription,
                'url' => $metaUrl,
                'image' => $metaImage,
                'type' => $type,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $metaTitle,
                'description' => $metaDescription,
                'image' => $metaImage,
            ],
        ];
    }
}
