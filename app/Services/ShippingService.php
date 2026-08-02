<?php

namespace App\Services;

use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\Vendor;

class ShippingService
{
    /**
     * Estimate shipping total in minor units for a vendor group and destination country.
     */
    public function estimateFreight(Vendor $vendor, string $countryCode, float $weightKg, int $subtotal): int
    {
        $zone = ShippingZone::where(function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)->orWhereNull('vendor_id');
        })->get()->first(function ($z) use ($countryCode) {
            return is_array($z->country_codes) && in_array(strtoupper($countryCode), array_map('strtoupper', $z->country_codes));
        });

        if ($zone) {
            $rate = ShippingRate::where('shipping_zone_id', $zone->id)
                ->where('min_weight_kg', '<=', $weightKg)
                ->where(function ($q) use ($weightKg) {
                    $q->whereNull('max_weight_kg')->orWhere('max_weight_kg', '>=', $weightKg);
                })->first();

            if ($rate) {
                $base = $rate->base_rate;
                $extra = (int) round(max(0, $weightKg - 1) * $rate->per_kg_rate);

                return $base + $extra;
            }
        }

        // Fallback default: 3% of subtotal or minimum $25 (2500 cents)
        return max(2500, (int) round($subtotal * 0.03));
    }
}
