<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    /** Admins bypass every check below. */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('vendors.view');
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $this->belongsTo($user, $vendor) || $user->can('vendors.view');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $this->belongsTo($user, $vendor);
    }

    public function approve(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.approve');
    }

    public function manageStaff(User $user, Vendor $vendor): bool
    {
        return $vendor->user_id === $user->id;
    }

    public function viewPayouts(User $user, Vendor $vendor): bool
    {
        return $this->belongsTo($user, $vendor) && $user->can('payouts.view');
    }

    /** Owner or an invited staff member of that vendor. */
    private function belongsTo(User $user, Vendor $vendor): bool
    {
        return $vendor->user_id === $user->id
            || $vendor->staff()->whereKey($user->id)->exists();
    }
}
