<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(private Request $request) {}

    /** The signed-in user's cart, or the guest cart bound to this session. */
    public function current(bool $create = true): ?Cart
    {
        $user = $this->request->user();
        $sessionId = $this->request->session()->getId();

        $cart = $user
            ? Cart::firstWhere('user_id', $user->id)
            : Cart::whereNull('user_id')->firstWhere('session_id', $sessionId);

        if (! $cart && $user) {
            // Carry a guest cart over after login instead of dropping it.
            $cart = Cart::whereNull('user_id')->firstWhere('session_id', $sessionId);
            $cart?->update(['user_id' => $user->id]);
        }

        if (! $cart && $create) {
            $cart = Cart::create([
                'user_id' => $user?->id,
                'session_id' => $sessionId,
                'currency' => config('vexporter.default_currency'),
            ]);
        }

        return $cart;
    }

    public function add(Product $product, int $qty, ?int $variantId = null): CartItem
    {
        if ($product->requiresQuote()) {
            throw ValidationException::withMessages([
                'product' => 'This item is sold on quotation — please request a quote.',
            ]);
        }

        $variant = $this->resolveVariant($product, $variantId);

        $qty = $this->normaliseQty($product, $qty);
        $cart = $this->current();

        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
        ]);

        $item->qty = $this->normaliseQty($product, ($item->exists ? $item->qty : 0) + $qty);
        $item->vendor_id = $product->vendor_id;
        $item->unit = $product->unit;
        $item->unit_price = $product->priceForQty($item->qty, $variant);
        $item->snapshot = [
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $variant?->sku ?: $product->sku,
            'variant' => $variant?->name,
            'vendor' => $product->vendor?->name,
            'vendor_slug' => $product->vendor?->slug,
            'icon' => $product->icon,
            'icon_color' => $product->icon_color,
            'image_gradient' => $product->image_gradient,
        ];
        $item->save();

        return $item;
    }

    public function update(CartItem $item, int $qty): CartItem
    {
        $product = $item->product;

        $item->qty = $this->normaliseQty($product, $qty);
        $item->unit_price = $product->priceForQty($item->qty, $item->variant);
        $item->save();

        return $item;
    }

    /**
     * A variable product must be added as one of its options; anything else
     * ignores the variant, so a stale id cannot mis-price a line.
     */
    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if ($product->type !== Product::TYPE_VARIABLE) {
            return null;
        }

        $variant = $variantId === null
            ? null
            : $product->variants->firstWhere('id', $variantId);

        if (! $variant) {
            throw ValidationException::withMessages([
                'variant_id' => 'Choose an option before adding this product to the cart.',
            ]);
        }

        return $variant;
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(): void
    {
        $this->current(create: false)?->items()->delete();
    }

    /** Items grouped per vendor — the storefront and checkout both need this. */
    public function vendorGroups()
    {
        $cart = $this->current(create: false);

        if (! $cart) {
            return collect();
        }

        return $cart->items()
            ->with(['product.vendor', 'vendor'])
            ->get()
            ->groupBy('vendor_id')
            ->map(fn ($items) => [
                'vendor' => $items->first()->vendor,
                'items' => $items,
                'subtotal' => $items->sum(fn (CartItem $item) => $item->unit_price * $item->qty),
            ])
            ->values();
    }

    /** JSON payload the Alpine cart store keeps in sync. */
    public function payload(): array
    {
        $cart = $this->current(create: false);

        if (! $cart) {
            return ['count' => 0, 'total' => Money::format(0), 'subtotal' => 0, 'items' => []];
        }

        $items = $cart->items()->with('product')->get();
        $subtotal = (int) $items->sum(fn (CartItem $item) => $item->unit_price * $item->qty);

        return [
            'count' => (int) $items->sum('qty'),
            'subtotal' => $subtotal,
            'total' => Money::format($subtotal, $cart->currency),
            'items' => $items->map(fn (CartItem $item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->snapshot['name'] ?? $item->product?->name,
                'qty' => $item->qty,
                'unit' => $item->unit,
                'unit_price' => Money::format($item->unit_price, $cart->currency),
                'line_total' => Money::format($item->unit_price * $item->qty, $cart->currency),
                'url' => route('products.show', $item->snapshot['slug'] ?? $item->product?->slug),
            ])->all(),
        ];
    }

    /** Respects MOQ and the vendor's order increment. */
    private function normaliseQty(Product $product, int $qty): int
    {
        $moq = max(1, (int) $product->moq);
        $step = max(1, (int) $product->order_increment);

        $qty = max($moq, $qty);

        if (($qty - $moq) % $step !== 0) {
            $qty = $moq + (int) (ceil(($qty - $moq) / $step) * $step);
        }

        return $qty;
    }
}
