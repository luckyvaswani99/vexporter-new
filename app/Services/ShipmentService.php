<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\SubOrder;

class ShipmentService
{
    public function __construct(private EscrowService $escrowService) {}

    /**
     * Create a shipment for a vendor sub-order.
     *
     * @param  array<string, mixed>  $data
     */
    public function createShipment(SubOrder $subOrder, array $data): Shipment
    {
        $shipment = Shipment::create([
            'sub_order_id' => $subOrder->id,
            'carrier' => $data['carrier'] ?? 'DHL Express',
            'service' => $data['service'] ?? 'Air Freight',
            'tracking_no' => $data['tracking_no'] ?? 'TRK'.strtoupper(bin2hex(random_bytes(4))),
            'tracking_url' => $data['tracking_url'] ?? null,
            'status' => 'pending',
            'weight_kg' => $data['weight_kg'] ?? 10.0,
            'packages' => $data['packages'] ?? 1,
            'incoterm' => $data['incoterm'] ?? $subOrder->order->incoterm ?? 'CIF',
            'port_of_loading' => $data['port_of_loading'] ?? 'Mumbai, India',
            'port_of_discharge' => $data['port_of_discharge'] ?? null,
            'container_no' => $data['container_no'] ?? null,
            'bl_awb_no' => $data['bl_awb_no'] ?? null,
            'shipped_at' => now(),
        ]);

        $subOrder->update(['status' => Order::STATUS_SHIPPED]);

        $shipment->events()->create([
            'status' => 'pending',
            'location' => $data['port_of_loading'] ?? 'Warehouse',
            'description' => 'Shipment booked and dispatch pending.',
            'happened_at' => now(),
        ]);

        return $shipment;
    }

    /**
     * Update shipment status and log tracking event.
     */
    public function updateStatus(Shipment $shipment, string $status, ?string $location = null, ?string $description = null): void
    {
        $shipment->update([
            'status' => $status,
            'delivered_at' => $status === 'delivered' ? now() : $shipment->delivered_at,
        ]);

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'status' => $status,
            'location' => $location ?? 'In Transit',
            'description' => $description ?? "Shipment status updated to {$status}.",
            'happened_at' => now(),
        ]);

        $subOrder = $shipment->subOrder;

        if ($status === 'delivered') {
            $subOrder->update(['status' => Order::STATUS_DELIVERED]);
            $this->escrowService->release($subOrder);
        }
    }
}
