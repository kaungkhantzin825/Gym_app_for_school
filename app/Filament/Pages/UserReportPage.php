<?php

namespace App\Filament\Pages;

use App\Models\Meal;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Workout;
use App\Models\WorkoutSet;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use UnitEnum;

class UserReportPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Members';

    protected static ?string $title = 'Member report';

    protected static ?string $navigationLabel = 'Report';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.user-report-page';

    public int $totalMembers = 0;

    public int $activeMembers30Days = 0;

    public int $totalWorkoutsLogged = 0;

    public int $totalMealsLogged = 0;

    public float $totalVolumeLiftedKg = 0;

    public ?float $averageFormScore = null;

    public int $scoredSetCount = 0;

    public array $topActiveUsers = [];

    public array $goalDistribution = [];

    public function mount(): void
    {
        $this->totalMembers = User::where('role', 'user')->count();

        $sub30 = Carbon::now()->subDays(30);
        $this->activeMembers30Days = User::whereHas('workouts', function ($q) use ($sub30) {
            $q->where('created_at', '>=', $sub30);
        })->count();

        $this->totalWorkoutsLogged = Workout::count();
        $this->totalMealsLogged = Meal::count();
        $this->totalVolumeLiftedKg = (float) Workout::sum('total_volume');

        // Camera-derived quality signal: only present when a session ran the
        // pose tracker, so the average is taken over scored sets alone.
        $this->averageFormScore = WorkoutSet::whereNotNull('form_score')->avg('form_score');
        $this->scoredSetCount = WorkoutSet::whereNotNull('form_score')->count();

        // Top 10 Active Members by Workout count
        $this->topActiveUsers = User::withCount(['workouts', 'meals'])
            ->with('profile')
            ->orderBy('workouts_count', 'desc')
            ->take(10)
            ->get()
            ->toArray();

        // Goal distribution
        $this->goalDistribution = UserProfile::selectRaw('goal, count(*) as count')
            ->whereNotNull('goal')
            ->groupBy('goal')
            ->pluck('count', 'goal')
            ->toArray();
    }
}
