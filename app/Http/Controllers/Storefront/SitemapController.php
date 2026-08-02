<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Vertical;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect();

        // 1. Static & Core Pages
        $urls->push(['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily']);
        $urls->push(['loc' => route('vendors.index'), 'priority' => '0.8', 'changefreq' => 'weekly']);
        $urls->push(['loc' => route('deals'), 'priority' => '0.8', 'changefreq' => 'daily']);
        $urls->push(['loc' => route('new-arrivals'), 'priority' => '0.8', 'changefreq' => 'daily']);

        // 2. Verticals & Categories
        foreach (Vertical::all() as $vertical) {
            $urls->push(['loc' => route('verticals.show', $vertical), 'priority' => '0.9', 'changefreq' => 'weekly']);
        }

        foreach (Category::all() as $category) {
            $urls->push(['loc' => route('categories.show', $category), 'priority' => '0.8', 'changefreq' => 'weekly']);
        }

        // 3. Vendors
        foreach (Vendor::approved()->get() as $vendor) {
            $urls->push(['loc' => route('vendors.show', $vendor), 'priority' => '0.7', 'changefreq' => 'weekly']);
        }

        // 4. Products
        foreach (Product::visible()->limit(1000)->get() as $product) {
            $urls->push(['loc' => route('products.show', $product), 'priority' => '0.9', 'changefreq' => 'daily']);
        }

        // 5. Static Legal Pages
        foreach (Page::where('is_published', true)->get() as $page) {
            $urls->push(['loc' => route('pages.show', $page->slug), 'priority' => '0.5', 'changefreq' => 'monthly']);
        }

        $xml = view('storefront.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'text/xml']);
    }

    public function robots(): Response
    {
        $sitemapUrl = route('sitemap');
        $content = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /vendor/\nDisallow: /account/\nDisallow: /checkout/\n\nSitemap: {$sitemapUrl}\n";

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
