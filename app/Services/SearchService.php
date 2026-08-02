<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchService
{
    /** Common B2B industry synonyms and term expansions. */
    public const SYNONYMS = [
        'api' => ['active pharmaceutical ingredient', 'raw material', 'bulk drug'],
        'pv' => ['solar panel', 'photovoltaic', 'solar module'],
        'mod' => ['module', 'panel'],
        'inverter' => ['solar inverter', 'grid tie inverter'],
        'formulation' => ['tablet', 'capsule', 'injectable', 'syrup'],
        'surgical' => ['medical disposable', 'surgical instrument'],
    ];

    /**
     * Expand search query with industry synonyms.
     */
    public function expandQuery(string $term): array
    {
        $term = strtolower(trim($term));
        $terms = [$term];

        if (isset(self::SYNONYMS[$term])) {
            $terms = array_merge($terms, self::SYNONYMS[$term]);
        }

        foreach (self::SYNONYMS as $key => $expansions) {
            if (in_array($term, $expansions, true)) {
                $terms[] = $key;
            }
        }

        return array_unique($terms);
    }

    /**
     * Apply search term with synonym expansion to an Eloquent Builder query.
     */
    public function applySearch(Builder $query, string $term): Builder
    {
        $terms = $this->expandQuery($term);

        return $query->where(function (Builder $search) use ($terms): void {
            foreach ($terms as $t) {
                $search->orWhere('name', 'like', "%{$t}%")
                    ->orWhere('sku', 'like', "%{$t}%")
                    ->orWhere('short_description', 'like', "%{$t}%")
                    ->orWhereRelation('vendor', 'name', 'like', "%{$t}%")
                    ->orWhereRelation('category', 'name', 'like', "%{$t}%");
            }
        });
    }

    /**
     * Get autocomplete suggestions for search bar.
     *
     * @return array{products: Collection, categories: Collection, vendors: Collection}
     */
    public function suggest(string $term, int $limit = 5): array
    {
        $term = trim($term);

        if ($term === '') {
            return [
                'products' => collect(),
                'categories' => collect(),
                'vendors' => collect(),
            ];
        }

        $terms = $this->expandQuery($term);

        $products = Product::query()
            ->visible()
            ->where(function ($q) use ($terms) {
                foreach ($terms as $t) {
                    $q->orWhere('name', 'like', "%{$t}%")->orWhere('sku', 'like', "%{$t}%");
                }
            })
            ->with(['category', 'vendor'])
            ->limit($limit)
            ->get();

        $categories = Category::query()
            ->where(function ($q) use ($terms) {
                foreach ($terms as $t) {
                    $q->orWhere('name', 'like', "%{$t}%");
                }
            })
            ->limit($limit)
            ->get(['id', 'name', 'slug']);

        $vendors = Vendor::query()
            ->approved()
            ->where(function ($q) use ($terms) {
                foreach ($terms as $t) {
                    $q->orWhere('name', 'like', "%{$t}%");
                }
            })
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'logo']);

        return [
            'products' => $products,
            'categories' => $categories,
            'vendors' => $vendors,
        ];
    }
}
