<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->input('query'));
        $email = trim((string) $request->input('email'));

        $order = null;
        $shipments = collect();

        if ($query !== '') {
            $order = Order::where('reference', $query)
                ->when($email !== '', function ($q) use ($email) {
                    $q->whereHas('buyer', fn ($b) => $b->where('email', $email));
                })
                ->with(['subOrders.vendor', 'subOrders.shipments.events'])
                ->first();

            if (! $order) {
                $shipment = Shipment::where('tracking_no', $query)
                    ->orWhere('bl_awb_no', $query)
                    ->with(['subOrder.order', 'events'])
                    ->first();

                if ($shipment) {
                    $order = $shipment->subOrder?->order;
                    $shipments = collect([$shipment]);
                }
            } else {
                $shipments = $order->subOrders->flatMap->shipments;
            }
        }

        return view('storefront.track-order', [
            'query' => $query,
            'email' => $email,
            'order' => $order,
            'shipments' => $shipments,
        ]);
    }
}
