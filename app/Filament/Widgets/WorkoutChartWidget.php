<?php

namespace App\Filament\Widgets;

use App\Models\Workout;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class WorkoutChartWidget extends ChartWidget
{
    protected ?string $heading = 'Daily Workouts Completed (Last 7 Days)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D (M j)');
            $data[] = Workout::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Workouts Completed',
                    'data' => $data,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
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
