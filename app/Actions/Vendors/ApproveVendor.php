<?php

namespace App\Actions\Vendors;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Notifications\VendorApproved;
use Illuminate\Support\Facades\DB;

class ApproveVendor
{
    /**
     * Approves the vendor, marks its submitted paperwork as verified and lets
     * the owner know they can start listing.
     */
    public function handle(Vendor $vendor, User $approver, ?string $note = null): Vendor
    {
        DB::transaction(function () use ($vendor, $approver, $note): void {
            $vendor->update([
                'status' => Vendor::STATUS_APPROVED,
                'approved_at' => now(),
                'approved_by' => $approver->id,
                'rejection_reason' => null,
            ]);

            $vendor->documents()
                ->where('status', VendorDocument::STATUS_PENDING)
                ->update([
                    'status' => VendorDocument::STATUS_VERIFIED,
                    'reviewed_by' => $approver->id,
                ]);

            $vendor->kycLogs()->create([
                'actor_id' => $approver->id,
                'action' => 'approved',
                'note' => $note,
            ]);
        });

        $vendor->owner->notify(new VendorApproved($vendor));

        return $vendor->refresh();
    }
}
