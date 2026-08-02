<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\SubOrder;
use App\Models\Vendor;
use App\Support\Money;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Your store';

    protected function getStats(): array
    {
        /** @var Vendor $vendor */
        $vendor = Filament::getTenant();

        $sales = (int) SubOrder::where('vendor_id', $vendor->id)->sum('total');
        $orderCount = SubOrder::where('vendor_id', $vendor->id)->count();

        $pendingPayout = (int) SubOrder::where('vendor_id', $vendor->id)
            ->whereIn('payout_status', [SubOrder::PAYOUT_PENDING, SubOrder::PAYOUT_ELIGIBLE])
            ->sum('vendor_payout_amount');

        $openOrders = SubOrder::where('vendor_id', $vendor->id)
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING])
            ->count();

        $liveProducts = Product::where('vendor_id', $vendor->id)->visible()->count();
        $inReview = Product::where('vendor_id', $vendor->id)
            ->where('approval_status', Product::APPROVAL_PENDING)
            ->count();

        return [
            Stat::make('Total sales', Money::format($sales, 'USD'))
                ->description(number_format($orderCount).' orders')
                ->color('success'),

            Stat::make('Awaiting payout', Money::format($pendingPayout, 'USD'))
                ->description('Released after delivery is confirmed')
                ->color('info'),

            Stat::make('Orders to action', number_format($openOrders))
                ->description($openOrders > 0 ? 'Accept, produce or ship' : 'Nothing waiting')
                ->color($openOrders > 0 ? 'warning' : 'gray'),

            Stat::make('Live products', number_format($liveProducts))
                ->description(number_format($inReview).' in review')
                ->color('primary'),

            Stat::make('Store rating', number_format((float) $vendor->rating_cache, 1).' / 5')
                ->description(number_format($vendor->reviews_count).' reviews')
                ->color('warning'),

            Stat::make('Commission rate', $vendor->commissionPercent().'%')
                ->description($vendor->commission_percent ? 'Negotiated rate' : 'Platform default')
                ->color('gray'),
        ];
    }
}
