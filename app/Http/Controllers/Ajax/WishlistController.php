<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        if (! $request->user()) {
            return response()->json([
                'message' => 'Sign in to save products to your wishlist.',
                'redirect' => route('login'),
            ], 401);
        }

        $existing = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $data['product_id'])
            ->first();

        $existing
            ? $existing->delete()
            : Wishlist::create(['user_id' => $request->user()->id, 'product_id' => $data['product_id']]);

        return response()->json([
            'saved' => ! $existing,
            'ids' => Wishlist::where('user_id', $request->user()->id)->pluck('product_id'),
        ]);
    }
}
