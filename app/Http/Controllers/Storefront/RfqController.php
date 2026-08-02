<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Rfq\AcceptQuote;
use App\Actions\Rfq\SubmitRfq;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Rfq;
use App\Models\Vertical;
use App\Support\Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RfqController extends Controller
{
    public function create(Request $request): View
    {
        $product = $request->query('product')
            ? Product::visible()->with('vendor', 'category')->where('slug', $request->query('product'))->first()
            : null;

        return view('storefront.rfq', [
            'product' => $product,
            'verticals' => Vertical::orderBy('sort_order')->get(),
            'countries' => Countries::NAMES,
        ]);
    }

    public function store(Request $request, SubmitRfq $submit): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'vertical_id' => ['nullable', 'integer', 'exists:verticals,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'qty' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string', 'max:24'],
            'target_price' => ['nullable', 'numeric', 'min:0'],
            'destination_country' => ['required', 'string', 'size:2'],
            'incoterm' => ['required', 'in:EXW,FOB,CIF,DDP,DAP,CFR'],
            'delivery_by' => ['nullable', 'date', 'after:today'],
        ]);

        $rfq = $submit->handle($request->user(), $data);

        return redirect()
            ->route('account.rfqs.show', $rfq)
            ->with('status', 'Quote request sent — matching vendors have been notified.');
    }

    public function show(Request $request, Rfq $rfq): View
    {
        abort_unless($rfq->buyer_id === $request->user()->id, 403);

        $rfq->load(['product', 'vendors', 'quotes.vendor', 'quotes.items']);

        return view('storefront.rfq-show', ['rfq' => $rfq]);
    }

    /** Buyer accepts one quote; it becomes a confirmed order with that vendor. */
    public function acceptQuote(Request $request, Rfq $rfq, Quote $quote, AcceptQuote $accept): RedirectResponse
    {
        abort_unless($rfq->buyer_id === $request->user()->id, 403);
        abort_unless($quote->rfq_id === $rfq->id, 404);

        $address = $request->user()->addresses()
            ->orderByDesc('is_default_shipping')
            ->latest()
            ->first();

        $order = $accept->handle($quote, $address ? collect($address->toArray())->only([
            'contact_name', 'company', 'line1', 'line2', 'city', 'state', 'postcode', 'country_code', 'phone', 'tax_id',
        ])->all() : []);

        return redirect()
            ->route('account.orders.show', $order)
            ->with('status', "Quote accepted — order {$order->reference} created.");
    }
}
