<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DocumentGenerator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(private DocumentGenerator $documentGenerator) {}

    public function show(Request $request, Order $order, string $type): View
    {
        abort_unless($request->user()->can('view', $order) || $request->user()->type === 'admin', 403);

        $order->load(['subOrders.vendor', 'subOrders.items', 'buyer']);

        return match ($type) {
            'commercial-invoice' => $this->showCommercialInvoice($order),
            'packing-list' => $this->showPackingList($order, $request->input('sub_order_id')),
            'certificate-of-origin' => $this->showCertificateOfOrigin($order, $request->input('sub_order_id')),
            default => abort(404, 'Document type not found.'),
        };
    }

    private function showCommercialInvoice(Order $order): View
    {
        $this->documentGenerator->generateCommercialInvoice($order);

        return view('pdf.commercial-invoice', ['order' => $order]);
    }

    private function showPackingList(Order $order, ?string $subOrderId): View
    {
        $subOrder = $subOrderId
            ? $order->subOrders()->where('id', $subOrderId)->firstOrFail()
            : $order->subOrders->first();

        abort_unless($subOrder, 404);
        $this->documentGenerator->generatePackingList($subOrder);

        return view('pdf.packing-list', ['order' => $order, 'subOrder' => $subOrder]);
    }

    private function showCertificateOfOrigin(Order $order, ?string $subOrderId): View
    {
        $subOrder = $subOrderId
            ? $order->subOrders()->where('id', $subOrderId)->firstOrFail()
            : $order->subOrders->first();

        abort_unless($subOrder, 404);
        $this->documentGenerator->generateCertificateOfOrigin($subOrder);

        return view('pdf.certificate-of-origin', ['order' => $order, 'subOrder' => $subOrder]);
    }
}
