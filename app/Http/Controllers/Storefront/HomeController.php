<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\Vendor;
use App\Models\Vertical;
use App\Support\Countries;
use App\Support\HomeAnalytics;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(HomeAnalytics $analytics): View
    {
        return view('storefront.home', [
            'heroStats' => $this->heroStats(),
            'verticals' => Vertical::with('categories')->orderBy('sort_order')->get(),
            'pharmaProducts' => $this->featuredProducts('pharma'),
            'solarProducts' => $this->featuredProducts('solar'),
            'flashDeal' => FlashSale::running()->latest('ends_at')->first(),
            'topVendors' => Vendor::approved()
                ->with(['documents', 'products.category'])
                ->orderByDesc('rating_cache')
                ->take($this->limit('vendors'))
                ->get(),
            'totalVendors' => Vendor::approved()->count(),
            'analytics' => fluent(Cache::remember('home:analytics', now()->addMinutes(15), fn () => $analytics->build())),
            'testimonials' => Testimonial::featured()->take($this->limit('testimonials', 3))->get(),
        ]);
    }

    /** @return Collection<int, Product> */
    private function featuredProducts(string $verticalSlug): Collection
    {
        return Product::visible()
            ->featured()
            ->whereRelation('vertical', 'slug', $verticalSlug)
            ->with(['vendor', 'category', 'certificates'])
            ->orderByDesc('rating_cache')
            ->take($this->limit($verticalSlug))
            ->get();
    }

    /** Clamped so a bad value in the admin cannot pull the whole catalogue. */
    private function limit(string $section, int $default = 4): int
    {
        return max(1, min(12, (int) setting("home.{$section}.limit", $default)));
    }

    /** @return array<int, array{value: string, label: string}> */
    private function heroStats(): array
    {
        return Cache::remember('home:stats', now()->addHour(), fn () => [
            ['value' => $this->round(Product::visible()->count()), 'label' => 'Products'],
            ['value' => $this->round(Vendor::approved()->count()), 'label' => 'Vendors'],
            ['value' => count(Countries::NAMES).'+', 'label' => 'Countries'],
        ]);
    }

    /** 1,248 → "1,200+", 96 → "96+" — headline figures, not exact counts. */
    private function round(int $value): string
    {
        return match (true) {
            $value >= 1000 => number_format(floor($value / 100) * 100).'+',
            $value >= 100 => (floor($value / 10) * 10).'+',
            default => $value.'+',
        };
    }
}
