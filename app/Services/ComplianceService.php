<?php

namespace App\Services;

use App\Models\SubOrder;
use Illuminate\Validation\ValidationException;

class ComplianceService
{
    /**
     * Validate regulatory compliance before a vendor can fulfill / ship a sub-order.
     *
     * @throws ValidationException
     */
    public function validateFulfillment(SubOrder $subOrder): void
    {
        $vendor = $subOrder->vendor;

        // Check if sub-order contains Pharma items
        $hasPharma = $subOrder->items->contains(function ($item) {
            return $item->product && ($item->product->vertical?->slug === 'pharma' || str_contains(strtolower($item->product->category?->name ?? ''), 'pharma'));
        });

        if ($hasPharma) {
            // 1. Vendor Drug License / Cert Check
            $hasValidLicense = $vendor->documents()
                ->whereIn('type', ['drug_license', 'who_gmp', 'fda', 'eu_gmp'])
                ->exists();

            if (! $hasValidLicense) {
                throw ValidationException::withMessages([
                    'compliance' => 'Pharma fulfillment blocked: Vendor must have a verified Drug License or WHO-GMP/FDA certification.',
                ]);
            }

            // 2. Certificate of Analysis (COA) / Batch validation
            foreach ($subOrder->items as $item) {
                $hasCoa = $item->product?->documents()
                    ->where('type', 'coa')
                    ->exists();

                if (! $hasCoa && empty($item->batch_no)) {
                    throw ValidationException::withMessages([
                        'compliance' => "Item '{$item->name_snapshot}' requires a Certificate of Analysis (COA) or Batch Number before dispatch.",
                    ]);
                }
            }
        }

        // Check if sub-order contains Solar items
        $hasSolar = $subOrder->items->contains(function ($item) {
            return $item->product && ($item->product->vertical?->slug === 'solar' || str_contains(strtolower($item->product->category?->name ?? ''), 'solar'));
        });

        if ($hasSolar) {
            $hasSolarCert = $vendor->documents()
                ->whereIn('type', ['bis', 'mnre', 'almm', 'iec', 'ce'])
                ->exists();

            if (! $hasSolarCert) {
                throw ValidationException::withMessages([
                    'compliance' => 'Solar fulfillment warning: Vendor missing BIS/MNRE/ALMM certification record.',
                ]);
            }
        }
    }
}
