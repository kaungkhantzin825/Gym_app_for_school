<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Month grid of everything members recorded.
 *
 * One screen answers "what came in on a given day": the grid carries per-day
 * counts so patterns are visible at a glance, and picking a day lists the
 * actual workout and meal records behind those counts.
 */
class CalendarController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $month = $this->month($request);
        $selected = $this->selectedDay($request, $month);

        return Inertia::render('Calendar', [
            'month' => $month->format('Y-m'),
            'label' => $month->format('F Y'),
            'previous' => $month->copy()->subMonth()->format('Y-m'),
            'next' => $month->copy()->addMonth()->format('Y-m'),
            'today' => Carbon::today()->format('Y-m-d'),
            'selected' => $selected->format('Y-m-d'),
            'weeks' => $this->weeks($month, $selected),
            'records' => $this->records($selected),
            'summary' => $this->summary($month),
        ]);
    }

    private function month(Request $request): Carbon
    {
        $raw = (string) $request->query('month', '');

        if (preg_match('/^\d{4}-\d{2}$/', $raw) === 1) {
            return Carbon::createFromFormat('Y-m-d', $raw.'-01')->startOfMonth();
        }

        return Carbon::today()->startOfMonth();
    }

    private function selectedDay(Request $request, Carbon $month): Carbon
    {
        $raw = (string) $request->query('day', '');

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1) {
            return Carbon::createFromFormat('Y-m-d', $raw)->startOfDay();
        }

        // Default to today when it is in view, otherwise the first of the month.
        $today = Carbon::today();

        return $today->format('Y-m') === $month->format('Y-m')
            ? $today
            : $month->copy();
    }

    /**
     * Whole Monday-to-Sunday weeks covering the month, so the grid never
     * starts or ends mid-week. A month spans five or six of them.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function weeks(Carbon $month, Carbon $selected): array
    {
        $gridStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $workouts = $this->countsByDay(Workout::query(), $gridStart, $gridEnd);
        $meals = $this->countsByDay(Meal::query(), $gridStart, $gridEnd);

        $weeks = [];
        $cursor = $gridStart->copy();

        while ($cursor->lessThanOrEqualTo($gridEnd)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $key = $cursor->format('Y-m-d');

                $week[] = [
                    'date' => $key,
                    'day' => (int) $cursor->format('j'),
                    'inMonth' => $cursor->format('Y-m') === $month->format('Y-m'),
                    'isToday' => $key === Carbon::today()->format('Y-m-d'),
                    'isSelected' => $key === $selected->format('Y-m-d'),
                    'workouts' => $workouts[$key] ?? 0,
                    'meals' => $meals[$key] ?? 0,
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    /**
     * @return array<string, int>
     */
    private function countsByDay(Builder $query, Carbon $from, Carbon $to): array
    {
        return $query
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function records(Carbon $day): array
    {
        $workouts = Workout::with('user:id,name')
            ->whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get()
            ->map(fn (Workout $workout): array => [
                'id' => $workout->id,
                'title' => $workout->name,
                'member' => $workout->user?->name ?? 'Deleted member',
                'time' => $workout->created_at?->format('H:i'),
                'volume' => (float) $workout->total_volume,
                'duration' => (int) $workout->total_duration_minutes,
            ])
            ->all();

        $meals = Meal::with('user:id,name')
            ->whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get()
            ->map(fn (Meal $meal): array => [
                'id' => $meal->id,
                'title' => $meal->name,
                'member' => $meal->user?->name ?? 'Deleted member',
                'time' => $meal->created_at?->format('H:i'),
                'calories' => (float) $meal->total_calories,
            ])
            ->all();

        return [
            'workouts' => $workouts,
            'meals' => $meals,
        ];
    }

    /**
     * @return array{workouts: int, meals: int, volume: float, activeMembers: int}
     */
    private function summary(Carbon $month): array
    {
        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        return [
            'workouts' => Workout::whereBetween('created_at', [$from, $to])->count(),
            'meals' => Meal::whereBetween('created_at', [$from, $to])->count(),
            'volume' => (float) Workout::whereBetween('created_at', [$from, $to])->sum('total_volume'),
            'activeMembers' => Workout::whereBetween('created_at', [$from, $to])
                ->distinct()
                ->count('user_id'),
        ];
    }
}
