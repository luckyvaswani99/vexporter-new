<?php

namespace App\Services;

use App\Models\ExportDocument;
use App\Models\Order;
use App\Models\SubOrder;

class DocumentGenerator
{
    /**
     * Generate or fetch Commercial Invoice export document for an order.
     */
    public function generateCommercialInvoice(Order $order): ExportDocument
    {
        $docNumber = 'EXP-INV-'.$order->reference;

        return ExportDocument::firstOrCreate(
            ['order_id' => $order->id, 'type' => ExportDocument::TYPE_COMMERCIAL_INVOICE],
            [
                'number' => $docNumber,
                'issued_by' => auth()->id() ?? $order->buyer_id,
                'issued_at' => now(),
            ]
        );
    }

    /**
     * Generate or fetch Packing List for a sub-order.
     */
    public function generatePackingList(SubOrder $subOrder): ExportDocument
    {
        $docNumber = 'EXP-PKL-'.$subOrder->reference;

        return ExportDocument::firstOrCreate(
            ['sub_order_id' => $subOrder->id, 'type' => ExportDocument::TYPE_PACKING_LIST],
            [
                'order_id' => $subOrder->order_id,
                'number' => $docNumber,
                'issued_by' => auth()->id() ?? $subOrder->order->buyer_id,
                'issued_at' => now(),
            ]
        );
    }

    /**
     * Generate or fetch Certificate of Origin for a sub-order.
     */
    public function generateCertificateOfOrigin(SubOrder $subOrder): ExportDocument
    {
        $docNumber = 'EXP-COO-'.$subOrder->reference;

        return ExportDocument::firstOrCreate(
            ['sub_order_id' => $subOrder->id, 'type' => ExportDocument::TYPE_CERTIFICATE_OF_ORIGIN],
            [
                'order_id' => $subOrder->order_id,
                'number' => $docNumber,
                'issued_by' => auth()->id() ?? $subOrder->order->buyer_id,
                'issued_at' => now(),
            ]
        );
    }
}
