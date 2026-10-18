<?php

namespace App\Filament\Widgets;

use App\Models\Meal;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MealChartWidget extends ChartWidget
{
    protected ?string $heading = 'Meals logged, last 7 days';

    protected static ?int $sort = 4;

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
                    'label' => 'Meals logged',
                    'data' => $data,
                    'borderColor' => '#94a3b8',
                    'backgroundColor' => 'rgba(148, 163, 184, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 2,
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
