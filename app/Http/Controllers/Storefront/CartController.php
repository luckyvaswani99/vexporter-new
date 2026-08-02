<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(CartService $cart): View
    {
        $groups = $cart->vendorGroups();

        return view('storefront.cart', [
            'groups' => $groups,
            'subtotal' => (int) $groups->sum('subtotal'),
            'currency' => $cart->current(create: false)?->currency ?? config('vexporter.default_currency'),
        ]);
    }
}
