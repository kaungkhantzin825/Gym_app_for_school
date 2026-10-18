<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WorkoutSet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * The four numbers an admin actually acts on: is the member base growing, are
 * people training, how much are they lifting, and is their form holding up.
 */
class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $weekAgo = Carbon::now()->subDays(7);

        $totalUsers = User::count();
        $newUsersThisWeek = User::where('created_at', '>=', $weekAgo)->count();

        $activeMembers = User::whereHas(
            'workouts',
            fn ($query) => $query->where('created_at', '>=', $weekAgo)
        )->count();

        $weekSets = WorkoutSet::query()
            ->join('workouts', 'workouts.id', '=', 'workout_sets.workout_id')
            ->where('workouts.created_at', '>=', $weekAgo);

        $weekVolume = (float) (clone $weekSets)
            ->selectRaw('COALESCE(SUM(workout_sets.reps * COALESCE(workout_sets.weight, 0)), 0) as volume')
            ->value('volume');

        $averageForm = (clone $weekSets)
            ->whereNotNull('workout_sets.form_score')
            ->avg('workout_sets.form_score');

        $scoredSets = (clone $weekSets)
            ->whereNotNull('workout_sets.form_score')
            ->count();

        return [
            Stat::make('Members', number_format($totalUsers))
                ->description("+{$newUsersThisWeek} this week")
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart($this->dailySignups())
                ->color('primary'),

            Stat::make('Active this week', number_format($activeMembers))
                ->description($this->shareOfMembers($activeMembers, $totalUsers))
                ->descriptionIcon('heroicon-m-fire')
                ->color($activeMembers > 0 ? 'success' : 'gray'),

            Stat::make('Volume lifted', $this->formatVolume($weekVolume))
                ->description('Sets x kg, last 7 days')
                ->descriptionIcon('heroicon-m-bolt')
                ->chart($this->dailyVolume($weekAgo))
                ->color('primary'),

            Stat::make('Average form', $averageForm === null ? '—' : round((float) $averageForm).'%')
                ->description($scoredSets === 0
                    ? 'No camera-scored sets yet'
                    : "{$scoredSets} scored sets, last 7 days")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($this->formColor($averageForm)),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function dailySignups(): array
    {
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $counts[] = User::whereDate('created_at', Carbon::today()->subDays($i))->count();
        }

        return $counts;
    }

    /**
     * @return array<int, float>
     */
    private function dailyVolume(Carbon $since): array
    {
        $rows = WorkoutSet::query()
            ->join('workouts', 'workouts.id', '=', 'workout_sets.workout_id')
            ->where('workouts.created_at', '>=', $since)
            ->get(['workout_sets.reps', 'workout_sets.weight', 'workouts.created_at']);

        $byDay = $rows->groupBy(
            fn ($set) => Carbon::parse($set->created_at)->toDateString()
        );

        $volume = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $volume[] = round($byDay->get($day->toDateString(), collect())->sum(
                fn ($set) => (int) $set->reps * (float) $set->weight
            ), 1);
        }

        return $volume;
    }

    private function shareOfMembers(int $active, int $total): string
    {
        if ($total === 0) {
            return 'No members yet';
        }

        return round($active / $total * 100).'% of the member base';
    }

    private function formatVolume(float $volume): string
    {
        if ($volume >= 1_000_000) {
            return number_format($volume / 1_000_000, 1).'M kg';
        }

        if ($volume >= 1_000) {
            return number_format($volume / 1_000, 1).'k kg';
        }

        return number_format($volume, 0).' kg';
    }

    private function formColor(?float $average): string
    {
        if ($average === null) {
            return 'gray';
        }

        return $average >= 80 ? 'success' : ($average >= 60 ? 'warning' : 'danger');
    }
}
