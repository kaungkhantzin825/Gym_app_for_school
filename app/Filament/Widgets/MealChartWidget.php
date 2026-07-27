<?php

namespace App\Filament\Widgets;

use App\Models\Meal;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MealChartWidget extends ChartWidget
{
    protected ?string $heading = 'Daily Meals Logged (Last 7 Days)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D (M j)');
            $data[] = Meal::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Meals Logged',
                    'data' => $data,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
