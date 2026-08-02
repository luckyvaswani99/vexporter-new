<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarketplaceOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Marketplace at a glance';

    protected function getStats(): array
    {
        $thisMonth = Order::where('placed_at', '>=', now()->subDays(30));
        $lastMonth = Order::whereBetween('placed_at', [now()->subDays(60), now()->subDays(30)]);

        $gmv = (int) (clone $thisMonth)->sum('grand_total');
        $previousGmv = (int) (clone $lastMonth)->sum('grand_total');
        $commission = (int) (clone $thisMonth)->sum('commission_total');
        $pendingVendors = Vendor::where('status', Vendor::STATUS_PENDING)->count();

        return [
            Stat::make('GMV (30 days)', Money::format($gmv, 'USD'))
                ->description($this->delta($gmv, $previousGmv))
                ->descriptionIcon($gmv >= $previousGmv ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($gmv >= $previousGmv ? 'success' : 'danger')
                ->chart($this->gmvTrend()),

            Stat::make('Commission earned', Money::format($commission, 'USD'))
                ->description(config('vexporter.commission_percent').'% platform fee')
                ->color('primary'),

            Stat::make('Orders (30 days)', number_format((clone $thisMonth)->count()))
                ->description(number_format(Order::count()).' all time')
                ->color('info'),

            Stat::make('Approved vendors', number_format(Vendor::approved()->count()))
                ->description(number_format($pendingVendors).' awaiting review')
                ->color($pendingVendors > 0 ? 'warning' : 'success'),

            Stat::make('Live products', number_format(Product::visible()->count()))
                ->description(number_format(Product::where('approval_status', Product::APPROVAL_PENDING)->count()).' pending approval')
                ->color('gray'),

            Stat::make('Open RFQs', number_format(Rfq::where('status', Rfq::STATUS_OPEN)->count()))
                ->description('Buyers waiting on quotes')
                ->color('warning'),
        ];
    }

    /** Daily GMV for the sparkline, oldest first. */
    private function gmvTrend(): array
    {
        return collect(range(13, 0))
            ->map(fn (int $daysAgo) => (int) (Order::whereBetween('placed_at', [
                now()->subDays($daysAgo)->startOfDay(),
                now()->subDays($daysAgo)->endOfDay(),
            ])->sum('grand_total') / 100))
            ->all();
    }

    private function delta(int $current, int $previous): string
    {
        if ($previous <= 0) {
            return $current > 0 ? 'First revenue this period' : 'No revenue yet';
        }

        $change = round((($current - $previous) / $previous) * 100);

        return abs($change).'% vs previous 30 days';
    }
}
