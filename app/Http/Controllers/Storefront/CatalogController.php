<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Vertical;
use App\Queries\ProductListing;
use App\Support\Html;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function vertical(Request $request, Vertical $vertical): View
    {
        $listing = ProductListing::for($request, vertical: $vertical);

        return $this->render($request, $listing, [
            'title' => $vertical->name,
            'subtitle' => $vertical->tagline,
            'vertical' => $vertical,
            'breadcrumbs' => [['label' => $vertical->name]],
        ]);
    }

    public function category(Request $request, Category $category): View
    {
        $category->load('vertical');
        $listing = ProductListing::for($request, category: $category);

        return $this->render($request, $listing, [
            'title' => $category->name,
            // The description is rich text: flattened for the meta tag,
            // rendered as markup above the grid.
            'subtitle' => Html::toText($category->description, 160) ?: null,
            'intro' => $category->description,
            'vertical' => $category->vertical,
            'breadcrumbs' => [
                ['label' => $category->vertical->name, 'url' => route('verticals.show', $category->vertical)],
                ['label' => $category->name],
            ],
        ]);
    }

    public function search(Request $request): View
    {
        $term = trim((string) $request->query('q'));
        $listing = ProductListing::for($request);

        return $this->render($request, $listing, [
            'title' => $term !== '' ? "Results for “{$term}”" : 'All products',
            'subtitle' => null,
            'breadcrumbs' => [['label' => 'Search']],
        ]);
    }

    public function deals(Request $request): View
    {
        $request->merge(['deals' => true]);
        $listing = ProductListing::for($request);

        return $this->render($request, $listing, [
            'title' => 'Hot deals',
            'subtitle' => 'Discounted bulk pricing from verified manufacturers, updated daily.',
            'breadcrumbs' => [['label' => 'Hot deals']],
        ]);
    }

    public function newArrivals(Request $request): View
    {
        $request->merge(['sort' => 'newest']);
        $listing = ProductListing::for($request);

        return $this->render($request, $listing, [
            'title' => 'New arrivals',
            'subtitle' => 'The latest listings across pharma, solar and general trade.',
            'breadcrumbs' => [['label' => 'New arrivals']],
        ]);
    }

    /** Alpine swaps just the grid; a full request renders the whole page. */
    private function render(Request $request, ProductListing $listing, array $data): View
    {
        $payload = array_merge($data, [
            'products' => $listing->paginate(),
            'facets' => $listing->facets(),
            'activeFilters' => $listing->activeFilters(),
            'sorts' => ProductListing::SORTS,
            'perPageOptions' => ProductListing::PER_PAGE,
        ]);

        if ($request->boolean('partial')) {
            return view('storefront.partials.product-grid', $payload);
        }

        return view('storefront.listing', $payload);
    }
}
