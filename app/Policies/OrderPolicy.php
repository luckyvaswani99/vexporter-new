<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Order $order): bool
    {
        if ($order->buyer_id === $user->id) {
            return true;
        }

        // A vendor may open an order only through the sub-order they fulfil.
        return $order->subOrders()
            ->whereHas('vendor', fn ($vendor) => $vendor->where('user_id', $user->id))
            ->exists();
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('orders.manage');
    }
}
