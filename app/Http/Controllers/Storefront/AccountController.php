<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        return view('account.dashboard', [
            'user' => $user,
            'vendor' => $user->vendor()->first(),
            'orders' => $user->orders()->with('subOrders')->latest('placed_at')->take(5)->get(),
            'stats' => [
                'orders' => $user->orders()->count(),
                'open_rfqs' => $user->rfqs()->where('status', 'open')->count(),
                'wishlist' => $user->wishlist()->count(),
            ],
        ]);
    }

    public function orders(Request $request): View
    {
        return view('account.orders', [
            'orders' => $request->user()
                ->orders()
                ->with(['subOrders.vendor'])
                ->latest('placed_at')
                ->paginate(10),
        ]);
    }

    public function order(Request $request, Order $order): View
    {
        abort_unless($request->user()->can('view', $order), 403);

        $order->load(['subOrders.vendor', 'subOrders.items', 'subOrders.shipments', 'subOrders.statusHistory']);

        return view('account.order', ['order' => $order]);
    }

    public function wishlist(Request $request): View
    {
        return view('account.wishlist', [
            'products' => Wishlist::where('user_id', $request->user()->id)
                ->with(['product.vendor', 'product.category', 'product.certificates'])
                ->latest()
                ->get()
                ->pluck('product')
                ->filter(),
        ]);
    }

    public function rfqs(Request $request): View
    {
        return view('account.rfqs', [
            'rfqs' => $request->user()
                ->rfqs()
                ->withCount(['quotes', 'vendors'])
                ->latest()
                ->paginate(10),
        ]);
    }
}
