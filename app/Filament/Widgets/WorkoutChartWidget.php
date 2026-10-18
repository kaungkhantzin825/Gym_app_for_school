<?php

namespace App\Filament\Widgets;

use App\Models\Workout;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class WorkoutChartWidget extends ChartWidget
{
    protected ?string $heading = 'Workouts completed, last 7 days';

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
                    'label' => 'Workouts completed',
                    'data' => $data,
                    'borderColor' => '#c6f24e',
                    'backgroundColor' => 'rgba(198, 242, 78, 0.12)',
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
