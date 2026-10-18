<?php

namespace App\Filament\Widgets;

use App\Models\WorkoutSet;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Daily training volume for the last two weeks.
 *
 * Workouts are only interesting to an admin in aggregate, so this reads the
 * sets directly (reps x weight) instead of trusting a denormalised total.
 */
class TrainingVolumeWidget extends ChartWidget
{
    protected ?string $heading = 'Training volume, last 14 days';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '220px';

    protected function getData(): array
    {
        $start = Carbon::today()->subDays(13);

        $rows = WorkoutSet::query()
            ->join('workouts', 'workouts.id', '=', 'workout_sets.workout_id')
            ->whereRaw('COALESCE(workouts.started_at, workouts.created_at) >= ?', [$start])
            ->get([
                'workout_sets.reps',
                'workout_sets.weight',
                'workouts.started_at',
                'workouts.created_at',
            ]);

        // Bucket in PHP so the query stays portable (SQLite in tests, MySQL in prod).
        $byDay = $rows->groupBy(fn ($set) => Carbon::parse(
            $set->started_at ?? $set->created_at
        )->toDateString());

        $labels = [];
        $volume = [];
        $sets = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $bucket = $byDay->get($day->toDateString(), collect());

            $labels[] = $day->format('M j');
            $volume[] = round($bucket->sum(
                fn ($set) => (int) $set->reps * (float) $set->weight
            ), 1);
            $sets[] = $bucket->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Volume (kg)',
                    'data' => $volume,
                    'backgroundColor' => 'rgba(198, 242, 78, 0.55)',
                    'borderColor' => '#c6f24e',
                    'borderWidth' => 1,
                    'borderRadius' => 3,
                ],
                [
                    'label' => 'Sets',
                    'data' => $sets,
                    'backgroundColor' => 'rgba(148, 163, 184, 0.35)',
                    'borderColor' => '#94a3b8',
                    'borderWidth' => 1,
                    'borderRadius' => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
