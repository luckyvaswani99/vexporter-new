<?php

namespace App\Actions\Vendors;

use App\Models\User;
use App\Models\Vendor;
use App\Notifications\VendorRejected;
use Illuminate\Support\Facades\DB;

class RejectVendor
{
    /** Rejection always carries a reason — the vendor can fix and resubmit. */
    public function handle(Vendor $vendor, User $reviewer, string $reason): Vendor
    {
        DB::transaction(function () use ($vendor, $reviewer, $reason): void {
            $vendor->update([
                'status' => Vendor::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            $vendor->kycLogs()->create([
                'actor_id' => $reviewer->id,
                'action' => 'rejected',
                'note' => $reason,
            ]);
        });

        $vendor->owner->notify(new VendorRejected($vendor, $reason));

        return $vendor->refresh();
    }
}
