<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuggestController extends Controller
{
    public function __construct(private SearchService $searchService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));

        if (mb_strlen($term) < 2) {
            return response()->json(['products' => [], 'categories' => [], 'vendors' => []]);
        }

        $results = $this->searchService->suggest($term, 5);

        return response()->json([
            'products' => $results['products']->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'vendor' => $product->vendor?->name,
                'price' => $product->requires_license ? 'On request' : $product->price_label,
                'url' => route('products.show', $product),
            ]),

            'categories' => $results['categories']->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'url' => route('categories.show', $category),
            ]),

            'vendors' => $results['vendors']->map(fn (Vendor $vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'url' => route('vendors.show', $vendor),
            ]),
        ]);
    }
}
