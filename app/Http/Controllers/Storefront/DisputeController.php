<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\SubOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function store(Request $request, Order $order, SubOrder $subOrder): RedirectResponse
    {
        abort_unless($request->user()->can('view', $order), 403);
        abort_unless($subOrder->order_id === $order->id, 404);

        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $reference = 'DSP-'.now()->format('Y').'-'.strtoupper(bin2hex(random_bytes(3)));

        $dispute = Dispute::create([
            'reference' => $reference,
            'order_id' => $order->id,
            'sub_order_id' => $subOrder->id,
            'buyer_id' => $request->user()->id,
            'vendor_id' => $subOrder->vendor_id,
            'reason' => $request->input('reason'),
            'description' => $request->input('description'),
            'status' => Dispute::STATUS_OPEN,
            'refund_amount' => $subOrder->total,
        ]);

        // Freeze escrow payout release for this sub-order while dispute is open
        $subOrder->update(['payout_status' => 'disputed']);

        $dispute->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $request->input('description'),
        ]);

        return redirect()->route('account.orders.show', $order)->with('status', "Dispute {$reference} opened. Escrow funds placed on hold for arbitration.");
    }

    public function show(Request $request, Dispute $dispute): View
    {
        abort_unless(
            $request->user()->id === $dispute->buyer_id ||
            $request->user()->type === 'admin' ||
            $request->user()->vendors->pluck('id')->contains($dispute->vendor_id),
            403
        );

        $dispute->load(['order', 'subOrder.vendor', 'messages.user']);

        return view('storefront.dispute', ['dispute' => $dispute]);
    }

    public function reply(Request $request, Dispute $dispute): RedirectResponse
    {
        abort_unless(
            $request->user()->id === $dispute->buyer_id ||
            $request->user()->type === 'admin' ||
            $request->user()->vendors->pluck('id')->contains($dispute->vendor_id),
            403
        );

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $dispute->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $request->input('message'),
        ]);

        return back()->with('status', 'Message added to dispute thread.');
    }
}
