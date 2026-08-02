<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Checkout\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use App\Support\Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function create(Request $request): RedirectResponse|View
    {
        $groups = $this->cart->vendorGroups();

        if ($groups->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        $subtotal = (int) $groups->sum('subtotal');

        return view('storefront.checkout', [
            'groups' => $groups,
            'subtotal' => $subtotal,
            'shipping' => $this->shippingEstimate($groups),
            'countries' => Countries::NAMES,
            'address' => $request->user()->addresses()->where('is_default_shipping', true)->first()
                ?? $request->user()->addresses()->latest()->first(),
        ]);
    }

    public function store(Request $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $data = $request->validate([
            'contact_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postcode' => ['nullable', 'string', 'max:24'],
            'country_code' => ['required', 'string', 'size:2'],
            'phone' => ['required', 'string', 'max:32'],
            'tax_id' => ['nullable', 'string', 'max:40'],
            'incoterm' => ['required', 'in:EXW,FOB,CIF,DDP,DAP,CFR'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'save_address' => ['nullable', 'boolean'],
        ]);

        $cart = $this->cart->current(create: false);

        abort_if($cart === null || $cart->items()->doesntExist(), 419, 'Your cart is empty.');

        $address = collect($data)->only([
            'contact_name', 'company', 'line1', 'line2', 'city', 'state', 'postcode', 'country_code', 'phone', 'tax_id',
        ])->all();

        if ($request->boolean('save_address')) {
            $request->user()->addresses()->create($address + [
                'label' => 'Shipping',
                'is_default_shipping' => true,
            ]);
        }

        $order = $placeOrder->handle($request->user(), $cart, [
            'shipping_address' => $address,
            'incoterm' => $data['incoterm'],
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('payment.show', $order);
    }

    public function confirmation(Request $request, Order $order): View
    {
        abort_unless($request->user()->can('view', $order), 403);

        $order->load(['subOrders.vendor', 'subOrders.items']);

        return view('storefront.checkout-confirmation', ['order' => $order]);
    }

    /** Mirrors the estimate PlaceOrder freezes onto each sub-order. */
    private function shippingEstimate($groups): int
    {
        return (int) $groups->sum(fn (array $group) => max(
            PlaceOrder::SHIPPING_MINIMUM,
            (int) round($group['subtotal'] * PlaceOrder::SHIPPING_PERCENT / 100),
        ));
    }
}
