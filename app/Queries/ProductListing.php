<?php

namespace App\Queries;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Vertical;
use App\Services\SearchService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The single place listing pages (vertical, category, search, deals, new
 * arrivals) build their query, facets and sorting from.
 */
class ProductListing
{
    public const SORTS = [
        'relevance' => 'Relevance',
        'newest' => 'Newest first',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
        'rating' => 'Top rated',
        'moq' => 'Lowest MOQ',
    ];

    public const PER_PAGE = [24, 48, 96];

    public function __construct(
        private Request $request,
        private ?Vertical $vertical = null,
        private ?Category $category = null,
    ) {}

    public static function for(Request $request, ?Vertical $vertical = null, ?Category $category = null): self
    {
        return new self($request, $vertical, $category);
    }

    public function paginate(): LengthAwarePaginator
    {
        $perPage = in_array((int) $this->request->query('per_page'), self::PER_PAGE, true)
            ? (int) $this->request->query('per_page')
            : self::PER_PAGE[0];

        return $this->sorted($this->filtered())
            ->with(['vendor', 'category', 'certificates'])
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Facet counts are computed before pagination but after the base scope. */
    public function facets(): array
    {
        $base = $this->baseQuery();

        return [
            'vendors' => $this->facetVendors($base),
            'categories' => $this->facetCategories($base),
            'certifications' => $this->facetCertifications($base),
            'price' => $this->priceRange($base),
            'total' => (clone $base)->count(),
        ];
    }

    /** The active filters, for rendering removable chips. */
    public function activeFilters(): Collection
    {
        return collect([
            'q' => $this->request->query('q'),
            'vendor' => $this->request->query('vendor'),
            'category' => $this->request->query('category'),
            'certification' => $this->request->query('certification'),
            'min_price' => $this->request->query('min_price'),
            'max_price' => $this->request->query('max_price'),
            'rating' => $this->request->query('rating'),
            'in_stock' => $this->request->boolean('in_stock') ?: null,
        ])->filter();
    }

    private function baseQuery(): Builder
    {
        $query = Product::query()->visible();

        if ($this->vertical) {
            $query->where('vertical_id', $this->vertical->id);
        }

        if ($this->category) {
            $query->where('category_id', $this->category->id);
        }

        if ($vertical = $this->request->query('vertical')) {
            $query->whereRelation('vertical', 'slug', $vertical);
        }

        if ($term = trim((string) $this->request->query('q'))) {
            app(SearchService::class)->applySearch($query, $term);
        }

        return $query;
    }

    private function filtered(): Builder
    {
        $query = $this->baseQuery();

        if ($slugs = array_filter((array) $this->request->query('vendor', []))) {
            $query->whereHas('vendor', fn (Builder $vendor) => $vendor->whereIn('slug', $slugs));
        }

        if ($slugs = array_filter((array) $this->request->query('category', []))) {
            $query->whereHas('category', fn (Builder $category) => $category->whereIn('slug', $slugs));
        }

        if ($certifications = array_filter((array) $this->request->query('certification', []))) {
            $query->whereHas('certificates', fn (Builder $cert) => $cert->whereIn('type', $certifications));
        }

        if ($min = $this->request->query('min_price')) {
            $query->where('base_price', '>=', (int) round((float) $min * 100));
        }

        if ($max = $this->request->query('max_price')) {
            $query->where('base_price', '<=', (int) round((float) $max * 100));
        }

        if ($rating = (float) $this->request->query('rating')) {
            $query->where('rating_cache', '>=', $rating);
        }

        if ($this->request->boolean('in_stock')) {
            $query->where('stock_qty', '>', 0);
        }

        if ($this->request->boolean('deals')) {
            $query->whereNotNull('compare_at_price');
        }

        return $query;
    }

    private function sorted(Builder $query): Builder
    {
        return match ($this->request->query('sort')) {
            'newest' => $query->latest('published_at'),
            'price_asc' => $query->orderBy('base_price'),
            'price_desc' => $query->orderByDesc('base_price'),
            'rating' => $query->orderByDesc('rating_cache'),
            'moq' => $query->orderBy('moq'),
            default => $query->orderByDesc('is_featured')
                ->orderByDesc('is_bestseller')
                ->orderByDesc('rating_cache'),
        };
    }

    private function facetVendors(Builder $base): Collection
    {
        $counts = (clone $base)
            ->selectRaw('vendor_id, count(*) as total')
            ->groupBy('vendor_id')
            ->pluck('total', 'vendor_id');

        return Vendor::whereIn('id', $counts->keys())
            ->orderByDesc('rating_cache')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Vendor $vendor) => [
                'slug' => $vendor->slug,
                'name' => $vendor->name,
                'count' => (int) $counts[$vendor->id],
            ]);
    }

    private function facetCategories(Builder $base): Collection
    {
        $counts = (clone $base)
            ->selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        return Category::whereIn('id', $counts->keys())
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category) => [
                'slug' => $category->slug,
                'name' => $category->name,
                'count' => (int) $counts[$category->id],
            ]);
    }

    private function facetCertifications(Builder $base): Collection
    {
        $productIds = (clone $base)->select('products.id');

        // Plain query builder — this is an aggregate, not a set of models.
        return DB::table('product_certificates')
            ->whereIn('product_id', $productIds->toBase())
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $row) => ['type' => $row->type, 'count' => (int) $row->total]);
    }

    private function priceRange(Builder $base): array
    {
        $range = (clone $base)
            ->selectRaw('min(base_price) as min_price, max(base_price) as max_price')
            ->first();

        return [
            'min' => (int) floor(($range->min_price ?? 0) / 100),
            'max' => (int) ceil(($range->max_price ?? 0) / 100),
        ];
    }
}
