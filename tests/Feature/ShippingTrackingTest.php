<?php

use App\Models\Order;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\SubOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ShipmentService;
use App\Services\ShippingService;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('calculates shipping rates based on shipping zones or defaults', function () {
    $vendor = Vendor::factory()->create();
    $zone = ShippingZone::create([
        'vendor_id' => $vendor->id,
        'name' => 'Middle East',
        'country_codes' => ['AE', 'SA', 'QA'],
    ]);

    ShippingRate::create([
        'shipping_zone_id' => $zone->id,
        'name' => 'Express Cargo',
        'min_weight_kg' => 0,
        'max_weight_kg' => 50,
        'base_rate' => 3000,
        'per_kg_rate' => 200,
    ]);

    $shippingService = app(ShippingService::class);

    // Calculated zone rate: 3000 + 4 * 200 = 3800
    $rateAE = $shippingService->estimateFreight($vendor, 'AE', 5.0, 100000);
    expect($rateAE)->toBe(3800);

    // Fallback default: max(2500, 3% of 100,000) = 3000
    $rateUS = $shippingService->estimateFreight($vendor, 'US', 5.0, 100000);
    expect($rateUS)->toBe(3000);
});

it('creates shipments, logs milestone events, and provides public tracking', function () {
    $buyer = User::factory()->create(['email' => 'buyer@example.com']);
    $vendor = Vendor::factory()->create();

    $order = Order::create([
        'reference' => 'VX-2026-TRK001',
        'buyer_id' => $buyer->id,
        'status' => Order::STATUS_CONFIRMED,
        'payment_status' => Order::PAYMENT_ESCROW_HELD,
        'currency' => 'USD',
        'subtotal' => 100000,
        'shipping_total' => 3000,
        'grand_total' => 103000,
    ]);

    $subOrder = SubOrder::create([
        'order_id' => $order->id,
        'vendor_id' => $vendor->id,
        'reference' => 'VX-2026-TRK001-A',
        'status' => Order::STATUS_CONFIRMED,
        'subtotal' => 100000,
        'total' => 103000,
        'vendor_payout_amount' => 97000,
    ]);

    $shipmentService = app(ShipmentService::class);

    $shipment = $shipmentService->createShipment($subOrder, [
        'carrier' => 'DHL Express',
        'tracking_no' => 'DHL99887766',
        'port_of_loading' => 'Mumbai Port',
    ]);

    expect($shipment->tracking_no)->toBe('DHL99887766')
        ->and($subOrder->fresh()->status)->toBe(Order::STATUS_SHIPPED);

    // Update milestone
    $shipmentService->updateStatus($shipment, 'in_transit', 'Dubai Hub', 'Cargo in transit.');

    // Public tracking search by order reference
    $this->get(route('track-order', ['query' => 'VX-2026-TRK001']))
        ->assertOk()
        ->assertSee('VX-2026-TRK001')
        ->assertSee('DHL99887766')
        ->assertSee('Dubai Hub');
});
