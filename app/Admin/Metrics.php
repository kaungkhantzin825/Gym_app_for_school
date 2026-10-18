<?php

namespace App\Admin;

use App\Models\Meal;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Workout;
use App\Models\WorkoutSet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The numbers behind the dashboard and the member report.
 *
 * Both screens ask the same questions at different zoom levels, so the queries
 * live here once. Everything returns plain arrays so it can go straight into
 * an Inertia prop.
 */
class Metrics
{
    /**
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    public static function headline(): array
    {
        $members = User::where('role', 'user')->count();
        $newThisWeek = User::where('role', 'user')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $activeThisWeek = Workout::where('created_at', '>=', Carbon::now()->subDays(7))
            ->distinct()
            ->count('user_id');

        $share = $members > 0 ? round(($activeThisWeek / $members) * 100) : 0;

        $volume = (float) Workout::where('created_at', '>=', Carbon::now()->subDays(30))
            ->sum('total_volume');

        $form = WorkoutSet::whereNotNull('form_score')->avg('form_score');
        $scored = WorkoutSet::whereNotNull('form_score')->count();

        return [
            [
                'label' => 'Members',
                'value' => number_format($members),
                'hint' => $newThisWeek === 0
                    ? 'No new members this week'
                    : '+'.number_format($newThisWeek).' this week',
            ],
            [
                'label' => 'Active this week',
                'value' => number_format($activeThisWeek),
                'hint' => $share.'% of the member base',
            ],
            [
                'label' => 'Volume lifted',
                'value' => number_format($volume).' kg',
                'hint' => 'Last 30 days',
            ],
            [
                'label' => 'Average form',
                'value' => $form === null ? '—' : number_format((float) $form, 1),
                'hint' => $scored === 0
                    ? 'No camera-scored sets yet'
                    : number_format($scored).' scored sets',
            ],
        ];
    }

    /**
     * Daily counts for the last $days days, oldest first, gaps filled with 0.
     *
     * @return array{labels: array<int, string>, workouts: array<int, int>, meals: array<int, int>, volume: array<int, float>}
     */
    public static function dailySeries(int $days = 14): array
    {
        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        $workouts = Workout::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total, COALESCE(SUM(total_volume), 0) as volume')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $meals = Meal::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $workoutSeries = [];
        $mealSeries = [];
        $volumeSeries = [];

        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->toDateString();

            $labels[] = $day->format('d M');
            $workoutSeries[] = (int) ($workouts[$key]->total ?? 0);
            $mealSeries[] = (int) ($meals[$key]->total ?? 0);
            $volumeSeries[] = (float) ($workouts[$key]->volume ?? 0);
        }

        return [
            'labels' => $labels,
            'workouts' => $workoutSeries,
            'meals' => $mealSeries,
            'volume' => $volumeSeries,
        ];
    }

    /**
     * Highest-volume exercises over the last 30 days.
     *
     * Grouped by id *and* name because MySQL's ONLY_FULL_GROUP_BY rejects a
     * select of a column that is not in the GROUP BY.
     *
     * @return array<int, array{name: string, sets: int, volume: float}>
     */
    public static function topExercises(int $limit = 5): array
    {
        return DB::table('workout_sets')
            ->join('exercises', 'exercises.id', '=', 'workout_sets.exercise_id')
            ->join('workouts', 'workouts.id', '=', 'workout_sets.workout_id')
            ->whereRaw('COALESCE(workouts.started_at, workouts.created_at) >= ?', [Carbon::now()->subDays(30)])
            ->groupBy('exercises.id', 'exercises.exercise_name')
            ->select('exercises.id')
            ->selectRaw('exercises.exercise_name as name')
            ->selectRaw('COUNT(workout_sets.id) as sets')
            ->selectRaw('COALESCE(SUM(workout_sets.reps * COALESCE(workout_sets.weight, 0)), 0) as volume')
            ->orderByDesc('volume')
            ->limit($limit)
            ->get()
            ->map(fn (object $row): array => [
                'name' => (string) $row->name,
                'sets' => (int) $row->sets,
                'volume' => (float) $row->volume,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function recentWorkouts(int $limit = 5): array
    {
        return Workout::with('user:id,name')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Workout $workout): array => [
                'id' => $workout->id,
                'name' => $workout->name,
                'member' => $workout->user?->name ?? 'Deleted member',
                'volume' => (float) $workout->total_volume,
                'when' => $workout->created_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function recentMembers(int $limit = 5): array
    {
        return User::where('role', 'user')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'when' => $user->created_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * Members ranked by how much they logged, for the report page.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function topMembers(int $limit = 10): array
    {
        return User::where('role', 'user')
            ->withCount(['workouts', 'meals'])
            ->orderByDesc('workouts_count')
            ->limit($limit)
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'workouts' => (int) $user->workouts_count,
                'meals' => (int) $user->meals_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array{goal: string, total: int}>
     */
    public static function goalDistribution(): array
    {
        return UserProfile::selectRaw('goal, COUNT(*) as total')
            ->whereNotNull('goal')
            ->groupBy('goal')
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $row): array => [
                'goal' => (string) $row->goal,
                'total' => (int) $row->total,
            ])
            ->all();
    }
}
