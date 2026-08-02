<?php

namespace App\Policies;

use App\Models\SubOrder;
use App\Models\User;

class SubOrderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /** The buyer who placed it, or the vendor fulfilling it. */
    public function view(User $user, SubOrder $subOrder): bool
    {
        return $subOrder->order?->buyer_id === $user->id || $this->belongsToVendor($user, $subOrder);
    }

    public function update(User $user, SubOrder $subOrder): bool
    {
        return $this->belongsToVendor($user, $subOrder) && $user->can('orders.manage');
    }

    private function belongsToVendor(User $user, SubOrder $subOrder): bool
    {
        $vendor = $subOrder->vendor;

        return $vendor !== null
            && ($vendor->user_id === $user->id || $vendor->staff()->whereKey($user->id)->exists());
    }
}
