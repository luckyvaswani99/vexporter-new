<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;

/**
 * Feeds the "Trade Analytics" card on the homepage with real platform numbers.
 * Cached because it runs on every homepage hit.
 */
class HomeAnalytics
{
    /** @return array{metrics: array<int, array<string, string>>, recent_orders: array<int, array<string, mixed>>} */
    public function build(): array
    {
        $now = now();
        $currentFrom = $now->copy()->subDays(30);
        $previousFrom = $now->copy()->subDays(60);

        $current = $this->window($currentFrom, $now);
        $previous = $this->window($previousFrom, $currentFrom);

        return [
            'metrics' => [
                [
                    'label' => 'Monthly GMV',
                    'value' => $this->compact($current['gmv']),
                    'delta' => $this->delta($current['gmv'], $previous['gmv']),
                ],
                [
                    'label' => 'Orders Shipped',
                    'value' => number_format($current['orders']),
                    'delta' => $this->delta($current['orders'], $previous['orders']),
                ],
                [
                    'label' => 'Active Buyers',
                    'value' => number_format($current['buyers']),
                    'delta' => $this->delta($current['buyers'], $previous['buyers']),
                ],
                [
                    'label' => 'Countries',
                    'value' => number_format(count(Countries::NAMES)),
                    'delta' => 'trade corridors served',
                ],
            ],
            'recent_orders' => $this->recentOrders(),
        ];
    }

    /** @return array{gmv: int, orders: int, buyers: int} */
    private function window(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $orders = Order::whereBetween('placed_at', [$from, $to]);

        return [
            'gmv' => (int) (clone $orders)->sum('grand_total'),
            'orders' => (clone $orders)->count(),
            'buyers' => (clone $orders)->distinct()->count('buyer_id'),
        ];
    }

    private function recentOrders(): array
    {
        return OrderItem::with('subOrder.order')
            ->latest('created_at')
            ->take(3)
            ->get()
            ->map(fn (OrderItem $item) => [
                'label' => str($item->name_snapshot)->limit(28)->toString().' - '.number_format($item->qty).' '.$item->unit,
                'amount' => Money::format($item->total, $item->subOrder?->order?->currency ?? 'USD'),
                'settled' => $item->subOrder?->order?->payment_status === Order::PAYMENT_RELEASED,
            ])
            ->all();
    }

    /** $24.8M / $845K style figures. */
    private function compact(int $minor): string
    {
        $major = $minor / 100;

        return match (true) {
            $major >= 1_000_000 => '$'.round($major / 1_000_000, 1).'M',
            $major >= 1_000 => '$'.round($major / 1_000).'K',
            default => '$'.number_format($major),
        };
    }

    private function delta(int|float $current, int|float $previous): string
    {
        if ($previous <= 0) {
            return $current > 0 ? 'new this month' : 'no change';
        }

        $change = round((($current - $previous) / $previous) * 100);

        return abs($change).'% vs last month';
    }
}
