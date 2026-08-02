<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SubOrder;

class EscrowService
{
    /**
     * Mark funds as held in escrow upon payment capture.
     */
    public function hold(Order $order): void
    {
        $order->update([
            'payment_status' => Order::PAYMENT_ESCROW_HELD,
            'status' => $order->status === Order::STATUS_PENDING ? Order::STATUS_CONFIRMED : $order->status,
        ]);

        foreach ($order->subOrders as $subOrder) {
            $subOrder->update([
                'status' => $subOrder->status === Order::STATUS_PENDING ? Order::STATUS_CONFIRMED : $subOrder->status,
                'payout_status' => SubOrder::PAYOUT_PENDING,
            ]);
        }
    }

    /**
     * Release escrow for a specific sub-order.
     */
    public function release(SubOrder $subOrder): void
    {
        $subOrder->update([
            'escrow_released_at' => now(),
            'payout_status' => SubOrder::PAYOUT_ELIGIBLE,
        ]);

        $order = $subOrder->order;

        // The parent order is only "released" once every vendor leg is.
        if ($order->subOrders()->whereNull('escrow_released_at')->doesntExist()) {
            $order->update(['payment_status' => Order::PAYMENT_RELEASED]);
        }
    }

    /**
     * Auto-release escrow for sub-orders delivered past retention period (e.g. 7 days).
     */
    public function autoReleaseEligibleSubOrders(int $retentionDays = 7): int
    {
        $cutoff = now()->subDays($retentionDays);

        $eligibleSubOrders = SubOrder::where('status', Order::STATUS_DELIVERED)
            ->whereNull('escrow_released_at')
            ->where('updated_at', '<=', $cutoff)
            ->get();

        $count = 0;
        foreach ($eligibleSubOrders as $subOrder) {
            $this->release($subOrder);
            $count++;
        }

        return $count;
    }
}
