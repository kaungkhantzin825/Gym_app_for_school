<?php

namespace App\Filament\Widgets;

use App\Models\WorkoutSet;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * The five movements members spend the most tonnage on — the content team's
 * queue for which tutorial video to record next.
 */
class TopExercisesWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WorkoutSet::query()
                    ->join('exercises', 'exercises.id', '=', 'workout_sets.exercise_id')
                    ->join('workouts', 'workouts.id', '=', 'workout_sets.workout_id')
                    ->whereRaw(
                        'COALESCE(workouts.started_at, workouts.created_at) >= ?',
                        [now()->subDays(30)]
                    )
                    ->groupBy('exercises.id', 'exercises.exercise_name')
                    ->selectRaw('exercises.id as id')
                    ->selectRaw('exercises.exercise_name as exercise_name')
                    ->selectRaw('COUNT(workout_sets.id) as set_count')
                    ->selectRaw('COALESCE(SUM(workout_sets.reps), 0) as rep_count')
                    ->selectRaw('COALESCE(SUM(workout_sets.reps * COALESCE(workout_sets.weight, 0)), 0) as volume')
                    ->orderByDesc('volume')
                    ->limit(5)
            )
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
}
