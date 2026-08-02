<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Request $request, Product $product): View
    {
        abort_unless($request->user()?->can('view', $product) ?? $product->vendor?->isApproved(), 404);
        abort_unless($product->is_active && $product->approval_status === Product::APPROVAL_APPROVED, 404);

        $product->load([
            'vendor.documents',
            'category.vertical',
            'certificates',
            'documents',
            'tierPrices',
            'variants',
            'attributeValues.attribute',
            'reviews' => fn ($query) => $query->approved()->with('user')->latest()->take(5),
        ]);

        $product->increment('views_count');

        return view('storefront.product', [
            'product' => $product,
            // Licensed goods stay quote-only until the buyer's licence is verified.
            'canSeePrice' => $this->canSeePrice($request, $product),
            'variantOptions' => $this->variantOptions($product),
            'related' => Product::visible()
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->with(['vendor', 'category', 'certificates'])
                ->orderByDesc('rating_cache')
                ->take(4)
                ->get(),
            'fromVendor' => Product::visible()
                ->where('vendor_id', $product->vendor_id)
                ->whereKeyNot($product->id)
                ->with(['vendor', 'category', 'certificates'])
                ->take(4)
                ->get(),
        ]);
    }

    /**
     * Only variable products get a picker — an option list on a simple product
     * would let the buyer send a variant id the cart is going to ignore.
     *
     * @return array<int, array{id: int, name: string, price_label: string, stock_qty: int, is_default: bool}>
     */
    private function variantOptions(Product $product): array
    {
        if ($product->type !== Product::TYPE_VARIABLE) {
            return [];
        }

        $default = $product->defaultVariant();

        return $product->variants
            ->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'price_label' => Money::format($product->priceForQty($product->moq, $variant), $product->currency),
                'stock_qty' => $variant->stock_qty,
                'is_default' => $variant->id === $default?->id,
            ])
            ->all();
    }

    private function canSeePrice(Request $request, Product $product): bool
    {
        if (! $product->requires_license) {
            return true;
        }

        return (bool) $request->user()?->buyerProfile?->canBuyRestrictedPharma();
    }
}
