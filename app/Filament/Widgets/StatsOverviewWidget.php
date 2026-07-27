<?php

namespace App\Filament\Widgets;

use App\Models\Meal;
use App\Models\TutorialVideo;
use App\Models\User;
use App\Models\Workout;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();

        $totalUsers = User::count();
        $newUsersThisWeek = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        $workoutsToday = Workout::whereDate('created_at', $today)->count();
        $totalWorkouts = Workout::count();

        $mealsToday = Meal::whereDate('created_at', $today)->count();
        $totalMeals = Meal::count();

        $tutorialCount = TutorialVideo::count();

        // 7-day trend array for sparklines
        $workoutSparkline = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $workoutSparkline[] = Workout::whereDate('created_at', $date)->count();
        }

        $userSparkline = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $userSparkline[] = User::whereDate('created_at', $date)->count();
        }

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->description("+$newUsersThisWeek new this week")
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart($userSparkline)
                ->color('primary'),

            Stat::make('Workouts Logged Today', number_format($workoutsToday))
                ->description("$totalWorkouts total all-time")
                ->descriptionIcon('heroicon-m-bolt')
                ->chart($workoutSparkline)
                ->color('success'),

            Stat::make('Meals Logged Today', number_format($mealsToday))
                ->description("$totalMeals total meals logged")
                ->descriptionIcon('heroicon-m-cake')
                ->color('warning'),

            Stat::make('Tutorial Videos', number_format($tutorialCount))
                ->description('Active exercise library')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('info'),
        ];
    }
}
