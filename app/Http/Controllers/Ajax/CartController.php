<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function show(): JsonResponse
    {
        return response()->json($this->cart->payload());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        $product = Product::visible()->findOrFail($data['product_id']);

        $this->cart->add($product, (int) ($data['qty'] ?? $product->moq), $data['variant_id'] ?? null);

        return response()->json($this->cart->payload());
    }

    public function update(Request $request, CartItem $item): JsonResponse
    {
        $this->authorizeItem($item);

        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $this->cart->update($item, (int) $data['qty']);

        return response()->json($this->cart->payload());
    }

    public function destroy(CartItem $item): JsonResponse
    {
        $this->authorizeItem($item);

        $this->cart->remove($item);

        return response()->json($this->cart->payload());
    }

    /** Items may only be touched through the cart they belong to. */
    private function authorizeItem(CartItem $item): void
    {
        abort_unless($item->cart_id === $this->cart->current(create: false)?->id, 403);
    }
}
