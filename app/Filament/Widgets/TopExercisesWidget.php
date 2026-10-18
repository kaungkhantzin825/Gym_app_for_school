<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

/**
 * The five movements members spend the most tonnage on — the content team's
 * queue for which tutorial video to record next.
 *
 * Deliberately NOT an Eloquent query. This is an aggregate with GROUP BY, and
 * Filament appends `ORDER BY workout_sets.id` to model-backed tables to keep
 * the sort stable. MySQL with ONLY_FULL_GROUP_BY rejects that column because
 * it is neither grouped nor aggregated (error 1055). Feeding `records()` a
 * plain array keeps Filament out of the query entirely.
 */
class TopExercisesWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): array => $this->topExercises())
            ->columns([
                TextColumn::make('exercise_name')
                    ->label('Exercise')
                    ->weight('bold')
                    ->limit(28),
                TextColumn::make('set_count')
                    ->label('Sets')
                    ->numeric(),
                TextColumn::make('volume')
                    ->label('Volume (kg)')
                    ->numeric(decimalPlaces: 0),
            ])
            ->heading('Top exercises, last 30 days')
            ->paginated(false);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function topExercises(): array
    {
        return DB::table('workout_sets')
            ->join('exercises', 'exercises.id', '=', 'workout_sets.exercise_id')
            ->join('workouts', 'workouts.id', '=', 'workout_sets.workout_id')
            ->whereRaw(
                'COALESCE(workouts.started_at, workouts.created_at) >= ?',
                [now()->subDays(30)]
            )
            ->groupBy('exercises.id', 'exercises.exercise_name')
            ->select('exercises.id')
            ->selectRaw('exercises.exercise_name as exercise_name')
            ->selectRaw('COUNT(workout_sets.id) as set_count')
            ->selectRaw('COALESCE(SUM(workout_sets.reps), 0) as rep_count')
            ->selectRaw('COALESCE(SUM(workout_sets.reps * COALESCE(workout_sets.weight, 0)), 0) as volume')
            ->orderByDesc('volume')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => (array) $row)
            ->all();
    }
}
