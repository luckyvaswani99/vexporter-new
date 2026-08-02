<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'GMV & commission — last 12 weeks';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $weeks = collect(range(11, 0))->map(fn (int $weeksAgo) => [
            'start' => now()->subWeeks($weeksAgo)->startOfWeek(),
            'end' => now()->subWeeks($weeksAgo)->endOfWeek(),
        ]);

        $gmv = $weeks->map(fn (array $week) => round(
            Order::whereBetween('placed_at', [$week['start'], $week['end']])->sum('grand_total') / 100,
        ));

        $commission = $weeks->map(fn (array $week) => round(
            Order::whereBetween('placed_at', [$week['start'], $week['end']])->sum('commission_total') / 100,
        ));

        return [
            'datasets' => [
                [
                    'label' => 'GMV (USD)',
                    'data' => $gmv->all(),
                    'borderColor' => '#E31837',
                    'backgroundColor' => 'rgba(227, 24, 55, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Commission (USD)',
                    'data' => $commission->all(),
                    'borderColor' => '#FF6B35',
                    'backgroundColor' => 'rgba(255, 107, 53, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $weeks->map(fn (array $week) => $week['start']->format('d M'))->all(),
        ];
    }
}
